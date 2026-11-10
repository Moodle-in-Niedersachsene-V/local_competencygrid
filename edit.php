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
 * Create a competency grid or edit its settings.
 *
 * @package    local_competencygrid
 * @copyright  2026 Moodle in Niedersachsen e. V.
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');

use local_competencygrid\form\grid_form;
use local_competencygrid\grid;
use local_competencygrid\local\level_schemes;

$id = optional_param('id', 0, PARAM_INT);

require_login();
$context = context_system::instance();
$indexurl = new moodle_url('/local/competencygrid/index.php');

if ($id) {
    $record = grid::get($id);
    grid::require_manage($record);
    $returnurl = new moodle_url('/local/competencygrid/grid.php', ['id' => $id]);
    $title = get_string('gridsettings', 'local_competencygrid');
} else {
    require_capability('local/competencygrid:create', $context);
    $record = null;
    $returnurl = $indexurl;
    $title = get_string('creategrid', 'local_competencygrid');
}

$PAGE->set_url(new moodle_url('/local/competencygrid/edit.php', ['id' => $id]));
$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
$PAGE->set_title($title);
$PAGE->set_heading($title);
$PAGE->navbar->add(get_string('competencygrids', 'local_competencygrid'), $indexurl);
$PAGE->navbar->add($title);

$form = new grid_form(null, ['isnew' => !$id, 'current' => $record]);
if ($record) {
    $form->set_data($record);
}

if ($form->is_cancelled()) {
    redirect($returnurl);
} else if ($data = $form->get_data()) {
    $data->visibility = (int) $data->visibility === grid::VISIBILITY_SITE ? grid::VISIBILITY_SITE : grid::VISIBILITY_PRIVATE;
    foreach (\local_competencygrid\local\field_options::FIELDS as $field) {
        $data->$field = \core_text::substr(trim((string) ($data->$field ?? '')), 0, 255);
    }
    $levels = (int) $data->levelscheme >= 0 ? level_schemes::get_levels((int) $data->levelscheme) : [];
    if ($id) {
        $data->id = $id;
        grid::update($data);
        if ($levels && grid::add_levels($id, $levels) > 0) {
            grid::record_change($id, 'level', 'created', 0);
        }
    } else {
        $id = grid::create($data, $levels);
    }
    redirect(new moodle_url('/local/competencygrid/grid.php', ['id' => $id]));
}

echo $OUTPUT->header();
$form->display();
echo $OUTPUT->footer();
