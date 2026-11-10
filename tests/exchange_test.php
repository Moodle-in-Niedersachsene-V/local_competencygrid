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
use local_competencygrid\local\moodlecsv_exporter;

/**
 * Tests for import and export of competency grids.
 *
 * @package    local_competencygrid
 * @copyright  2026 Moodle in Niedersachsen e. V.
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[\PHPUnit\Framework\Attributes\CoversClass(exacomp_importer::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(exacomp_exporter::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(moodlecsv_exporter::class)]
final class exchange_test extends \advanced_testcase {
    /**
     * Imports the fixture file.
     *
     * @return \stdClass Imported grid.
     */
    protected function import_fixture(): \stdClass {
        $ids = exacomp_importer::import(file_get_contents(__DIR__ . '/fixtures/komet_bwl.xml'));
        $this->assertCount(1, $ids);
        return grid::get(reset($ids));
    }

    /**
     * Counts the items of a grid.
     *
     * @param int $gridid Grid id.
     * @return array Counts of levels, topics and competencies.
     */
    protected function counts(int $gridid): array {
        global $DB;
        return [
            $DB->count_records(grid::TABLE_LEVEL, ['gridid' => $gridid]),
            $DB->count_records(grid::TABLE_TOPIC, ['gridid' => $gridid]),
            $DB->count_records(grid::TABLE_COMP, ['gridid' => $gridid]),
        ];
    }

    /**
     * A Komet export is imported with all topics, levels and competencies.
     */
    public function test_import_komet_file(): void {
        global $DB;
        $this->resetAfterTest();
        $this->setAdminUser();

        $record = $this->import_fixture();
        $this->assertSame('Betriebswirtschaftslehre · Jahrgang 11', $record->name);
        $this->assertSame('Sekundarstufe II', $record->edulevel);
        $this->assertSame('KOMET-IDQQDAAcEDwIHBwsNDQ8CgIA1', $record->source);
        $this->assertSame([3, 7, 44], $this->counts((int) $record->id));

        $levels = array_values($DB->get_records(grid::TABLE_LEVEL, ['gridid' => $record->id], 'sortorder ASC'));
        $this->assertSame('AFB I – Wiedergeben', $levels[0]->name);
        $this->assertSame('AFB III – Beurteilen', $levels[2]->name);
        $this->assertSame('12039', $levels[2]->sourceid);
    }

    /**
     * Exporting and importing again keeps structure, texts and ids.
     */
    public function test_exacomp_roundtrip(): void {
        global $DB;
        $this->resetAfterTest();
        $this->setAdminUser();

        $record = $this->import_fixture();
        $newtopic = grid::add_topic((int) $record->id, 'Neues Thema');
        $level = $DB->get_records(grid::TABLE_LEVEL, ['gridid' => $record->id], 'sortorder ASC', '*', 0, 1);
        $newcomp = grid::add_competency((int) $record->id, $newtopic, (int) reset($level)->id, 'Neue Kompetenz');

        $xml = (new exacomp_exporter(grid::get((int) $record->id)))->export();
        $this->assertStringContainsString('id="' . (exacomp_exporter::ID_OFFSET + $newcomp) . '"', $xml);
        $this->assertStringContainsString('id="256360"', $xml);

        $ids = exacomp_importer::import($xml);
        $copy = grid::get(reset($ids));
        $this->assertSame([3, 8, 45], $this->counts((int) $copy->id));
        $this->assertSame($record->source, $copy->source);
        $this->assertSame($record->subjectsourceid, $copy->subjectsourceid);

        $original = $DB->get_records_menu(grid::TABLE_COMP, ['gridid' => $record->id], 'id', 'sourceid, title');
        $copied = $DB->get_records_menu(grid::TABLE_COMP, ['gridid' => $copy->id], 'id', 'sourceid, title');
        unset($copied[(string) (exacomp_exporter::ID_OFFSET + $newcomp)]);
        unset($original['']);
        $this->assertCount(44, $original);
        $this->assertEquals($original, $copied);
    }

    /**
     * Files with a document type declaration are refused.
     */
    public function test_import_refuses_doctype(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
        $this->expectException(\moodle_exception::class);
        exacomp_importer::import('<?xml version="1.0"?><!DOCTYPE x [<!ENTITY a "b">]><exacomp></exacomp>');
    }

    /**
     * The Moodle CSV export can be imported by the core competency framework importer.
     */
    public function test_moodlecsv_import_into_core(): void {
        $this->resetAfterTest();
        $this->setAdminUser();

        $record = $this->import_fixture();
        $rows = (new moodlecsv_exporter($record))->get_rows();
        $this->assertCount(14, $rows[0]);

        $handle = fopen('php://temp', 'w+');
        foreach ($rows as $row) {
            fputcsv($handle, $row, ',', '"', '');
        }
        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        $importer = new \tool_lpimportcsv\framework_importer($csv);
        $this->assertSame('', (string) $importer->get_error());
        $framework = $importer->import();
        $this->assertNotEmpty($framework->get('id'));

        // Topics, level nodes per topic and competencies.
        $count = \core_competency\api::count_competencies(['competencyframeworkid' => $framework->get('id')]);
        $this->assertSame(7 + 21 + 44, $count);
    }
}
