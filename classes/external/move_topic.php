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
 * Moves a topic one position up or down.
 *
 * @package    local_competencygrid
 * @copyright  2026 Moodle in Niedersachsen e. V.
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class move_topic extends external_api {
    /**
     * Describes the parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'id' => new external_value(PARAM_INT, 'Topic id'),
            'direction' => new external_value(PARAM_ALPHA, 'Either up or down'),
        ]);
    }

    /**
     * Moves a topic one position up or down.
     *
     * @param int $id Topic id.
     * @param string $direction Either up or down.
     * @return array
     */
    public static function execute(int $id, string $direction): array {
        global $DB;
        $params = self::validate_parameters(
            self::execute_parameters(),
            [
                'id' => $id,
                'direction' => $direction,
            ]
        );
        self::validate_context(context_system::instance());
        $topic = $DB->get_record(grid::TABLE_TOPIC, ['id' => $params['id']], '*', MUST_EXIST);
        $record = grid::get((int) $topic->gridid);
        grid::require_edit($record);
        $topics = $DB->get_records(grid::TABLE_TOPIC, ['gridid' => $record->id], 'sortorder ASC, id ASC', 'id');
        $ids = array_map('intval', array_keys($topics));
        $pos = array_search((int) $topic->id, $ids, true);
        $target = $params['direction'] === 'up' ? $pos - 1 : $pos + 1;
        if ($pos !== false && $target >= 0 && $target < count($ids)) {
            [$ids[$pos], $ids[$target]] = [$ids[$target], $ids[$pos]];
            $transaction = $DB->start_delegated_transaction();
            foreach ($ids as $sortorder => $topicid) {
                $DB->set_field(grid::TABLE_TOPIC, 'sortorder', $sortorder, ['id' => $topicid]);
            }
            $transaction->allow_commit();
            grid::record_change((int) $record->id, 'topic', 'moved', (int) $topic->id);
        }
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
