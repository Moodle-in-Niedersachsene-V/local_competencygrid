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

use DOMDocument;
use DOMElement;
use local_competencygrid\grid;
use stdClass;

/**
 * Exports a competency grid as exacomp XML (block_exacomp, Komet).
 *
 * @package    local_competencygrid
 * @copyright  2026 Moodle in Niedersachsen e. V.
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class exacomp_exporter {
    /** @var int Offset for ids of items that were not imported, avoids clashes with imported ids. */
    public const ID_OFFSET = 900000000;

    /** @var stdClass Grid record. */
    protected stdClass $grid;

    /** @var DOMDocument Document being built. */
    protected DOMDocument $doc;

    /**
     * Constructor.
     *
     * @param stdClass $grid Grid record.
     */
    public function __construct(stdClass $grid) {
        $this->grid = $grid;
    }

    /**
     * Returns the stable exacomp id of an item.
     *
     * @param stdClass $item Level, topic or competency record.
     * @return int
     */
    public static function item_id(stdClass $item): int {
        if (isset($item->sourceid) && $item->sourceid !== '' && ctype_digit((string) $item->sourceid)) {
            return (int) $item->sourceid;
        }
        return self::ID_OFFSET + (int) $item->id;
    }

    /**
     * Returns a stable id for an imported or generated wrapper element.
     *
     * @param string|null $sourceid Imported id.
     * @return int
     */
    protected function wrapper_id(?string $sourceid): int {
        if ($sourceid !== null && $sourceid !== '' && ctype_digit($sourceid)) {
            return (int) $sourceid;
        }
        return self::ID_OFFSET + (int) $this->grid->id;
    }

    /**
     * Suggested file name of the export.
     *
     * @return string
     */
    public function get_filename(): string {
        return clean_filename('competencygrid_' . str_replace(' · ', ' - ', $this->grid->name) . '.xml');
    }

    /**
     * Appends a child element with optional text.
     *
     * @param DOMElement $parent Parent element.
     * @param string $name Element name.
     * @param string|null $text Plain text content or null for an empty element.
     * @param bool $cdata Whether to wrap the text in CDATA.
     * @return DOMElement
     */
    protected function add(DOMElement $parent, string $name, ?string $text = null, bool $cdata = false): DOMElement {
        $element = $this->doc->createElement($name);
        if ($text !== null && $text !== '') {
            if ($cdata) {
                $element->appendChild($this->doc->createCDATASection(str_replace(']]>', ']] >', $text)));
            } else {
                $element->appendChild($this->doc->createTextNode($text));
            }
        }
        $parent->appendChild($element);
        return $element;
    }

    /**
     * Appends an element carrying source and id attributes.
     *
     * @param DOMElement $parent Parent element.
     * @param string $name Element name.
     * @param int $id Exacomp id.
     * @return DOMElement
     */
    protected function add_item(DOMElement $parent, string $name, int $id): DOMElement {
        $element = $this->add($parent, $name);
        $element->setAttribute('source', $this->grid->source);
        $element->setAttribute('id', (string) $id);
        return $element;
    }

    /**
     * Builds the XML document.
     *
     * @return string
     */
    public function export(): string {
        global $DB;
        $gridid = (int) $this->grid->id;
        $levels = $DB->get_records(grid::TABLE_LEVEL, ['gridid' => $gridid], 'sortorder ASC, id ASC');
        $topics = $DB->get_records(grid::TABLE_TOPIC, ['gridid' => $gridid], 'sortorder ASC, id ASC');
        $comps = $DB->get_records(grid::TABLE_COMP, ['gridid' => $gridid], 'sortorder ASC, id ASC');

        $this->doc = new DOMDocument('1.0', 'UTF-8');
        $this->doc->formatOutput = true;
        $root = $this->doc->createElement('exacomp');
        $root->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        $root->setAttributeNS(
            'http://www.w3.org/2001/XMLSchema-instance',
            'xsi:noNamespaceSchemaLocation',
            'https://github.com/gtn/edustandards/blob/master/new%20schema/exacomp.xsd'
        );
        $root->setAttribute('source', $this->grid->source);
        $root->setAttribute('sourcename', get_string('pluginname', 'local_competencygrid'));
        $this->doc->appendChild($root);

        $this->add($root, 'skills');

        $levelorder = [];
        $niveaus = $this->add($root, 'niveaus');
        $position = 0;
        foreach ($levels as $level) {
            $position++;
            $levelorder[(int) $level->id] = $position;
            $niveau = $this->add_item($niveaus, 'niveau', self::item_id($level));
            $this->add($niveau, 'title', $level->name, true);
            $this->add($niveau, 'sorting', (string) ($position * 10));
            $this->add($niveau, 'span', '0');
            $this->add($niveau, 'numb', (string) $position);
        }

        $this->add($root, 'taxonomies');
        $this->add($root, 'categories');
        $this->add($root, 'categoriesn');
        $this->add($root, 'examples');

        // Competencies are ordered per topic by level first, then by their own sort order.
        $bytopic = [];
        foreach ($comps as $comp) {
            $bytopic[(int) $comp->topicid][] = $comp;
        }
        $sorter = function ($a, $b) use ($levelorder) {
            $la = $levelorder[(int) $a->levelid] ?? PHP_INT_MAX;
            $lb = $levelorder[(int) $b->levelid] ?? PHP_INT_MAX;
            return [$la, (int) $a->sortorder, (int) $a->id] <=> [$lb, (int) $b->sortorder, (int) $b->id];
        };
        foreach ($bytopic as $topicid => $list) {
            usort($list, $sorter);
            $bytopic[$topicid] = $list;
        }

        $descriptors = $this->add($root, 'descriptors');
        $sorting = 0;
        foreach ($topics as $topic) {
            foreach ($bytopic[(int) $topic->id] ?? [] as $comp) {
                $sorting += 10;
                $descriptor = $this->add_item($descriptors, 'descriptor', self::item_id($comp));
                if (isset($levels[$comp->levelid])) {
                    $this->add_item($descriptor, 'niveauid', self::item_id($levels[$comp->levelid]));
                }
                $this->add($descriptor, 'title', $comp->title, true);
                $this->add($descriptor, 'sorting', (string) $sorting);
                $this->add($descriptor, 'profoundness', '0');
                $this->add($descriptor, 'epop', '0');
                $this->add($descriptor, 'creator');
                $this->add($descriptor, 'editor');
            }
        }

        $this->add($root, 'crosssubjects');

        $edulevels = $this->add($root, 'edulevels');
        $edulevel = $this->add_item($edulevels, 'edulevel', $this->wrapper_id($this->grid->edulevelsourceid));
        $this->add($edulevel, 'title', $this->grid->edulevel ?: get_string('notspecified', 'local_competencygrid'), true);
        $this->add($edulevel, 'sorting', '10');
        $schooltypes = $this->add($edulevel, 'schooltypes');
        $schooltype = $this->add_item($schooltypes, 'schooltype', $this->wrapper_id($this->grid->schooltypesourceid));
        $this->add($schooltype, 'title', $this->grid->schooltype ?: get_string('notspecified', 'local_competencygrid'), true);
        $this->add($schooltype, 'sorting', '10');
        $this->add($schooltype, 'isoez', '0');
        $this->add($schooltype, 'epop', '0');
        $subjects = $this->add($schooltype, 'subjects');
        $subject = $this->add_item($subjects, 'subject', $this->wrapper_id($this->grid->subjectsourceid));
        $this->add($subject, 'title', $this->grid->subject ?: $this->grid->name, true);
        $this->add($subject, 'titleshort');
        $this->add($subject, 'sorting', '10');
        $this->add($subject, 'numb', '0');
        $this->add($subject, 'class', (string) $this->grid->classlevel, true);
        $this->add($subject, 'author');
        $this->add($subject, 'editor');
        $this->add($subject, 'infolink');
        $this->add($subject, 'epop', '0');
        $this->add($subject, 'isglobal', '0');
        $this->add($subject, 'version');
        $this->add($subject, 'is_editable', '0');

        $topicsnode = $this->add($subject, 'topics');
        $position = 0;
        foreach ($topics as $topic) {
            $position++;
            $topicnode = $this->add_item($topicsnode, 'topic', self::item_id($topic));
            $this->add($topicnode, 'title', $topic->name, true);
            $this->add($topicnode, 'titleshort');
            $this->add($topicnode, 'sorting', (string) ($position * 10));
            $this->add($topicnode, 'description');
            $this->add($topicnode, 'numb');
            $this->add($topicnode, 'epop', '0');
            $this->add($topicnode, 'span', '0');
            $this->add($topicnode, 'creator');
            $this->add($topicnode, 'editor');
            $refs = $this->add($topicnode, 'descriptors');
            $refsorting = 0;
            foreach ($bytopic[(int) $topic->id] ?? [] as $comp) {
                $refsorting++;
                $ref = $this->add_item($refs, 'descriptorid', self::item_id($comp));
                $ref->setAttribute('sorting', (string) $refsorting);
            }
        }

        return $this->doc->saveXML();
    }
}
