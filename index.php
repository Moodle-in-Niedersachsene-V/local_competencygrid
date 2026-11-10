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
 * Library of competency grids.
 *
 * @package    local_competencygrid
 * @copyright  2026 Moodle in Niedersachsen e. V.
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/licenselib.php');

use local_competencygrid\grid;

require_login();
$context = context_system::instance();
require_capability('local/competencygrid:view', $context);

$scope = optional_param('scope', 'all', PARAM_ALPHA);
$scope = $scope === 'mine' ? 'mine' : 'all';
$search = trim(optional_param('search', '', PARAM_TEXT));

$url = new moodle_url('/local/competencygrid/index.php', ['scope' => $scope]);
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_pagelayout('report');
$PAGE->set_title(get_string('competencygrids', 'local_competencygrid'));
$PAGE->set_heading(get_string('competencygrids', 'local_competencygrid'));

$licenses = [];
foreach (license_manager::get_licenses() as $license) {
    $licenses[$license->shortname] = $license->fullname;
}

$cards = [];
foreach (grid::list_grids($scope, $search) as $record) {
    if ((int) $record->ownerid === (int) $USER->id) {
        $badge = get_string('roleowner', 'local_competencygrid');
        $badgeclass = 'own';
    } else if ($record->memberrole === grid::ROLE_EDITOR) {
        $badge = get_string('roleeditor', 'local_competencygrid');
        $badgeclass = 'own';
    } else if ((int) $record->visibility === grid::VISIBILITY_SITE) {
        $badge = get_string('visibilitysite', 'local_competencygrid');
        $badgeclass = 'site';
    } else {
        $badge = get_string('visibilityprivate', 'local_competencygrid');
        $badgeclass = 'private';
    }
    $subtitle = array_filter([
        format_string($record->edulevel),
        format_string($record->schooltype),
        format_string($record->classlevel),
    ]);
    $cards[] = [
        'name' => format_string($record->name),
        'url' => (new moodle_url('/local/competencygrid/grid.php', ['id' => $record->id]))->out(false),
        'subtitle' => implode(' · ', $subtitle),
        'topiccount' => (int) $record->topiccount,
        'compcount' => (int) $record->compcount,
        'license' => $licenses[$record->license] ?? '',
        'badge' => $badge,
        'badgeclass' => $badgeclass,
        'timemodified' => userdate($record->timemodified, get_string('strftimedatefullshort', 'langconfig')),
    ];
}

$tabs = [];
foreach (['all' => 'tabvisible', 'mine' => 'tabmine'] as $tabscope => $label) {
    $tabs[] = [
        'label' => get_string($label, 'local_competencygrid'),
        'url' => (new moodle_url('/local/competencygrid/index.php', ['scope' => $tabscope]))->out(false),
        'active' => $tabscope === $scope,
    ];
}

$data = [
    'cancreate' => has_capability('local/competencygrid:create', $context),
    'createurl' => (new moodle_url('/local/competencygrid/edit.php'))->out(false),
    'importurl' => (new moodle_url('/local/competencygrid/import.php'))->out(false),
    'actionurl' => (new moodle_url('/local/competencygrid/index.php'))->out(false),
    'scope' => $scope,
    'search' => $search,
    'tabs' => $tabs,
    'hasgrids' => !empty($cards),
    'grids' => $cards,
];

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('local_competencygrid/library', $data);
echo $OUTPUT->footer();
