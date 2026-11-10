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

namespace local_competencygrid\external;

use core_external\external_multiple_structure;
use core_external\external_single_structure;
use core_external\external_value;
use local_competencygrid\grid;
use stdClass;

/**
 * Shared return structure of all grid web services.
 *
 * @package    local_competencygrid
 * @copyright  2026 Moodle in Niedersachsen e. V.
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class grid_structure {
    /**
     * Describes the grid structure.
     *
     * @return external_single_structure
     */
    public static function returns(): external_single_structure {
        return new external_single_structure([
            'id' => new external_value(PARAM_INT, 'Grid id'),
            'name' => new external_value(PARAM_TEXT, 'Grid name'),
            'sentencestart' => new external_value(PARAM_TEXT, 'Common sentence start'),
            'canedit' => new external_value(PARAM_BOOL, 'Whether the user may edit'),
            'timemodified' => new external_value(PARAM_INT, 'Last modification of the grid'),
            'levels' => new external_multiple_structure(new external_single_structure([
                'id' => new external_value(PARAM_INT, 'Level id'),
                'name' => new external_value(PARAM_TEXT, 'Level name'),
                'description' => new external_value(PARAM_TEXT, 'Level description'),
            ])),
            'topics' => new external_multiple_structure(new external_single_structure([
                'id' => new external_value(PARAM_INT, 'Topic id'),
                'name' => new external_value(PARAM_TEXT, 'Topic name'),
            ])),
            'competencies' => new external_multiple_structure(new external_single_structure([
                'id' => new external_value(PARAM_INT, 'Competency id'),
                'topicid' => new external_value(PARAM_INT, 'Topic id'),
                'levelid' => new external_value(PARAM_INT, 'Level id'),
                'title' => new external_value(PARAM_TEXT, 'Competency text'),
                'timemodified' => new external_value(PARAM_INT, 'Last modification'),
                'modifiedby' => new external_value(PARAM_TEXT, 'Full name of the last editor'),
            ])),
        ]);
    }

    /**
     * Describes the result of a changing web service: the affected id and the reloaded grid.
     *
     * @return external_single_structure
     */
    public static function mutation_returns(): external_single_structure {
        return new external_single_structure([
            'id' => new external_value(PARAM_INT, 'Id of the created, changed or deleted item'),
            'grid' => self::returns(),
        ]);
    }

    /**
     * Builds the result of a changing web service.
     *
     * @param int $id Id of the affected item.
     * @param int $gridid Grid id.
     * @return array
     */
    public static function mutation_result(int $id, int $gridid): array {
        return [
            'id' => $id,
            'grid' => self::build(grid::get($gridid)),
        ];
    }

    /**
     * Builds the structure for a grid.
     *
     * @param stdClass $record Grid record.
     * @return array
     */
    public static function build(stdClass $record): array {
        global $DB;
        $gridid = (int) $record->id;
        $levels = $DB->get_records(grid::TABLE_LEVEL, ['gridid' => $gridid], 'sortorder ASC, id ASC');
        $topics = $DB->get_records(grid::TABLE_TOPIC, ['gridid' => $gridid], 'sortorder ASC, id ASC');
        $comps = $DB->get_records(grid::TABLE_COMP, ['gridid' => $gridid], 'sortorder ASC, id ASC');

        $userids = array_unique(array_filter(array_map(fn($c) => (int) $c->usermodified, $comps)));
        $names = [];
        if ($userids) {
            $namefields = 'id, ' . implode(', ', \core_user\fields::get_name_fields());
            $users = $DB->get_records_list('user', 'id', $userids, '', $namefields);
            foreach ($users as $user) {
                $names[(int) $user->id] = fullname($user);
            }
        }

        $levellist = [];
        foreach ($levels as $level) {
            $levellist[] = [
                'id' => (int) $level->id,
                'name' => $level->name,
                'description' => (string) $level->description,
            ];
        }
        $topiclist = [];
        foreach ($topics as $topic) {
            $topiclist[] = [
                'id' => (int) $topic->id,
                'name' => $topic->name,
            ];
        }
        $complist = [];
        foreach ($comps as $comp) {
            $complist[] = [
                'id' => (int) $comp->id,
                'topicid' => (int) $comp->topicid,
                'levelid' => (int) $comp->levelid,
                'title' => $comp->title,
                'timemodified' => (int) $comp->timemodified,
                'modifiedby' => $names[(int) $comp->usermodified] ?? '',
            ];
        }

        return [
            'id' => $gridid,
            'name' => $record->name,
            'sentencestart' => (string) $record->sentencestart,
            'canedit' => grid::can_edit($record),
            'timemodified' => (int) $record->timemodified,
            'levels' => $levellist,
            'topics' => $topiclist,
            'competencies' => $complist,
        ];
    }
}
