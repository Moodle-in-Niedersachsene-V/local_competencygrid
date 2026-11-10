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

/**
 * External functions for local_competencygrid.
 *
 * @package    local_competencygrid
 * @copyright  2026 Moodle in Niedersachsen e. V.
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = [
    'local_competencygrid_get_grid' => [
        'classname' => 'local_competencygrid\external\get_grid',
        'description' => 'Returns the full structure of a competency grid.',
        'type' => 'read',
        'ajax' => true,
        'capabilities' => 'local/competencygrid:view',
    ],
    'local_competencygrid_save_competency' => [
        'classname' => 'local_competencygrid\external\save_competency',
        'description' => 'Creates or updates a competency of a grid.',
        'type' => 'write',
        'ajax' => true,
        'capabilities' => 'local/competencygrid:view',
    ],
    'local_competencygrid_delete_competency' => [
        'classname' => 'local_competencygrid\external\delete_competency',
        'description' => 'Deletes a competency of a grid.',
        'type' => 'write',
        'ajax' => true,
        'capabilities' => 'local/competencygrid:view',
    ],
    'local_competencygrid_save_topic' => [
        'classname' => 'local_competencygrid\external\save_topic',
        'description' => 'Creates or renames a topic of a grid.',
        'type' => 'write',
        'ajax' => true,
        'capabilities' => 'local/competencygrid:view',
    ],
    'local_competencygrid_delete_topic' => [
        'classname' => 'local_competencygrid\external\delete_topic',
        'description' => 'Deletes a topic and its competencies.',
        'type' => 'write',
        'ajax' => true,
        'capabilities' => 'local/competencygrid:view',
    ],
    'local_competencygrid_move_topic' => [
        'classname' => 'local_competencygrid\external\move_topic',
        'description' => 'Moves a topic up or down.',
        'type' => 'write',
        'ajax' => true,
        'capabilities' => 'local/competencygrid:view',
    ],
    'local_competencygrid_save_level' => [
        'classname' => 'local_competencygrid\external\save_level',
        'description' => 'Creates or updates a level of a grid.',
        'type' => 'write',
        'ajax' => true,
        'capabilities' => 'local/competencygrid:view',
    ],
    'local_competencygrid_delete_level' => [
        'classname' => 'local_competencygrid\external\delete_level',
        'description' => 'Deletes a level and its competencies.',
        'type' => 'write',
        'ajax' => true,
        'capabilities' => 'local/competencygrid:view',
    ],
];
