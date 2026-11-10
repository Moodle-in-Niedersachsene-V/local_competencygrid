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
 * View and edit a competency grid.
 *
 * @package    local_competencygrid
 * @copyright  2026 Moodle in Niedersachsen e. V.
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/licenselib.php');

use local_competencygrid\grid;

$id = required_param('id', PARAM_INT);

require_login();
$context = context_system::instance();
$record = grid::get($id);
grid::require_view($record);

$PAGE->set_url(new moodle_url('/local/competencygrid/grid.php', ['id' => $id]));
$PAGE->set_context($context);
$PAGE->set_pagelayout('report');
$PAGE->set_title(format_string($record->name));
$PAGE->set_heading(format_string($record->name));
$PAGE->navbar->add(get_string('competencygrids', 'local_competencygrid'), new moodle_url('/local/competencygrid/index.php'));
$PAGE->navbar->add(format_string($record->name));
$PAGE->requires->js_call_amd('local_competencygrid/editor', 'init', [$id]);

$chips = [];
foreach (['edulevel', 'schooltype', 'subject', 'classlevel'] as $field) {
    if (!empty($record->$field)) {
        $chips[] = ['text' => format_string($record->$field)];
    }
}
if (!empty($record->license)) {
    foreach (license_manager::get_licenses() as $license) {
        if ($license->shortname === $record->license) {
            $chips[] = ['text' => $license->fullname];
        }
    }
}
$visibilitykey = (int) $record->visibility === grid::VISIBILITY_SITE ? 'visibilitysite' : 'visibilityprivate';
$chips[] = ['text' => get_string($visibilitykey, 'local_competencygrid')];

$data = [
    'gridid' => $id,
    'chips' => $chips,
    'description' => format_text($record->description, FORMAT_PLAIN),
    'canmanage' => grid::can_manage($record),
    'settingsurl' => (new moodle_url('/local/competencygrid/edit.php', ['id' => $id]))->out(false),
    'shareurl' => (new moodle_url('/local/competencygrid/share.php', ['id' => $id]))->out(false),
    'deleteurl' => (new moodle_url('/local/competencygrid/delete.php', ['id' => $id]))->out(false),
    'exportexacompurl' => (new moodle_url('/local/competencygrid/export.php', ['id' => $id, 'format' => 'exacomp']))->out(false),
    'exportcsvurl' => (new moodle_url('/local/competencygrid/export.php', ['id' => $id, 'format' => 'moodlecsv']))->out(false),
];

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('local_competencygrid/grid_page', $data);
echo $OUTPUT->footer();
