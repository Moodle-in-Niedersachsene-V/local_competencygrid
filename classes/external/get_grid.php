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
 * Returns the full structure of a competency grid.
 *
 * @package    local_competencygrid
 * @copyright  2026 Moodle in Niedersachsen e. V.
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class get_grid extends external_api {
    /**
     * Describes the parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'gridid' => new external_value(PARAM_INT, 'Grid id'),
        ]);
    }

    /**
     * Returns the full structure of a competency grid.
     *
     * @param int $gridid Grid id.
     * @return array
     */
    public static function execute(int $gridid): array {
        $params = self::validate_parameters(
            self::execute_parameters(),
            [
                'gridid' => $gridid,
            ]
        );
        self::validate_context(context_system::instance());
        $record = grid::get((int) $params['gridid']);
        grid::require_view($record);
        return grid_structure::build($record);
    }

    /**
     * Describes the return value.
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return grid_structure::returns();
    }
}
