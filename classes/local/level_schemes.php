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

/**
 * Level schemes (sets of grid columns) configured by the administrator.
 *
 * Format of the setting: a line "[Name]" starts a scheme, every following line
 * "Level name | short description" adds a level. Without any "[Name]" line the
 * whole text forms one scheme.
 *
 * @package    local_competencygrid
 * @copyright  2026 Moodle in Niedersachsen e. V.
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class level_schemes {
    /**
     * Parses a scheme definition.
     *
     * @param string $text Setting value.
     * @return array[] List of ['name' => string, 'levels' => [['name' => string, 'description' => string]]].
     */
    public static function parse(string $text): array {
        $schemes = [];
        $current = null;
        foreach (preg_split('/\R/', $text) as $line) {
            $line = trim(clean_param($line, PARAM_TEXT));
            if ($line === '') {
                continue;
            }
            if (preg_match('/^\[(.+)\]$/u', $line, $matches)) {
                $schemes[] = ['name' => core_text::substr(trim($matches[1]), 0, 255), 'levels' => []];
                $current = count($schemes) - 1;
                continue;
            }
            if ($current === null) {
                $schemes[] = ['name' => get_string('levelschemedefaultname', 'local_competencygrid'), 'levels' => []];
                $current = 0;
            }
            $parts = array_map('trim', explode('|', $line, 2));
            if ($parts[0] === '') {
                continue;
            }
            $schemes[$current]['levels'][] = [
                'name' => core_text::substr($parts[0], 0, 255),
                'description' => core_text::substr($parts[1] ?? '', 0, 255),
            ];
        }
        return array_values(array_filter($schemes, fn($scheme) => !empty($scheme['levels'])));
    }

    /**
     * Returns the configured schemes.
     *
     * @return array[]
     */
    public static function get_all(): array {
        return self::parse((string) get_config('local_competencygrid', 'levelschemes'));
    }

    /**
     * Returns the levels of a configured scheme.
     *
     * @param int $index Position of the scheme in the setting, starting with 0.
     * @return array[] Levels, empty if the scheme does not exist.
     */
    public static function get_levels(int $index): array {
        $schemes = self::get_all();
        return $schemes[$index]['levels'] ?? [];
    }

    /**
     * Returns the options for a select element.
     *
     * @return array Index => "Name (n levels)".
     */
    public static function options(): array {
        $options = [];
        foreach (self::get_all() as $index => $scheme) {
            $a = (object) ['name' => $scheme['name'], 'count' => count($scheme['levels'])];
            $options[$index] = get_string('levelschemeoption', 'local_competencygrid', $a);
        }
        return $options;
    }
}
