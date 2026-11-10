<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

namespace local_competencygrid\privacy;

use context;
use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\transform;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;
use local_competencygrid\grid;

/**
 * Privacy provider for local_competencygrid.
 *
 * Grids are shared content. When a user's data is deleted, memberships are removed and
 * the user is replaced by 0 in ownership and modification fields, the content is kept.
 *
 * @package    local_competencygrid
 * @copyright  2026 Moodle in Niedersachsen e. V.
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\core_userlist_provider,
    \core_privacy\local\request\plugin\provider {
    /**
     * Describes the stored personal data.
     *
     * @param collection $collection Collection to add to.
     * @return collection
     */
    public static function get_metadata(collection $collection): collection {
        $collection->add_database_table(
            grid::TABLE,
            [
                'name' => 'privacy:metadata:grid:name',
                'ownerid' => 'privacy:metadata:grid:ownerid',
                'usermodified' => 'privacy:metadata:grid:usermodified',
                'timecreated' => 'privacy:metadata:grid:timecreated',
                'timemodified' => 'privacy:metadata:grid:timemodified',
            ],
            'privacy:metadata:grid'
        );
        $collection->add_database_table(
            grid::TABLE_COMP,
            [
                'title' => 'privacy:metadata:comp:title',
                'usermodified' => 'privacy:metadata:comp:usermodified',
                'timemodified' => 'privacy:metadata:comp:timemodified',
            ],
            'privacy:metadata:comp'
        );
        $collection->add_database_table(
            grid::TABLE_MEMBER,
            [
                'userid' => 'privacy:metadata:member:userid',
                'role' => 'privacy:metadata:member:role',
                'timecreated' => 'privacy:metadata:member:timecreated',
            ],
            'privacy:metadata:member'
        );
        return $collection;
    }

    /**
     * Whether the user has any data in this plugin.
     *
     * @param int $userid User id.
     * @return bool
     */
    protected static function user_has_data(int $userid): bool {
        global $DB;
        return $DB->record_exists_select(grid::TABLE, 'ownerid = :u1 OR usermodified = :u2', ['u1' => $userid, 'u2' => $userid])
            || $DB->record_exists(grid::TABLE_COMP, ['usermodified' => $userid])
            || $DB->record_exists(grid::TABLE_MEMBER, ['userid' => $userid]);
    }

    /**
     * Returns the contexts with data of the user (always the system context).
     *
     * @param int $userid User id.
     * @return contextlist
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        $contextlist = new contextlist();
        if (self::user_has_data($userid)) {
            $contextlist->add_system_context();
        }
        return $contextlist;
    }

    /**
     * Adds the users with data in the given context.
     *
     * @param userlist $userlist User list to fill.
     * @return void
     */
    public static function get_users_in_context(userlist $userlist): void {
        if (!$userlist->get_context() instanceof \context_system) {
            return;
        }
        $userlist->add_from_sql('ownerid', 'SELECT ownerid FROM {local_competencygrid} WHERE ownerid > 0', []);
        $userlist->add_from_sql('usermodified', 'SELECT usermodified FROM {local_competencygrid} WHERE usermodified > 0', []);
        $userlist->add_from_sql('usermodified', 'SELECT usermodified FROM {local_competencygrid_comp} WHERE usermodified > 0', []);
        $userlist->add_from_sql('userid', 'SELECT userid FROM {local_competencygrid_member}', []);
    }

    /**
     * Exports the data of the user.
     *
     * @param approved_contextlist $contextlist Approved contexts.
     * @return void
     */
    public static function export_user_data(approved_contextlist $contextlist): void {
        global $DB;
        $userid = (int) $contextlist->get_user()->id;
        foreach ($contextlist->get_contexts() as $context) {
            if (!$context instanceof \context_system) {
                continue;
            }
            $owned = $DB->get_records(grid::TABLE, ['ownerid' => $userid], 'id', 'id, name, timecreated, timemodified');
            $memberships = $DB->get_records_sql(
                'SELECT m.id, g.name, m.role, m.timecreated
                   FROM {local_competencygrid_member} m
                   JOIN {local_competencygrid} g ON g.id = m.gridid
                  WHERE m.userid = :userid',
                ['userid' => $userid]
            );
            $comps = $DB->get_records(grid::TABLE_COMP, ['usermodified' => $userid], 'id', 'id, title, timemodified');
            $data = (object) [
                'ownedgrids' => [],
                'memberships' => [],
                'lastmodifiedcompetencies' => [],
            ];
            foreach ($owned as $grid) {
                $data->ownedgrids[] = [
                    'name' => $grid->name,
                    'timecreated' => transform::datetime($grid->timecreated),
                    'timemodified' => transform::datetime($grid->timemodified),
                ];
            }
            foreach ($memberships as $membership) {
                $data->memberships[] = [
                    'grid' => $membership->name,
                    'role' => $membership->role,
                    'timecreated' => transform::datetime($membership->timecreated),
                ];
            }
            foreach ($comps as $comp) {
                $data->lastmodifiedcompetencies[] = [
                    'title' => $comp->title,
                    'timemodified' => transform::datetime($comp->timemodified),
                ];
            }
            writer::with_context($context)->export_data([get_string('pluginname', 'local_competencygrid')], $data);
        }
    }

    /**
     * Removes the personal data of one user.
     *
     * @param int $userid User id.
     * @return void
     */
    protected static function delete_user(int $userid): void {
        global $DB;
        $DB->delete_records(grid::TABLE_MEMBER, ['userid' => $userid]);
        $DB->set_field(grid::TABLE, 'ownerid', 0, ['ownerid' => $userid]);
        $DB->set_field(grid::TABLE, 'usermodified', 0, ['usermodified' => $userid]);
        $DB->set_field(grid::TABLE_COMP, 'usermodified', 0, ['usermodified' => $userid]);
    }

    /**
     * Removes the personal data of all users in a context.
     *
     * @param context $context Context.
     * @return void
     */
    public static function delete_data_for_all_users_in_context(context $context): void {
        global $DB;
        if (!$context instanceof \context_system) {
            return;
        }
        $DB->delete_records(grid::TABLE_MEMBER);
        $DB->set_field_select(grid::TABLE, 'ownerid', 0, 'ownerid > 0');
        $DB->set_field_select(grid::TABLE, 'usermodified', 0, 'usermodified > 0');
        $DB->set_field_select(grid::TABLE_COMP, 'usermodified', 0, 'usermodified > 0');
    }

    /**
     * Removes the personal data of the user in the approved contexts.
     *
     * @param approved_contextlist $contextlist Approved contexts.
     * @return void
     */
    public static function delete_data_for_user(approved_contextlist $contextlist): void {
        foreach ($contextlist->get_contexts() as $context) {
            if ($context instanceof \context_system) {
                self::delete_user((int) $contextlist->get_user()->id);
            }
        }
    }

    /**
     * Removes the personal data of several users in a context.
     *
     * @param approved_userlist $userlist Approved users.
     * @return void
     */
    public static function delete_data_for_users(approved_userlist $userlist): void {
        if (!$userlist->get_context() instanceof \context_system) {
            return;
        }
        foreach ($userlist->get_userids() as $userid) {
            self::delete_user((int) $userid);
        }
    }
}
