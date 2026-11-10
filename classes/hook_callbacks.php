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

use core\hook\navigation\primary_extend;

/**
 * Hook callbacks for local_competencygrid.
 *
 * @package    local_competencygrid
 * @copyright  2026 Moodle in Niedersachsen e. V.
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class hook_callbacks {
    /**
     * Adds the grid library to the primary navigation.
     *
     * @param primary_extend $hook Hook.
     * @return void
     */
    public static function extend_primary_navigation(primary_extend $hook): void {
        if (during_initial_install() || !get_config('local_competencygrid', 'showinprimarynav')) {
            return;
        }
        if (!isloggedin() || isguestuser()) {
            return;
        }
        if (!has_capability('local/competencygrid:view', \context_system::instance())) {
            return;
        }
        $hook->get_primaryview()->add(
            get_string('competencygrids', 'local_competencygrid'),
            new \moodle_url('/local/competencygrid/index.php'),
            \navigation_node::TYPE_CUSTOM,
            null,
            'local_competencygrid'
        );
    }
}
