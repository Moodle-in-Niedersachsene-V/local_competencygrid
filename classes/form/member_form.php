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
 * Form for adding a collaborator to a grid.
 *
 * @package    local_competencygrid
 * @copyright  2026 Moodle in Niedersachsen e. V.
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class member_form extends \moodleform {
    /**
     * Form definition.
     *
     * @return void
     */
    protected function definition(): void {
        $mform = $this->_form;
        $mform->addElement('hidden', 'id', 0);
        $mform->setType('id', PARAM_INT);
        $group = [];
        $label = get_string('memberidentifier', 'local_competencygrid');
        $group[] = $mform->createElement('text', 'identifier', $label, ['size' => 40, 'placeholder' => $label]);
        $roles = [
            grid::ROLE_EDITOR => get_string('roleeditor', 'local_competencygrid'),
            grid::ROLE_VIEWER => get_string('roleviewer', 'local_competencygrid'),
        ];
        $group[] = $mform->createElement('select', 'role', get_string('role', 'local_competencygrid'), $roles);
        $group[] = $mform->createElement('submit', 'addmember', get_string('addmember', 'local_competencygrid'));
        $mform->addGroup($group, 'membergroup', get_string('addmember', 'local_competencygrid'), ' ', false);
        $mform->setType('identifier', PARAM_RAW_TRIMMED);
        $mform->addHelpButton('membergroup', 'memberidentifier', 'local_competencygrid');
    }

    /**
     * Validates that the identifier matches exactly one active user.
     *
     * @param array $data Submitted data.
     * @param array $files Submitted files.
     * @return array Errors.
     */
    public function validation($data, $files): array {
        $errors = parent::validation($data, $files);
        if (self::find_user((string) ($data['identifier'] ?? '')) === null) {
            $errors['membergroup'] = get_string('usernotfound', 'local_competencygrid');
        }
        return $errors;
    }

    /**
     * Finds an active, confirmed user by email address or username.
     *
     * @param string $identifier Email address or username.
     * @return \stdClass|null
     */
    public static function find_user(string $identifier): ?\stdClass {
        global $CFG, $DB;
        $identifier = trim($identifier);
        if ($identifier === '') {
            return null;
        }
        $params = ['deleted' => 0, 'suspended' => 0, 'confirmed' => 1, 'mnethostid' => $CFG->mnet_localhost_id];
        if (strpos($identifier, '@') !== false) {
            $select = $DB->sql_equal('email', ':email', false) . ' AND deleted = :deleted AND suspended = :suspended
                       AND confirmed = :confirmed AND mnethostid = :mnethostid';
            $users = $DB->get_records_select('user', $select, $params + ['email' => $identifier], '', 'id', 0, 2);
        } else {
            $params['username'] = \core_text::strtolower($identifier);
            $users = $DB->get_records('user', $params, '', 'id', 0, 2);
        }
        if (count($users) !== 1) {
            return null;
        }
        $user = reset($users);
        return isguestuser($user->id) ? null : \core_user::get_user($user->id);
    }
}
