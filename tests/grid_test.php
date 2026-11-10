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

use core_external\external_api;
use local_competencygrid\external\save_competency;
use local_competencygrid\local\level_schemes;

/**
 * Tests for permissions and the editing web services.
 *
 * @package    local_competencygrid
 * @copyright  2026 Moodle in Niedersachsen e. V.
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[\PHPUnit\Framework\Attributes\CoversClass(grid::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(save_competency::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(level_schemes::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(event\grid_created::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(event\member_added::class)]
final class grid_test extends \advanced_testcase {
    /**
     * Owner, members and other users get the expected permissions.
     */
    public function test_permissions(): void {
        global $DB;
        $this->resetAfterTest();
        $owner = $this->getDataGenerator()->create_user();
        $editor = $this->getDataGenerator()->create_user();
        $viewer = $this->getDataGenerator()->create_user();
        $other = $this->getDataGenerator()->create_user();

        $this->setUser($owner);
        $gridid = grid::create((object) ['name' => 'Test'], level_schemes::get_levels(0));
        grid::add_member($gridid, (int) $editor->id, grid::ROLE_EDITOR);
        grid::add_member($gridid, (int) $viewer->id, grid::ROLE_VIEWER);
        $record = grid::get($gridid);

        $this->assertTrue(grid::can_manage($record, (int) $owner->id));
        $this->assertTrue(grid::can_edit($record, (int) $editor->id));
        $this->assertFalse(grid::can_manage($record, (int) $editor->id));
        $this->assertTrue(grid::can_view($record, (int) $viewer->id));
        $this->assertFalse(grid::can_edit($record, (int) $viewer->id));
        $this->assertFalse(grid::can_view($record, (int) $other->id));

        $record->visibility = grid::VISIBILITY_SITE;
        $this->assertTrue(grid::can_view($record, (int) $other->id));
        $this->assertFalse(grid::can_edit($record, (int) $other->id));
        $this->assertSame(3, $DB->count_records(grid::TABLE_LEVEL, ['gridid' => $gridid]));
    }

    /**
     * The library lists own, shared and visible grids and filters by search text.
     */
    public function test_list_grids(): void {
        $this->resetAfterTest();
        $owner = $this->getDataGenerator()->create_user();
        $other = $this->getDataGenerator()->create_user();
        $this->setUser($owner);
        $private = grid::create((object) ['name' => 'Privat Mathe', 'subject' => 'Mathematik']);
        $public = grid::create((object) ['name' => 'Offen BWL', 'subject' => 'BWL', 'visibility' => grid::VISIBILITY_SITE]);

        $this->assertEqualsCanonicalizing([$private, $public], array_keys(grid::list_grids('mine')));
        $this->assertSame([$public], array_keys(grid::list_grids('all', 'bwl')));

        $this->setUser($other);
        $this->assertSame([$public], array_keys(grid::list_grids('all')));
        $this->assertSame([], array_keys(grid::list_grids('mine')));
    }

    /**
     * Saving a competency works, outdated saves are refused, outsiders are refused.
     */
    public function test_save_competency(): void {
        global $DB;
        $this->resetAfterTest();
        $owner = $this->getDataGenerator()->create_user();
        $other = $this->getDataGenerator()->create_user();
        $this->setUser($owner);
        $gridid = grid::create((object) ['name' => 'Test'], level_schemes::get_levels(0));
        $topicid = grid::add_topic($gridid, 'Thema');
        $levelid = (int) $DB->get_field(grid::TABLE_LEVEL, 'MIN(id)', ['gridid' => $gridid]);

        $result = save_competency::execute($gridid, 0, $topicid, $levelid, 'Kompetenz A');
        $result = external_api::clean_returnvalue(save_competency::execute_returns(), $result);
        $this->assertCount(1, $result['grid']['competencies']);
        $comp = $result['grid']['competencies'][0];
        $this->assertSame('Kompetenz A', $comp['title']);

        $DB->set_field(grid::TABLE_COMP, 'timemodified', $comp['timemodified'] + 10, ['id' => $comp['id']]);
        try {
            save_competency::execute($gridid, $comp['id'], $topicid, $levelid, 'Kompetenz B', $comp['timemodified']);
            $this->fail('Conflict expected');
        } catch (\moodle_exception $e) {
            $this->assertSame('conflict', $e->errorcode);
        }

        $this->setUser($other);
        $this->expectException(\moodle_exception::class);
        save_competency::execute($gridid, 0, $topicid, $levelid, 'Fremd');
    }

    /**
     * Level templates are parsed from the setting, with and without template names.
     */
    public function test_level_schemes(): void {
        $this->resetAfterTest();
        $schemes = level_schemes::parse("[A]\nEins | erste\nZwei\n\n[Leer]\n\n[B]\nX | y");
        $this->assertSame(['A', 'B'], array_column($schemes, 'name'));
        $this->assertSame(['name' => 'Zwei', 'description' => ''], $schemes[0]['levels'][1]);
        $this->assertSame('erste', $schemes[0]['levels'][0]['description']);

        $plain = level_schemes::parse("Stufe 1\nStufe 2");
        $this->assertCount(1, $plain);
        $this->assertCount(2, $plain[0]['levels']);

        set_config('levelschemes', "[Vier]\n1\n2\n3\n4\n[GER]\nA1\nA2", 'local_competencygrid');
        $this->assertCount(4, level_schemes::get_levels(0));
        $this->assertSame([], level_schemes::get_levels(5));
    }

    /**
     * Adding a template to an existing grid skips levels it already has.
     */
    public function test_add_levels_skips_existing(): void {
        global $DB;
        $this->resetAfterTest();
        $this->setAdminUser();
        $gridid = grid::create((object) ['name' => 'Test'], [['name' => 'A1', 'description' => '']]);
        $levels = [
            ['name' => 'a1', 'description' => ''],
            ['name' => 'A2', 'description' => 'x'],
            ['name' => 'B1', 'description' => ''],
        ];
        $added = grid::add_levels($gridid, $levels);
        $this->assertSame(2, $added);
        $names = $DB->get_fieldset_select(grid::TABLE_LEVEL, 'name', 'gridid = ? ORDER BY sortorder', [$gridid]);
        $this->assertSame(['A1', 'A2', 'B1'], $names);
    }

    /**
     * Creating a grid, sharing it and changing its content raise events.
     */
    public function test_events(): void {
        global $DB;
        $this->resetAfterTest();
        $this->setAdminUser();
        $user = $this->getDataGenerator()->create_user();
        $sink = $this->redirectEvents();

        $gridid = grid::create((object) ['name' => 'Test'], level_schemes::get_levels(0));
        grid::add_member($gridid, (int) $user->id, grid::ROLE_EDITOR);
        $memberid = (int) $DB->get_field(grid::TABLE_MEMBER, 'id', ['gridid' => $gridid]);
        grid::remove_member($gridid, $memberid);
        $topicid = grid::add_topic($gridid, 'Thema');
        grid::record_change($gridid, 'topic', 'created', $topicid);
        grid::delete($gridid);

        $classes = array_map(fn($event) => get_class($event), $sink->get_events());
        $sink->close();
        $expected = [
            event\grid_created::class,
            event\member_added::class,
            event\member_removed::class,
            event\grid_content_updated::class,
            event\grid_deleted::class,
        ];
        $this->assertSame($expected, $classes);
    }
}
