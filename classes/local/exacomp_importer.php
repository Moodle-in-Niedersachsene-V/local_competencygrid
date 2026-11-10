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
use moodle_exception;
use SimpleXMLElement;

/**
 * Imports exacomp XML files (block_exacomp, Komet) as competency grids.
 *
 * Every subject in the file becomes one grid. Topics become rows, niveaus become
 * levels (columns) and descriptors become competencies. Examples (materials) are
 * not imported in this version.
 *
 * @package    local_competencygrid
 * @copyright  2026 Moodle in Niedersachsen e. V.
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class exacomp_importer {
    /**
     * Cleans a text from the file for use as a name.
     *
     * @param mixed $value Raw value.
     * @param int $maxlength Maximum length, 0 for unlimited.
     * @return string
     */
    protected static function clean($value, int $maxlength = 255): string {
        $text = trim(clean_param((string) $value, PARAM_TEXT));
        return $maxlength > 0 ? core_text::substr($text, 0, $maxlength) : $text;
    }

    /**
     * Parses the XML content.
     *
     * @param string $content File content.
     * @return SimpleXMLElement
     */
    protected static function parse(string $content): SimpleXMLElement {
        // Document type declarations are refused to rule out entity expansion attacks.
        if (stripos($content, '<!DOCTYPE') !== false || stripos($content, '<!ENTITY') !== false) {
            throw new moodle_exception('importinvalidxml', 'local_competencygrid');
        }
        $previous = libxml_use_internal_errors(true);
        $xml = simplexml_load_string($content, SimpleXMLElement::class, LIBXML_NONET | LIBXML_NOCDATA);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);
        if ($xml === false || $xml->getName() !== 'exacomp') {
            throw new moodle_exception('importinvalidxml', 'local_competencygrid');
        }
        return $xml;
    }

    /**
     * Imports the file and creates one grid per subject.
     *
     * @param string $content XML content.
     * @param int $visibility Visibility of the new grids.
     * @return int[] Ids of the created grids.
     */
    public static function import(string $content, int $visibility = grid::VISIBILITY_PRIVATE): array {
        global $DB;
        $xml = self::parse($content);
        $source = self::clean((string) $xml['source']);

        $niveaus = [];
        foreach ($xml->xpath('/exacomp/niveaus/niveau') ?: [] as $niveau) {
            $niveaus[(string) $niveau['id']] = [
                'title' => self::clean($niveau->title) ?: get_string('nolevel', 'local_competencygrid'),
                'sorting' => (int) $niveau->sorting,
            ];
        }
        $descriptors = [];
        foreach ($xml->xpath('/exacomp/descriptors/descriptor') ?: [] as $descriptor) {
            $descriptors[(string) $descriptor['id']] = [
                'title' => self::clean($descriptor->title, 0),
                'niveauid' => isset($descriptor->niveauid) ? (string) $descriptor->niveauid['id'] : '',
            ];
        }

        $created = [];
        $transaction = $DB->start_delegated_transaction();
        foreach ($xml->xpath('/exacomp/edulevels/edulevel') ?: [] as $edulevel) {
            foreach ($edulevel->xpath('schooltypes/schooltype') ?: [] as $schooltype) {
                foreach ($schooltype->xpath('subjects/subject') ?: [] as $subject) {
                    $created[] = self::import_subject(
                        $subject,
                        $schooltype,
                        $edulevel,
                        $niveaus,
                        $descriptors,
                        $source,
                        $visibility
                    );
                }
            }
        }
        if (!$created) {
            throw new moodle_exception('importnosubjects', 'local_competencygrid');
        }
        $transaction->allow_commit();
        return $created;
    }

    /**
     * Creates the grid for one subject.
     *
     * @param SimpleXMLElement $subject Subject element.
     * @param SimpleXMLElement $schooltype Parent school type element.
     * @param SimpleXMLElement $edulevel Parent educational level element.
     * @param array $niveaus Niveaus by id.
     * @param array $descriptors Descriptors by id.
     * @param string $source Source identifier of the file.
     * @param int $visibility Visibility of the new grid.
     * @return int New grid id.
     */
    protected static function import_subject(
        SimpleXMLElement $subject,
        SimpleXMLElement $schooltype,
        SimpleXMLElement $edulevel,
        array $niveaus,
        array $descriptors,
        string $source,
        int $visibility
    ): int {
        $subjecttitle = self::clean($subject->title) ?: get_string('notspecified', 'local_competencygrid');
        $classlevel = self::clean($subject->class);
        $name = $classlevel !== '' ? $subjecttitle . ' · ' . $classlevel : $subjecttitle;

        $gridid = grid::create((object) [
            'name' => core_text::substr($name, 0, 255),
            'subject' => $subjecttitle,
            'classlevel' => $classlevel,
            'edulevel' => self::clean($edulevel->title),
            'schooltype' => self::clean($schooltype->title),
            'visibility' => $visibility,
            'source' => $source,
            'edulevelsourceid' => self::clean((string) $edulevel['id'], 50),
            'schooltypesourceid' => self::clean((string) $schooltype['id'], 50),
            'subjectsourceid' => self::clean((string) $subject['id'], 50),
        ]);

        // Collect topics and the niveaus they use, so that levels can be created in their original order.
        $topics = [];
        $usedniveaus = [];
        $withoutniveau = false;
        foreach ($subject->xpath('topics/topic') ?: [] as $topic) {
            $refs = [];
            foreach ($topic->xpath('descriptors/descriptorid') ?: [] as $ref) {
                $refid = (string) $ref['id'];
                if (!isset($descriptors[$refid]) || $descriptors[$refid]['title'] === '') {
                    continue;
                }
                $refs[] = ['id' => $refid, 'sorting' => (int) $ref['sorting']];
                $niveauid = $descriptors[$refid]['niveauid'];
                if ($niveauid !== '' && isset($niveaus[$niveauid])) {
                    $usedniveaus[$niveauid] = $niveaus[$niveauid]['sorting'];
                } else {
                    $withoutniveau = true;
                }
            }
            $topics[] = [
                'id' => self::clean((string) $topic['id'], 50),
                'title' => self::clean($topic->title) ?: get_string('topic', 'local_competencygrid'),
                'sorting' => (int) $topic->sorting,
                'refs' => $refs,
            ];
        }

        asort($usedniveaus, SORT_NUMERIC);
        $levelids = [];
        foreach (array_keys($usedniveaus) as $index => $niveauid) {
            $levelids[(string) $niveauid] = grid::add_level(
                $gridid,
                $niveaus[$niveauid]['title'],
                '',
                $index,
                self::clean((string) $niveauid, 50)
            );
        }
        if ($withoutniveau || !$levelids) {
            $levelids[''] = grid::add_level($gridid, get_string('nolevel', 'local_competencygrid'), '', count($levelids));
        }

        usort($topics, fn($a, $b) => $a['sorting'] <=> $b['sorting']);
        foreach ($topics as $topic) {
            $topicid = grid::add_topic($gridid, $topic['title'], $topic['id'] !== '' ? $topic['id'] : null);
            foreach ($topic['refs'] as $ref) {
                $descriptor = $descriptors[$ref['id']];
                $levelid = $levelids[$descriptor['niveauid']] ?? $levelids[''];
                grid::add_competency($gridid, $topicid, $levelid, $descriptor['title'], self::clean($ref['id'], 50));
            }
        }
        return $gridid;
    }
}
