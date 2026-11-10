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

use context_system;
use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_single_structure;
use core_external\external_value;
use local_competencygrid\grid;
use moodle_exception;

/**
 * Creates or updates a competency.
 *
 * @package    local_competencygrid
 * @copyright  2026 Moodle in Niedersachsen e. V.
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class save_competency extends external_api {
    /**
     * Describes the parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'gridid' => new external_value(PARAM_INT, 'Grid id'),
            'id' => new external_value(PARAM_INT, 'Competency id, 0 for a new one'),
            'topicid' => new external_value(PARAM_INT, 'Topic id'),
            'levelid' => new external_value(PARAM_INT, 'Level id'),
            'title' => new external_value(PARAM_TEXT, 'Competency text'),
            'timemodified' => new external_value(PARAM_INT, 'Known modification time, 0 skips the check', VALUE_DEFAULT, 0),
        ]);
    }

    /**
     * Creates or updates a competency.
     *
     * @param int $gridid Grid id.
     * @param int $id Competency id, 0 for a new one.
     * @param int $topicid Topic id.
     * @param int $levelid Level id.
     * @param string $title Competency text.
     * @param int $timemodified Known modification time, 0 skips the check.
     * @return array
     */
    public static function execute(int $gridid, int $id, int $topicid, int $levelid, string $title, int $timemodified = 0): array {
        global $DB, $USER;
        $params = self::validate_parameters(
            self::execute_parameters(),
            [
                'gridid' => $gridid,
                'id' => $id,
                'topicid' => $topicid,
                'levelid' => $levelid,
                'title' => $title,
                'timemodified' => $timemodified,
            ]
        );
        self::validate_context(context_system::instance());
        $record = grid::get((int) $params['gridid']);
        grid::require_edit($record);
        $gridid = (int) $record->id;
        $DB->get_record(grid::TABLE_TOPIC, ['id' => $params['topicid'], 'gridid' => $gridid], 'id', MUST_EXIST);
        $DB->get_record(grid::TABLE_LEVEL, ['id' => $params['levelid'], 'gridid' => $gridid], 'id', MUST_EXIST);
        $title = trim($params['title']);
        if ($title === '') {
            throw new moodle_exception('emptytitle', 'local_competencygrid');
        }

        if (empty($params['id'])) {
            $id = grid::add_competency($gridid, (int) $params['topicid'], (int) $params['levelid'], $title);
        } else {
            $comp = $DB->get_record(grid::TABLE_COMP, ['id' => $params['id'], 'gridid' => $gridid], '*', MUST_EXIST);
            if (!empty($params['timemodified']) && (int) $comp->timemodified !== (int) $params['timemodified']) {
                $editor = \core_user::get_user((int) $comp->usermodified);
                throw new moodle_exception('conflict', 'local_competencygrid', '', $editor ? fullname($editor) : '-');
            }
            $update = (object) [
                'id' => (int) $comp->id,
                'title' => $title,
                'levelid' => (int) $params['levelid'],
                'topicid' => (int) $params['topicid'],
                'usermodified' => (int) $USER->id,
                'timemodified' => time(),
            ];
            if ((int) $comp->topicid !== (int) $params['topicid']) {
                $conditions = ['gridid' => $gridid, 'topicid' => $params['topicid']];
                $update->sortorder = grid::next_sortorder(grid::TABLE_COMP, $conditions);
            }
            $DB->update_record(grid::TABLE_COMP, $update);
            $id = (int) $comp->id;
        }
        grid::record_change($gridid, 'competency', empty($params['id']) ? 'created' : 'updated', $id);
        return grid_structure::mutation_result($id, $gridid);
    }

    /**
     * Describes the return value.
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return grid_structure::mutation_returns();
    }
}
