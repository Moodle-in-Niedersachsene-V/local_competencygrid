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
 * Delete a competency grid.
 *
 * @package    local_competencygrid
 * @copyright  2026 Moodle in Niedersachsen e. V.
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');

use local_competencygrid\grid;

$id = required_param('id', PARAM_INT);
$confirm = optional_param('confirm', 0, PARAM_BOOL);

require_login();
$context = context_system::instance();
$record = grid::get($id);
grid::require_manage($record);

$pageurl = new moodle_url('/local/competencygrid/delete.php', ['id' => $id]);
$indexurl = new moodle_url('/local/competencygrid/index.php');
$PAGE->set_url($pageurl);
$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('deletegrid', 'local_competencygrid'));
$PAGE->set_heading(format_string($record->name));

if ($confirm) {
    require_sesskey();
    grid::delete($id);
    redirect($indexurl, get_string('griddeleted', 'local_competencygrid'), null, \core\output\notification::NOTIFY_SUCCESS);
}

echo $OUTPUT->header();
echo $OUTPUT->confirm(
    get_string('deletegridconfirm', 'local_competencygrid', format_string($record->name)),
    new moodle_url($pageurl, ['confirm' => 1, 'sesskey' => sesskey()]),
    new moodle_url('/local/competencygrid/grid.php', ['id' => $id])
);
echo $OUTPUT->footer();
