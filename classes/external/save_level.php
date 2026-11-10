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
 * Creates or updates a level.
 *
 * @package    local_competencygrid
 * @copyright  2026 Moodle in Niedersachsen e. V.
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class save_level extends external_api {
    /**
     * Describes the parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'gridid' => new external_value(PARAM_INT, 'Grid id'),
            'id' => new external_value(PARAM_INT, 'Level id, 0 for a new one'),
            'name' => new external_value(PARAM_TEXT, 'Level name'),
            'description' => new external_value(PARAM_TEXT, 'Short description', VALUE_DEFAULT, ''),
        ]);
    }

    /**
     * Creates or updates a level.
     *
     * @param int $gridid Grid id.
     * @param int $id Level id, 0 for a new one.
     * @param string $name Level name.
     * @param string $description Short description.
     * @return array
     */
    public static function execute(int $gridid, int $id, string $name, string $description = ''): array {
        global $DB;
        $params = self::validate_parameters(
            self::execute_parameters(),
            [
                'gridid' => $gridid,
                'id' => $id,
                'name' => $name,
                'description' => $description,
            ]
        );
        self::validate_context(context_system::instance());
        $record = grid::get((int) $params['gridid']);
        grid::require_edit($record);
        $name = trim($params['name']);
        if ($name === '') {
            throw new moodle_exception('emptyname', 'local_competencygrid');
        }
        $name = \core_text::substr($name, 0, 255);
        $description = \core_text::substr(trim($params['description']), 0, 255);
        if (empty($params['id'])) {
            $id = grid::add_level((int) $record->id, $name, $description);
        } else {
            $level = $DB->get_record(grid::TABLE_LEVEL, ['id' => $params['id'], 'gridid' => $record->id], 'id', MUST_EXIST);
            $update = (object) [
                'id' => $level->id,
                'name' => $name,
                'description' => $description,
            ];
            $DB->update_record(grid::TABLE_LEVEL, $update);
            $id = (int) $level->id;
        }
        grid::record_change((int) $record->id, 'level', empty($params['id']) ? 'created' : 'updated', $id);
        return grid_structure::mutation_result($id, (int) $record->id);
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
