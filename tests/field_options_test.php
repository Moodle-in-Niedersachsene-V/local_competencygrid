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

namespace local_competencygrid;

use local_competencygrid\local\field_options;

/**
 * Tests for the selection lists of the grid metadata.
 *
 * @package    local_competencygrid
 * @copyright  2026 Moodle in Niedersachsen e. V.
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[\PHPUnit\Framework\Attributes\CoversClass(field_options::class)]
final class field_options_test extends \advanced_testcase {
    /**
     * Configured values come first, values entered by users follow.
     */
    public function test_options_with_custom_values(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
        set_config('options_subject', "Mathematik\n  Deutsch \n\nMathematik", 'local_competencygrid');
        set_config('allowcustomvalues', 1, 'local_competencygrid');
        grid::create((object) ['name' => 'A', 'subject' => 'Biologie']);
        grid::create((object) ['name' => 'B', 'subject' => 'Deutsch']);

        $this->assertSame(['Mathematik', 'Deutsch'], field_options::configured('subject'));
        $this->assertSame(
            ['Mathematik', 'Deutsch', 'Biologie'],
            array_keys(field_options::options('subject'))
        );
    }

    /**
     * Without custom values only the configured list and the current value are offered.
     */
    public function test_options_without_custom_values(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
        set_config('options_edulevel', "Sekundarstufe I\nSekundarstufe II", 'local_competencygrid');
        set_config('allowcustomvalues', 0, 'local_competencygrid');
        grid::create((object) ['name' => 'A', 'edulevel' => 'Primarstufe']);

        $this->assertFalse(field_options::custom_values_allowed());
        $this->assertSame(['Sekundarstufe I', 'Sekundarstufe II'], array_keys(field_options::options('edulevel')));
        $this->assertSame(
            ['Sekundarstufe I', 'Sekundarstufe II', 'Primarstufe'],
            array_keys(field_options::options('edulevel', 'Primarstufe'))
        );
    }

    /**
     * Unknown fields are refused.
     */
    public function test_unknown_field(): void {
        $this->expectException(\coding_exception::class);
        field_options::configured('name');
    }
}
