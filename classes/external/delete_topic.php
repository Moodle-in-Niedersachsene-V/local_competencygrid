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

/**
 * Deletes a topic and its competencies.
 *
 * @package    local_competencygrid
 * @copyright  2026 Moodle in Niedersachsen e. V.
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class delete_topic extends external_api {
    /**
     * Describes the parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'id' => new external_value(PARAM_INT, 'Topic id'),
        ]);
    }

    /**
     * Deletes a topic and its competencies.
     *
     * @param int $id Topic id.
     * @return array
     */
    public static function execute(int $id): array {
        global $DB;
        $params = self::validate_parameters(
            self::execute_parameters(),
            [
                'id' => $id,
            ]
        );
        self::validate_context(context_system::instance());
        $topic = $DB->get_record(grid::TABLE_TOPIC, ['id' => $params['id']], '*', MUST_EXIST);
        $record = grid::get((int) $topic->gridid);
        grid::require_edit($record);
        $transaction = $DB->start_delegated_transaction();
        $DB->delete_records(grid::TABLE_COMP, ['topicid' => $topic->id]);
        $DB->delete_records(grid::TABLE_TOPIC, ['id' => $topic->id]);
        $transaction->allow_commit();
        grid::record_change((int) $record->id, 'topic', 'deleted', (int) $topic->id);
        return grid_structure::mutation_result((int) $topic->id, (int) $record->id);
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
