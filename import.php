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
 * Import exacomp XML files.
 *
 * @package    local_competencygrid
 * @copyright  2026 Moodle in Niedersachsen e. V.
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');

use local_competencygrid\form\import_form;
use local_competencygrid\grid;
use local_competencygrid\local\exacomp_importer;

require_login();
$context = context_system::instance();
require_capability('local/competencygrid:create', $context);

$indexurl = new moodle_url('/local/competencygrid/index.php');
$title = get_string('importexacomp', 'local_competencygrid');
$PAGE->set_url(new moodle_url('/local/competencygrid/import.php'));
$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
$PAGE->set_title($title);
$PAGE->set_heading($title);
$PAGE->navbar->add(get_string('competencygrids', 'local_competencygrid'), $indexurl);
$PAGE->navbar->add($title);

$form = new import_form();
if ($form->is_cancelled()) {
    redirect($indexurl);
} else if ($data = $form->get_data()) {
    $content = $form->get_file_content('importfile');
    $visibility = (int) $data->visibility === grid::VISIBILITY_SITE ? grid::VISIBILITY_SITE : grid::VISIBILITY_PRIVATE;
    try {
        $ids = exacomp_importer::import((string) $content, $visibility);
    } catch (moodle_exception $e) {
        redirect($PAGE->url, $e->getMessage(), null, \core\output\notification::NOTIFY_ERROR);
    }
    if (count($ids) === 1) {
        redirect(
            new moodle_url('/local/competencygrid/grid.php', ['id' => reset($ids)]),
            get_string('importdone', 'local_competencygrid', 1),
            null,
            \core\output\notification::NOTIFY_SUCCESS
        );
    }
    redirect(
        new moodle_url('/local/competencygrid/index.php', ['scope' => 'mine']),
        get_string('importdone', 'local_competencygrid', count($ids)),
        null,
        \core\output\notification::NOTIFY_SUCCESS
    );
}

echo $OUTPUT->header();
$form->display();
echo $OUTPUT->footer();
