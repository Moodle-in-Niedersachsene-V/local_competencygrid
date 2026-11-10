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

use local_competencygrid\grid;
use stdClass;

/**
 * Exports a competency grid as CSV for the Moodle competency framework import (tool_lpimportcsv).
 *
 * The hierarchy is topic, then level, then competency.
 *
 * @package    local_competencygrid
 * @copyright  2026 Moodle in Niedersachsen e. V.
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class moodlecsv_exporter {
    /** @var stdClass Grid record. */
    protected stdClass $grid;

    /**
     * Constructor.
     *
     * @param stdClass $grid Grid record.
     */
    public function __construct(stdClass $grid) {
        $this->grid = $grid;
    }

    /**
     * Suggested file name without extension.
     *
     * @return string
     */
    public function get_filename(): string {
        return clean_filename('competencyframework_' . str_replace(' · ', ' - ', $this->grid->name));
    }

    /**
     * Stable id number of the framework.
     *
     * @return string
     */
    public function framework_idnumber(): string {
        return 'cg-' . substr(hash('sha256', $this->grid->source . '-' . $this->grid->id), 0, 12);
    }

    /**
     * Builds one CSV row in the column order of tool_lpimportcsv.
     *
     * @param string $parent Parent id number.
     * @param string $idnumber Id number.
     * @param string $shortname Short name.
     * @param string $description Description (plain text).
     * @param bool $isframework Whether this is the framework row.
     * @param string $scalevalues Scale values (framework only).
     * @param string $scaleconfig Scale configuration (framework only).
     * @param string $taxonomies Taxonomies (framework only).
     * @return array
     */
    protected function row(
        string $parent,
        string $idnumber,
        string $shortname,
        string $description,
        bool $isframework = false,
        string $scalevalues = '',
        string $scaleconfig = '',
        string $taxonomies = ''
    ): array {
        return [
            $parent,
            $idnumber,
            shorten_text($shortname, 100),
            $description === '' ? '' : s($description),
            FORMAT_HTML,
            $scalevalues,
            $scaleconfig,
            '',
            '',
            $isframework ? '' : 'null',
            '',
            $isframework ? '' : $idnumber,
            $isframework ? 1 : 0,
            $taxonomies,
        ];
    }

    /**
     * Returns all rows including the header row.
     *
     * @return array[]
     */
    public function get_rows(): array {
        global $DB;
        $gridid = (int) $this->grid->id;
        $levels = $DB->get_records(grid::TABLE_LEVEL, ['gridid' => $gridid], 'sortorder ASC, id ASC');
        $topics = $DB->get_records(grid::TABLE_TOPIC, ['gridid' => $gridid], 'sortorder ASC, id ASC');
        $comps = $DB->get_records(grid::TABLE_COMP, ['gridid' => $gridid], 'sortorder ASC, id ASC');

        $rows = [\tool_lpimportcsv\framework_importer::list_required_headers()];
        $scalevalues = get_string('scalenotreached', 'local_competencygrid') . ',' .
            get_string('scalereached', 'local_competencygrid');
        $scaleconfig = json_encode([
            ['scaleid' => '0'],
            ['id' => 1, 'scaledefault' => 1, 'proficient' => 0],
            ['id' => 2, 'scaledefault' => 0, 'proficient' => 1],
        ]);
        $parts = array_filter([$this->grid->edulevel, $this->grid->schooltype, $this->grid->classlevel]);
        $description = trim(implode(' · ', $parts));
        $rows[] = $this->row(
            '',
            $this->framework_idnumber(),
            $this->grid->name,
            $description,
            true,
            $scalevalues,
            $scaleconfig,
            'domain,level,competency'
        );

        $prefix = preg_replace('/[\s.…]+$/u', '', trim((string) $this->grid->sentencestart));
        foreach ($topics as $topic) {
            $topicidnumber = 'T' . exacomp_exporter::item_id($topic);
            $rows[] = $this->row('', $topicidnumber, $topic->name, '');
            foreach ($levels as $level) {
                $inlevel = [];
                foreach ($comps as $comp) {
                    if ((int) $comp->topicid === (int) $topic->id && (int) $comp->levelid === (int) $level->id) {
                        $inlevel[] = $comp;
                    }
                }
                if (!$inlevel) {
                    continue;
                }
                $levelidnumber = $topicidnumber . '-L' . exacomp_exporter::item_id($level);
                $rows[] = $this->row($topicidnumber, $levelidnumber, $level->name, (string) $level->description);
                foreach ($inlevel as $comp) {
                    $text = $prefix !== '' ? $prefix . ' ' . $comp->title : $comp->title;
                    $rows[] = $this->row($levelidnumber, 'C' . exacomp_exporter::item_id($comp), $comp->title, $text);
                }
            }
        }
        return $rows;
    }

    /**
     * Sends the CSV file to the browser.
     *
     * @return void
     */
    public function download(): void {
        global $CFG;
        require_once($CFG->libdir . '/csvlib.class.php');
        $writer = new \csv_export_writer();
        $writer->set_filename($this->get_filename());
        foreach ($this->get_rows() as $row) {
            $writer->add_data($row);
        }
        $writer->download_file();
    }
}
