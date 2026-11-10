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

namespace local_competencygrid\local;

use core_text;
use local_competencygrid\grid;

/**
 * Selection lists for the metadata fields of a grid.
 *
 * The lists combine the values configured by the administrator with the values
 * already used in grids, so entries made by users become selectable for everybody.
 *
 * @package    local_competencygrid
 * @copyright  2026 Moodle in Niedersachsen e. V.
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class field_options {
    /** @var string[] Fields with a selection list. */
    public const FIELDS = ['subject', 'classlevel', 'edulevel', 'schooltype'];

    /**
     * Whether users may enter values that are not in the list.
     *
     * @return bool
     */
    public static function custom_values_allowed(): bool {
        $setting = get_config('local_competencygrid', 'allowcustomvalues');
        return $setting === false || !empty($setting);
    }

    /**
     * Returns the values configured by the administrator, in their configured order.
     *
     * @param string $field One of self::FIELDS.
     * @return string[]
     */
    public static function configured(string $field): array {
        if (!in_array($field, self::FIELDS, true)) {
            throw new \coding_exception('Unknown field ' . $field);
        }
        $raw = (string) get_config('local_competencygrid', 'options_' . $field);
        $values = [];
        foreach (preg_split('/\R/', $raw) as $line) {
            $value = core_text::substr(trim(clean_param($line, PARAM_TEXT)), 0, 255);
            if ($value !== '') {
                $values[$value] = $value;
            }
        }
        return array_values($values);
    }

    /**
     * Returns the values already used in grids, sorted alphabetically.
     *
     * @param string $field One of self::FIELDS.
     * @return string[]
     */
    public static function used(string $field): array {
        global $DB;
        if (!in_array($field, self::FIELDS, true)) {
            throw new \coding_exception('Unknown field ' . $field);
        }
        $values = [];
        foreach ($DB->get_fieldset_select(grid::TABLE, $field, '1 = 1') as $value) {
            $value = trim((string) $value);
            if ($value !== '') {
                $values[$value] = $value;
            }
        }
        \core_collator::asort($values);
        return array_values($values);
    }

    /**
     * Returns the options for the autocomplete element of a field.
     *
     * Configured values come first in their configured order, then values entered
     * by users (only if custom values are allowed), then the current value.
     *
     * @param string $field One of self::FIELDS.
     * @param string $current Current value of the grid being edited.
     * @return array Value => label.
     */
    public static function options(string $field, string $current = ''): array {
        $options = [];
        foreach (self::configured($field) as $value) {
            $options[$value] = $value;
        }
        if (self::custom_values_allowed()) {
            foreach (self::used($field) as $value) {
                $options[$value] = $value;
            }
        }
        $current = trim($current);
        if ($current !== '') {
            $options[$current] = $current;
        }
        return $options;
    }
}
