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

use local_competencygrid\grid;

/**
 * Form for importing exacomp XML files.
 *
 * @package    local_competencygrid
 * @copyright  2026 Moodle in Niedersachsen e. V.
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class import_form extends \moodleform {
    /**
     * Form definition.
     *
     * @return void
     */
    protected function definition(): void {
        $mform = $this->_form;
        $mform->addElement('static', 'importinfo', '', get_string('importinfo', 'local_competencygrid'));
        $options = ['accepted_types' => ['.xml'], 'maxbytes' => 10 * 1024 * 1024];
        $mform->addElement('filepicker', 'importfile', get_string('importfile', 'local_competencygrid'), null, $options);
        $mform->addRule('importfile', null, 'required', null, 'client');
        $mform->addHelpButton('importfile', 'importfile', 'local_competencygrid');
        $visibilities = [
            grid::VISIBILITY_PRIVATE => get_string('visibilityprivate', 'local_competencygrid'),
            grid::VISIBILITY_SITE => get_string('visibilitysite', 'local_competencygrid'),
        ];
        $mform->addElement('select', 'visibility', get_string('visibility', 'local_competencygrid'), $visibilities);
        $mform->addHelpButton('visibility', 'visibility', 'local_competencygrid');
        $this->add_action_buttons(true, get_string('import', 'local_competencygrid'));
    }
}
