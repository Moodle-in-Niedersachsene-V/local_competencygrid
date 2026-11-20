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

use local_competencygrid\local\exacomp_exporter;
use local_competencygrid\local\exacomp_importer;
use local_competencygrid\local\feed;

/**
 * Tests for the feed of the school instances.
 *
 * @package    local_competencygrid
 * @copyright  2026 Moodle in Niedersachsen e. V.
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[\PHPUnit\Framework\Attributes\CoversClass(feed::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(exacomp_exporter::class)]
final class feed_test extends \advanced_testcase {
    /**
     * Creates a public grid with one topic, one level and one competency.
     *
     * @param string $name Grid name.
     * @return int Grid id.
     */
    protected function create_public_grid(string $name): int {
        global $DB;
        $gridid = grid::create(
            (object) ['name' => $name, 'subject' => 'BWL', 'visibility' => grid::VISIBILITY_SITE],
            [['name' => 'AFB I', 'description' => '']]
        );
        $topicid = grid::add_topic($gridid, 'Thema');
        $levelid = (int) $DB->get_field(grid::TABLE_LEVEL, 'id', ['gridid' => $gridid]);
        grid::add_competency($gridid, $topicid, $levelid, 'Kompetenz ' . $name);
        return $gridid;
    }

    /**
     * Only public grids shared with the schools are in the feed, under the fixed feed source.
     */
    public function test_feed_contains_shared_public_grids(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
        set_config('feedenabled', 1, 'local_competencygrid');
        set_config('feedsourcename', 'Material-Hub', 'local_competencygrid');
        $shared = $this->create_public_grid('Geteilt');
        $this->create_public_grid('Nicht geteilt');
        grid::set_school_sync($shared, true);

        $xml = simplexml_load_string(feed::get_xml());
        $this->assertSame(feed::source(), (string) $xml['source']);
        $this->assertSame('Material-Hub', (string) $xml['sourcename']);
        $subjects = $xml->xpath('//subjects/subject');
        $this->assertCount(1, $subjects);
        $this->assertSame((string) (exacomp_exporter::ID_OFFSET + $shared), (string) $subjects[0]['id']);
        $this->assertSame('1', (string) $subjects[0]->version);
        $this->assertSame('Geteilt', (string) $xml->xpath('//edulevels/edulevel/title')[0]);
        foreach ($xml->xpath('//*[@source]') as $element) {
            $this->assertSame(feed::source(), (string) $element['source']);
        }
    }

    /**
     * Every change of a shared grid raises the version and reaches the cached feed.
     */
    public function test_changes_raise_version(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
        set_config('feedenabled', 1, 'local_competencygrid');
        $gridid = $this->create_public_grid('Raster');
        grid::set_school_sync($gridid, true);
        feed::get_xml();

        $topicid = grid::add_topic($gridid, 'Neues Thema');
        grid::record_change($gridid, 'topic', 'created', $topicid);

        $xml = simplexml_load_string(feed::get_xml());
        $this->assertSame('2', (string) $xml->xpath('//subjects/subject/version')[0]);
        $this->assertCount(2, $xml->xpath('//subjects/subject/topics/topic'));
    }

    /**
     * Withdrawing, making the grid private or deleting it removes it from the feed.
     */
    public function test_grid_leaves_feed(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
        set_config('feedenabled', 1, 'local_competencygrid');
        $first = $this->create_public_grid('Eins');
        $second = $this->create_public_grid('Zwei');
        $third = $this->create_public_grid('Drei');
        foreach ([$first, $second, $third] as $gridid) {
            grid::set_school_sync($gridid, true);
        }
        $this->assertCount(3, simplexml_load_string(feed::get_xml())->xpath('//subjects/subject'));

        grid::set_school_sync($first, false);
        grid::update((object) ['id' => $second, 'visibility' => grid::VISIBILITY_PRIVATE]);
        grid::delete($third);

        $this->assertCount(0, simplexml_load_string(feed::get_xml())->xpath('//subjects/subject'));
        $this->assertSame(0, (int) grid::get($second)->schoolsync);
    }

    /**
     * Private grids cannot be shared, and only managers of a grid may share it.
     */
    public function test_release_rules(): void {
        $this->resetAfterTest();
        $owner = $this->getDataGenerator()->create_user();
        $other = $this->getDataGenerator()->create_user();
        $this->setUser($owner);
        set_config('feedenabled', 1, 'local_competencygrid');
        $gridid = grid::create((object) ['name' => 'Privat']);
        $record = grid::get($gridid);

        $this->assertTrue(grid::can_release($record, (int) $owner->id));
        $this->assertFalse(grid::can_release($record, (int) $other->id));
        set_config('feedenabled', 0, 'local_competencygrid');
        $this->assertFalse(grid::can_release($record, (int) $owner->id));

        $this->expectException(\moodle_exception::class);
        grid::set_school_sync($gridid, true);
    }

    /**
     * Grids imported from Komet are written with feed ids, so two copies of the same file do not clash.
     */
    public function test_imported_grids_get_unique_feed_ids(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
        set_config('feedenabled', 1, 'local_competencygrid');
        $content = file_get_contents(__DIR__ . '/fixtures/komet_bwl.xml');
        $ids = array_merge(
            exacomp_importer::import($content, grid::VISIBILITY_SITE),
            exacomp_importer::import($content, grid::VISIBILITY_SITE)
        );
        foreach ($ids as $gridid) {
            grid::set_school_sync($gridid, true);
        }

        $xml = simplexml_load_string(feed::get_xml());
        $descriptorids = array_map(fn($d) => (string) $d['id'], $xml->xpath('/exacomp/descriptors/descriptor'));
        $this->assertCount(88, $descriptorids);
        $this->assertCount(88, array_unique($descriptorids));
        $this->assertNotContains('256360', $descriptorids);
    }
}
