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
 * Manage collaborators of a competency grid.
 *
 * @package    local_competencygrid
 * @copyright  2026 Moodle in Niedersachsen e. V.
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');

use local_competencygrid\form\member_form;
use local_competencygrid\grid;

$id = required_param('id', PARAM_INT);
$removeid = optional_param('remove', 0, PARAM_INT);

require_login();
$context = context_system::instance();
$record = grid::get($id);
grid::require_manage($record);

$pageurl = new moodle_url('/local/competencygrid/share.php', ['id' => $id]);
$title = get_string('share', 'local_competencygrid');
$PAGE->set_url($pageurl);
$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
$PAGE->set_title($title . ': ' . format_string($record->name));
$PAGE->set_heading(format_string($record->name));
$PAGE->navbar->add(get_string('competencygrids', 'local_competencygrid'), new moodle_url('/local/competencygrid/index.php'));
$PAGE->navbar->add(format_string($record->name), new moodle_url('/local/competencygrid/grid.php', ['id' => $id]));
$PAGE->navbar->add($title);

if ($removeid) {
    require_sesskey();
    grid::remove_member($id, $removeid);
    redirect($pageurl, get_string('memberremoved', 'local_competencygrid'), null, \core\output\notification::NOTIFY_SUCCESS);
}

$form = new member_form($pageurl);
$form->set_data(['id' => $id]);
if ($data = $form->get_data()) {
    $user = member_form::find_user($data->identifier);
    if ($user && (int) $user->id !== (int) $record->ownerid) {
        grid::add_member($id, (int) $user->id, (string) $data->role);
    }
    redirect($pageurl, get_string('memberadded', 'local_competencygrid'), null, \core\output\notification::NOTIFY_SUCCESS);
}

$members = [];
$memberrecords = $DB->get_records(grid::TABLE_MEMBER, ['gridid' => $id], 'timecreated ASC, id ASC');
$userids = array_map(fn($member) => (int) $member->userid, $memberrecords);
$namefields = 'id, ' . implode(', ', \core_user\fields::get_name_fields());
$users = $userids ? $DB->get_records_list('user', 'id', $userids, '', $namefields) : [];
foreach ($memberrecords as $member) {
    if (!isset($users[$member->userid])) {
        continue;
    }
    $members[] = [
        'name' => fullname($users[$member->userid]),
        'role' => get_string($member->role === grid::ROLE_EDITOR ? 'roleeditor' : 'roleviewer', 'local_competencygrid'),
        'removeurl' => (new moodle_url($pageurl, ['remove' => $member->id]))->out(false),
    ];
}
$owner = $record->ownerid ? core_user::get_user($record->ownerid) : null;

echo $OUTPUT->header();
echo $OUTPUT->heading($title, 3);
echo html_writer::tag('p', get_string('shareintro', 'local_competencygrid'), ['class' => 'text-muted']);
$memberdata = [
    'owner' => $owner ? fullname($owner) : '-',
    'hasmembers' => !empty($members),
    'members' => $members,
    'sesskey' => sesskey(),
];
echo $OUTPUT->render_from_template('local_competencygrid/members', $memberdata);
$form->display();
$settingsurl = new moodle_url('/local/competencygrid/edit.php', ['id' => $id]);
echo html_writer::tag('p', html_writer::link($settingsurl, get_string('visibilityhint', 'local_competencygrid')));
echo $OUTPUT->footer();
