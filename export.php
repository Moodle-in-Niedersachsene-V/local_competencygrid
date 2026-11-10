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
 * Export a competency grid.
 *
 * @package    local_competencygrid
 * @copyright  2026 Moodle in Niedersachsen e. V.
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/filelib.php');

use local_competencygrid\grid;
use local_competencygrid\local\exacomp_exporter;
use local_competencygrid\local\moodlecsv_exporter;

$id = required_param('id', PARAM_INT);
$format = required_param('format', PARAM_ALPHA);

require_login();
$record = grid::get($id);
grid::require_view($record);

if ($format === 'moodlecsv') {
    (new moodlecsv_exporter($record))->download();
    die();
}

$exporter = new exacomp_exporter($record);
send_file($exporter->export(), $exporter->get_filename(), 0, 0, true, true, 'application/xml');
