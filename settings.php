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
