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
 * Share a grid with the school instances or stop sharing it.
 *
 * @package    local_competencygrid
 * @copyright  2026 Moodle in Niedersachsen e. V.
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');

use local_competencygrid\grid;

$id = required_param('id', PARAM_INT);
$action = required_param('action', PARAM_ALPHA);
$confirm = optional_param('confirm', 0, PARAM_BOOL);

require_login();
$context = context_system::instance();
$record = grid::get($id);
if (!grid::can_release($record)) {
    throw new moodle_exception('nopermissionmanage', 'local_competencygrid');
}
$enable = $action === 'release';

$pageurl = new moodle_url('/local/competencygrid/release.php', ['id' => $id, 'action' => $enable ? 'release' : 'withdraw']);
$gridurl = new moodle_url('/local/competencygrid/grid.php', ['id' => $id]);
$PAGE->set_url($pageurl);
$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string($enable ? 'release' : 'withdrawrelease', 'local_competencygrid'));
$PAGE->set_heading(format_string($record->name));

if ($confirm) {
    require_sesskey();
    grid::set_school_sync($id, $enable);
    $message = get_string($enable ? 'releasedone' : 'withdrawdone', 'local_competencygrid');
    redirect($gridurl, $message, null, \core\output\notification::NOTIFY_SUCCESS);
}

echo $OUTPUT->header();
echo $OUTPUT->confirm(
    get_string($enable ? 'releaseconfirm' : 'withdrawconfirm', 'local_competencygrid', format_string($record->name)),
    new moodle_url($pageurl, ['confirm' => 1, 'sesskey' => sesskey()]),
    $gridurl
);
echo $OUTPUT->footer();
