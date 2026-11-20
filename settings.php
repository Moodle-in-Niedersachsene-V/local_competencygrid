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
 * Admin settings for local_competencygrid.
 *
 * @package    local_competencygrid
 * @copyright  2026 Moodle in Niedersachsen e. V.
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $settings = new admin_settingpage('local_competencygrid', get_string('pluginname', 'local_competencygrid'));
    $ADMIN->add('localplugins', $settings);
    if ($ADMIN->fulltree) {
        $settings->add(new admin_setting_configcheckbox(
            'local_competencygrid/showinprimarynav',
            get_string('showinprimarynav', 'local_competencygrid'),
            get_string('showinprimarynav_desc', 'local_competencygrid'),
            1
        ));

        $cohorts = [0 => get_string('editorcohortnone', 'local_competencygrid')];
        foreach ($DB->get_records('cohort', null, 'name ASC', 'id, name, idnumber') as $cohort) {
            $suffix = $cohort->idnumber !== '' ? ' (' . s($cohort->idnumber) . ')' : '';
            $cohorts[$cohort->id] = format_string($cohort->name) . $suffix;
        }
        $settings->add(new admin_setting_configselect(
            'local_competencygrid/editorcohort',
            get_string('editorcohort', 'local_competencygrid'),
            get_string('editorcohort_desc', 'local_competencygrid'),
            0,
            $cohorts
        ));

        $settings->add(new admin_setting_heading(
            'local_competencygrid/feedheading',
            get_string('feedheading', 'local_competencygrid'),
            get_string('feedheading_desc', 'local_competencygrid')
        ));
        $settings->add(new admin_setting_configcheckbox(
            'local_competencygrid/feedenabled',
            get_string('feedenabled', 'local_competencygrid'),
            get_string('feedenabled_desc', 'local_competencygrid'),
            0
        ));
        $settings->add(new admin_setting_configtext(
            'local_competencygrid/feedsourcename',
            get_string('feedsourcename', 'local_competencygrid'),
            get_string('feedsourcename_desc', 'local_competencygrid'),
            '',
            PARAM_TEXT
        ));
        $feedinfo = (object) [
            'url' => \local_competencygrid\local\feed::url()->out(false),
            'source' => s(\local_competencygrid\local\feed::source()),
        ];
        $settings->add(new admin_setting_description(
            'local_competencygrid/feedinfo',
            get_string('feedinfo', 'local_competencygrid'),
            get_string('feedinfo_desc', 'local_competencygrid', $feedinfo)
        ));

        $settings->add(new admin_setting_heading(
            'local_competencygrid/levelschemesheading',
            get_string('levelschemes', 'local_competencygrid'),
            ''
        ));
        $settings->add(new admin_setting_configtextarea(
            'local_competencygrid/levelschemes',
            get_string('levelschemes', 'local_competencygrid'),
            get_string('levelschemes_desc', 'local_competencygrid'),
            get_string('levelschemes_default', 'local_competencygrid'),
            PARAM_TEXT,
            80,
            18
        ));

        $settings->add(new admin_setting_heading(
            'local_competencygrid/fieldoptionsheading',
            get_string('fieldoptionsheading', 'local_competencygrid'),
            get_string('fieldoptionsheading_desc', 'local_competencygrid')
        ));
        $settings->add(new admin_setting_configcheckbox(
            'local_competencygrid/allowcustomvalues',
            get_string('allowcustomvalues', 'local_competencygrid'),
            get_string('allowcustomvalues_desc', 'local_competencygrid'),
            1
        ));
        foreach (['subject', 'classlevel', 'edulevel', 'schooltype'] as $field) {
            $settings->add(new admin_setting_configtextarea(
                'local_competencygrid/options_' . $field,
                get_string('options_' . $field, 'local_competencygrid'),
                get_string('options_desc', 'local_competencygrid'),
                get_string('options_' . $field . '_default', 'local_competencygrid'),
                PARAM_TEXT,
                60,
                8
            ));
        }
    }
}
