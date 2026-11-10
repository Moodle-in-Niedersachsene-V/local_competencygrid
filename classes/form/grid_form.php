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

namespace local_competencygrid\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');
require_once($CFG->libdir . '/licenselib.php');

use local_competencygrid\grid;
use local_competencygrid\local\field_options;
use local_competencygrid\local\level_schemes;

/**
 * Form for creating a grid and editing its settings.
 *
 * @package    local_competencygrid
 * @copyright  2026 Moodle in Niedersachsen e. V.
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class grid_form extends \moodleform {
    /**
     * Form definition.
     *
     * @return void
     */
    protected function definition(): void {
        $mform = $this->_form;
        $isnew = empty($this->_customdata['isnew']) ? false : true;

        $mform->addElement('hidden', 'id', 0);
        $mform->setType('id', PARAM_INT);

        $mform->addElement('text', 'name', get_string('gridname', 'local_competencygrid'), ['size' => 60]);
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $mform->addHelpButton('name', 'gridname', 'local_competencygrid');

        $current = $this->_customdata['current'] ?? null;
        $attributes = [
            'multiple' => false,
            'tags' => field_options::custom_values_allowed(),
            'noselectionstring' => get_string('notspecified', 'local_competencygrid'),
        ];
        foreach (field_options::FIELDS as $field) {
            $value = $current ? (string) $current->$field : '';
            // The empty option has an empty label, so the autocomplete shows the no-selection text for it.
            $options = ['' => ''] + field_options::options($field, $value);
            $mform->addElement('autocomplete', $field, get_string($field, 'local_competencygrid'), $options, $attributes);
            $mform->setType($field, PARAM_TEXT);
            $mform->addHelpButton($field, $field, 'local_competencygrid');
        }

        $mform->addElement('text', 'sentencestart', get_string('sentencestart', 'local_competencygrid'), ['size' => 60]);
        $mform->setType('sentencestart', PARAM_TEXT);
        $mform->addHelpButton('sentencestart', 'sentencestart', 'local_competencygrid');

        $mform->addElement('textarea', 'description', get_string('description'), ['rows' => 3, 'cols' => 60]);
        $mform->setType('description', PARAM_TEXT);

        $licenses = ['' => get_string('choosedots')] + \license_manager::get_active_licenses_as_array();
        $mform->addElement('select', 'license', get_string('license', 'local_competencygrid'), $licenses);
        $mform->addHelpButton('license', 'license', 'local_competencygrid');

        $visibilities = [
            grid::VISIBILITY_PRIVATE => get_string('visibilityprivate', 'local_competencygrid'),
            grid::VISIBILITY_SITE => get_string('visibilitysite', 'local_competencygrid'),
        ];
        $mform->addElement('select', 'visibility', get_string('visibility', 'local_competencygrid'), $visibilities);
        $mform->addHelpButton('visibility', 'visibility', 'local_competencygrid');

        $schemes = level_schemes::options();
        $schemekey = $isnew ? 'levelscheme' : 'addlevelscheme';
        $schemeoptions = [-1 => get_string($isnew ? 'levelschemenone' : 'levelschemekeep', 'local_competencygrid')] + $schemes;
        $mform->addElement('select', 'levelscheme', get_string($schemekey, 'local_competencygrid'), $schemeoptions);
        $mform->setType('levelscheme', PARAM_INT);
        $mform->setDefault('levelscheme', $isnew && $schemes ? 0 : -1);
        $mform->addHelpButton('levelscheme', $schemekey, 'local_competencygrid');

        $this->add_action_buttons(true, $isnew ? get_string('creategrid', 'local_competencygrid') : null);
    }

    /**
     * Validates the selection fields.
     *
     * @param array $data Submitted data.
     * @param array $files Submitted files.
     * @return array Errors.
     */
    public function validation($data, $files): array {
        $errors = parent::validation($data, $files);
        $current = $this->_customdata['current'] ?? null;
        foreach (field_options::FIELDS as $field) {
            $value = trim((string) ($data[$field] ?? ''));
            $allowed = field_options::options($field, $current ? (string) $current->$field : '');
            if (\core_text::strlen($value) > 255) {
                $errors[$field] = get_string('maximumchars', '', 255);
            } else if ($value !== '' && !field_options::custom_values_allowed() && !array_key_exists($value, $allowed)) {
                $errors[$field] = get_string('fieldvaluenotallowed', 'local_competencygrid');
            }
        }
        return $errors;
    }
}
