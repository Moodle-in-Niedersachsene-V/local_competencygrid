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

namespace local_competencygrid;

use context_system;
use moodle_exception;
use stdClass;

/**
 * Data access and permission helpers for competency grids.
 *
 * @package    local_competencygrid
 * @copyright  2026 Moodle in Niedersachsen e. V.
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class grid {
    /** @var int Grid is only visible to owner and members. */
    public const VISIBILITY_PRIVATE = 0;

    /** @var int Grid is visible to everybody with the view capability. */
    public const VISIBILITY_SITE = 1;

    /** @var string Member may edit the grid content. */
    public const ROLE_EDITOR = 'editor';

    /** @var string Member may only view the grid. */
    public const ROLE_VIEWER = 'viewer';

    /** @var string Main table. */
    public const TABLE = 'local_competencygrid';

    /** @var string Level table. */
    public const TABLE_LEVEL = 'local_competencygrid_level';

    /** @var string Topic table. */
    public const TABLE_TOPIC = 'local_competencygrid_topic';

    /** @var string Competency table. */
    public const TABLE_COMP = 'local_competencygrid_comp';

    /** @var string Member table. */
    public const TABLE_MEMBER = 'local_competencygrid_member';

    /**
     * Loads a grid record.
     *
     * @param int $id Grid id.
     * @return stdClass
     */
    public static function get(int $id): stdClass {
        global $DB;
        return $DB->get_record(self::TABLE, ['id' => $id], '*', MUST_EXIST);
    }

    /**
     * Returns the member role of a user in a grid.
     *
     * @param int $gridid Grid id.
     * @param int $userid User id.
     * @return string|null Role or null if the user is no member.
     */
    public static function get_member_role(int $gridid, int $userid): ?string {
        global $DB;
        $role = $DB->get_field(self::TABLE_MEMBER, 'role', ['gridid' => $gridid, 'userid' => $userid]);
        return $role === false ? null : $role;
    }

    /**
     * Resolves the user id, defaulting to the current user.
     *
     * @param int|null $userid User id or null.
     * @return int
     */
    protected static function resolve_user(?int $userid): int {
        global $USER;
        return $userid ?? (int) $USER->id;
    }

    /**
     * Whether the user may manage all grids of the site.
     *
     * @param int|null $userid User id or null for the current user.
     * @return bool
     */
    public static function can_manage_all(?int $userid = null): bool {
        return has_capability('local/competencygrid:manageall', context_system::instance(), self::resolve_user($userid));
    }

    /**
     * Whether the user may view the grid.
     *
     * @param stdClass $grid Grid record.
     * @param int|null $userid User id or null for the current user.
     * @return bool
     */
    public static function can_view(stdClass $grid, ?int $userid = null): bool {
        $userid = self::resolve_user($userid);
        if (!has_capability('local/competencygrid:view', context_system::instance(), $userid)) {
            return false;
        }
        if ((int) $grid->visibility === self::VISIBILITY_SITE || (int) $grid->ownerid === $userid) {
            return true;
        }
        if (self::get_member_role((int) $grid->id, $userid) !== null) {
            return true;
        }
        return self::can_manage_all($userid);
    }

    /**
     * Whether the user may edit the content of the grid.
     *
     * @param stdClass $grid Grid record.
     * @param int|null $userid User id or null for the current user.
     * @return bool
     */
    public static function can_edit(stdClass $grid, ?int $userid = null): bool {
        $userid = self::resolve_user($userid);
        if (isguestuser($userid) || !has_capability('local/competencygrid:view', context_system::instance(), $userid)) {
            return false;
        }
        if ((int) $grid->ownerid === $userid) {
            return true;
        }
        if (self::get_member_role((int) $grid->id, $userid) === self::ROLE_EDITOR) {
            return true;
        }
        return self::can_manage_all($userid);
    }

    /**
     * Whether the user may change settings, members and delete the grid.
     *
     * @param stdClass $grid Grid record.
     * @param int|null $userid User id or null for the current user.
     * @return bool
     */
    public static function can_manage(stdClass $grid, ?int $userid = null): bool {
        $userid = self::resolve_user($userid);
        if (isguestuser($userid)) {
            return false;
        }
        if ((int) $grid->ownerid === $userid && has_capability('local/competencygrid:view', context_system::instance(), $userid)) {
            return true;
        }
        return self::can_manage_all($userid);
    }

    /**
     * Throws an exception if the current user may not view the grid.
     *
     * @param stdClass $grid Grid record.
     * @return void
     */
    public static function require_view(stdClass $grid): void {
        if (!self::can_view($grid)) {
            throw new moodle_exception('nopermissionview', 'local_competencygrid');
        }
    }

    /**
     * Throws an exception if the current user may not edit the grid.
     *
     * @param stdClass $grid Grid record.
     * @return void
     */
    public static function require_edit(stdClass $grid): void {
        if (!self::can_edit($grid)) {
            throw new moodle_exception('nopermissionedit', 'local_competencygrid');
        }
    }

    /**
     * Throws an exception if the current user may not manage the grid.
     *
     * @param stdClass $grid Grid record.
     * @return void
     */
    public static function require_manage(stdClass $grid): void {
        if (!self::can_manage($grid)) {
            throw new moodle_exception('nopermissionmanage', 'local_competencygrid');
        }
    }

    /**
     * Generates a new, stable exacomp source identifier.
     *
     * @return string
     */
    public static function generate_source(): string {
        global $CFG;
        $site = substr(hash('sha256', $CFG->siteidentifier ?? $CFG->wwwroot), 0, 8);
        return 'MOONI-CG-' . strtoupper($site . random_string(12));
    }

    /**
     * Creates a grid.
     *
     * @param stdClass $data Grid data (name and optional metadata).
     * @param array $levels Levels to create, each ['name' => string, 'description' => string].
     * @return int New grid id.
     */
    public static function create(stdClass $data, array $levels = []): int {
        global $DB, $USER;
        $now = time();
        $record = (object) [
            'name' => $data->name,
            'description' => $data->description ?? '',
            'subject' => $data->subject ?? '',
            'classlevel' => $data->classlevel ?? '',
            'edulevel' => $data->edulevel ?? '',
            'schooltype' => $data->schooltype ?? '',
            'sentencestart' => $data->sentencestart ?? '',
            'license' => $data->license ?? '',
            'visibility' => (int) ($data->visibility ?? self::VISIBILITY_PRIVATE),
            'source' => !empty($data->source) ? $data->source : self::generate_source(),
            'edulevelsourceid' => $data->edulevelsourceid ?? null,
            'schooltypesourceid' => $data->schooltypesourceid ?? null,
            'subjectsourceid' => $data->subjectsourceid ?? null,
            'ownerid' => (int) ($data->ownerid ?? $USER->id),
            'usermodified' => (int) $USER->id,
            'timecreated' => $now,
            'timemodified' => $now,
        ];
        $id = (int) $DB->insert_record(self::TABLE, $record);
        self::add_levels($id, $levels);
        event\grid_created::create([
            'context' => context_system::instance(),
            'objectid' => $id,
        ])->trigger();
        return $id;
    }

    /**
     * Adds levels to a grid, skipping names the grid already has.
     *
     * @param int $gridid Grid id.
     * @param array $levels Levels, each ['name' => string, 'description' => string].
     * @return int Number of added levels.
     */
    public static function add_levels(int $gridid, array $levels): int {
        global $DB;
        $existing = [];
        foreach ($DB->get_fieldset_select(self::TABLE_LEVEL, 'name', 'gridid = :gridid', ['gridid' => $gridid]) as $name) {
            $existing[\core_text::strtolower($name)] = true;
        }
        $added = 0;
        foreach ($levels as $level) {
            $key = \core_text::strtolower($level['name']);
            if ($level['name'] === '' || isset($existing[$key])) {
                continue;
            }
            self::add_level($gridid, $level['name'], $level['description'] ?? '');
            $existing[$key] = true;
            $added++;
        }
        return $added;
    }

    /**
     * Updates the metadata of a grid.
     *
     * @param stdClass $data Grid data including id.
     * @return void
     */
    public static function update(stdClass $data): void {
        global $DB;
        $fields = ['name', 'description', 'subject', 'classlevel', 'edulevel', 'schooltype', 'sentencestart', 'license',
            'visibility'];
        $record = (object) ['id' => (int) $data->id];
        foreach ($fields as $field) {
            if (property_exists($data, $field)) {
                $record->$field = $data->$field;
            }
        }
        $DB->update_record(self::TABLE, $record);
        self::touch((int) $data->id);
        event\grid_updated::create([
            'context' => context_system::instance(),
            'objectid' => (int) $data->id,
        ])->trigger();
    }

    /**
     * Deletes a grid with all its content.
     *
     * @param int $id Grid id.
     * @return void
     */
    public static function delete(int $id): void {
        global $DB;
        $record = self::get($id);
        $transaction = $DB->start_delegated_transaction();
        foreach ([self::TABLE_COMP, self::TABLE_TOPIC, self::TABLE_LEVEL, self::TABLE_MEMBER] as $table) {
            $DB->delete_records($table, ['gridid' => $id]);
        }
        $DB->delete_records(self::TABLE, ['id' => $id]);
        $transaction->allow_commit();
        $event = event\grid_deleted::create([
            'context' => context_system::instance(),
            'objectid' => $id,
        ]);
        $event->add_record_snapshot(self::TABLE, $record);
        $event->trigger();
    }

    /**
     * Records a change of the grid content: marks the grid as modified and logs an event.
     *
     * @param int $gridid Grid id.
     * @param string $itemtype Either competency, topic or level.
     * @param string $action Either created, updated, moved or deleted.
     * @param int $itemid Id of the changed item.
     * @return void
     */
    public static function record_change(int $gridid, string $itemtype, string $action, int $itemid): void {
        self::touch($gridid);
        event\grid_content_updated::create([
            'context' => context_system::instance(),
            'objectid' => $gridid,
            'other' => ['itemtype' => $itemtype, 'action' => $action, 'itemid' => $itemid],
        ])->trigger();
    }

    /**
     * Adds a collaborator or changes the role of an existing one.
     *
     * @param int $gridid Grid id.
     * @param int $userid User id.
     * @param string $role Either self::ROLE_EDITOR or self::ROLE_VIEWER.
     * @return void
     */
    public static function add_member(int $gridid, int $userid, string $role): void {
        global $DB;
        $role = $role === self::ROLE_EDITOR ? self::ROLE_EDITOR : self::ROLE_VIEWER;
        $existing = $DB->get_record(self::TABLE_MEMBER, ['gridid' => $gridid, 'userid' => $userid]);
        if ($existing) {
            $DB->set_field(self::TABLE_MEMBER, 'role', $role, ['id' => $existing->id]);
        } else {
            $member = (object) [
                'gridid' => $gridid,
                'userid' => $userid,
                'role' => $role,
                'timecreated' => time(),
            ];
            $DB->insert_record(self::TABLE_MEMBER, $member);
        }
        event\member_added::create([
            'context' => context_system::instance(),
            'objectid' => $gridid,
            'relateduserid' => $userid,
            'other' => ['role' => $role],
        ])->trigger();
    }

    /**
     * Removes a collaborator.
     *
     * @param int $gridid Grid id.
     * @param int $memberid Id of the member record.
     * @return void
     */
    public static function remove_member(int $gridid, int $memberid): void {
        global $DB;
        $member = $DB->get_record(self::TABLE_MEMBER, ['id' => $memberid, 'gridid' => $gridid]);
        if (!$member) {
            return;
        }
        $DB->delete_records(self::TABLE_MEMBER, ['id' => $member->id]);
        event\member_removed::create([
            'context' => context_system::instance(),
            'objectid' => $gridid,
            'relateduserid' => (int) $member->userid,
        ])->trigger();
    }

    /**
     * Marks the grid as modified by the current user.
     *
     * @param int $gridid Grid id.
     * @return void
     */
    public static function touch(int $gridid): void {
        global $DB, $USER;
        $record = (object) [
            'id' => $gridid,
            'timemodified' => time(),
            'usermodified' => (int) $USER->id,
        ];
        $DB->update_record(self::TABLE, $record);
    }

    /**
     * Returns the next free sort order in a table for a grid.
     *
     * @param string $table Table name.
     * @param array $conditions Conditions, must contain gridid.
     * @return int
     */
    public static function next_sortorder(string $table, array $conditions): int {
        global $DB;
        $max = $DB->get_field($table, 'MAX(sortorder)', $conditions);
        return $max === null || $max === false ? 0 : ((int) $max) + 1;
    }

    /**
     * Adds a level to a grid.
     *
     * @param int $gridid Grid id.
     * @param string $name Level name.
     * @param string $description Short description.
     * @param int|null $sortorder Sort order or null to append.
     * @param string|null $sourceid Imported source id.
     * @return int New level id.
     */
    public static function add_level(
        int $gridid,
        string $name,
        string $description = '',
        ?int $sortorder = null,
        ?string $sourceid = null
    ): int {
        global $DB;
        $record = (object) [
            'gridid' => $gridid,
            'name' => $name,
            'description' => $description,
            'sortorder' => $sortorder ?? self::next_sortorder(self::TABLE_LEVEL, ['gridid' => $gridid]),
            'sourceid' => $sourceid,
        ];
        return (int) $DB->insert_record(self::TABLE_LEVEL, $record);
    }

    /**
     * Adds a topic to a grid.
     *
     * @param int $gridid Grid id.
     * @param string $name Topic name.
     * @param string|null $sourceid Imported source id.
     * @return int New topic id.
     */
    public static function add_topic(int $gridid, string $name, ?string $sourceid = null): int {
        global $DB;
        $record = (object) [
            'gridid' => $gridid,
            'name' => $name,
            'sortorder' => self::next_sortorder(self::TABLE_TOPIC, ['gridid' => $gridid]),
            'sourceid' => $sourceid,
        ];
        return (int) $DB->insert_record(self::TABLE_TOPIC, $record);
    }

    /**
     * Adds a competency to a grid.
     *
     * @param int $gridid Grid id.
     * @param int $topicid Topic id.
     * @param int $levelid Level id.
     * @param string $title Competency text.
     * @param string|null $sourceid Imported source id.
     * @return int New competency id.
     */
    public static function add_competency(
        int $gridid,
        int $topicid,
        int $levelid,
        string $title,
        ?string $sourceid = null
    ): int {
        global $DB, $USER;
        $now = time();
        $record = (object) [
            'gridid' => $gridid,
            'topicid' => $topicid,
            'levelid' => $levelid,
            'title' => $title,
            'sortorder' => self::next_sortorder(self::TABLE_COMP, ['gridid' => $gridid, 'topicid' => $topicid]),
            'sourceid' => $sourceid,
            'usermodified' => (int) $USER->id,
            'timecreated' => $now,
            'timemodified' => $now,
        ];
        return (int) $DB->insert_record(self::TABLE_COMP, $record);
    }

    /**
     * Returns the grids the current user can see, with counts.
     *
     * @param string $scope Either 'mine' or 'all'.
     * @param string $search Optional search text.
     * @return stdClass[]
     */
    public static function list_grids(string $scope, string $search = ''): array {
        global $DB, $USER;
        $params = [
            'u1' => (int) $USER->id,
            'u2' => (int) $USER->id,
            'all' => $scope === 'mine' ? 0 : 1,
            'site' => self::VISIBILITY_SITE,
            'manageall' => self::can_manage_all() ? 1 : 0,
        ];
        $sql = "SELECT g.id, g.name, g.subject, g.classlevel, g.edulevel, g.schooltype, g.license, g.visibility,
                       g.ownerid, g.timemodified, m.role AS memberrole,
                       (SELECT COUNT(1) FROM {local_competencygrid_topic} t WHERE t.gridid = g.id) AS topiccount,
                       (SELECT COUNT(1) FROM {local_competencygrid_comp} c WHERE c.gridid = g.id) AS compcount
                  FROM {local_competencygrid} g
             LEFT JOIN {local_competencygrid_member} m ON m.gridid = g.id AND m.userid = :u2
                 WHERE g.ownerid = :u1
                       OR m.id IS NOT NULL
                       OR (:all = 1 AND (g.visibility = :site OR :manageall = 1))
              ORDER BY g.timemodified DESC";
        $records = $DB->get_records_sql($sql, $params, 0, 500);

        $search = \core_text::strtolower(trim($search));
        if ($search === '') {
            return $records;
        }
        $matches = [];
        foreach ($records as $id => $record) {
            $fields = [$record->name, $record->subject, $record->classlevel, $record->edulevel, $record->schooltype];
            $haystack = \core_text::strtolower(implode(' ', $fields));
            if (\core_text::strpos($haystack, $search) !== false) {
                $matches[$id] = $record;
            }
        }
        return $matches;
    }
}
