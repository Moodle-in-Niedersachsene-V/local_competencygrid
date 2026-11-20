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
 * Writes competency grids as exacomp XML (block_exacomp, Komet).
 *
 * Two modes:
 * - Single export of one grid under the grid's own source; imported ids are kept, so the file
 *   can be imported again into instances that already know the original grid.
 * - Feed for the school instances: several grids under one fixed feed source; all ids are
 *   derived from the database ids, so they are unique within the feed and never change.
 *
 * @package    local_competencygrid
 * @copyright  2026 Moodle in Niedersachsen e. V.
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class exacomp_exporter {
    /** @var int Offset for ids derived from database ids, avoids clashes with imported ids. */
    public const ID_OFFSET = 900000000;

    /** @var stdClass[] Grid records to export. */
    protected array $grids;

    /** @var string Source identifier written to the file. */
    protected string $source;

    /** @var string Source name shown in block_exacomp. */
    protected string $sourcename;

    /** @var bool Whether the file is the feed for the school instances. */
    protected bool $feedmode = false;

    /** @var DOMDocument Document being built. */
    protected DOMDocument $doc;

    /**
     * Creates an exporter for a single grid.
     *
     * @param stdClass $grid Grid record.
     */
    public function __construct(stdClass $grid) {
        $this->grids = [$grid];
        $this->source = (string) $grid->source;
        $this->sourcename = get_string('pluginname', 'local_competencygrid');
    }

    /**
     * Creates an exporter for the feed of the school instances.
     *
     * @param stdClass[] $grids Grid records, each with feedversion.
     * @param string $source Feed source identifier.
     * @param string $sourcename Source name shown in block_exacomp.
     * @return self
     */
    public static function for_feed(array $grids, string $source, string $sourcename): self {
        $first = reset($grids) ?: (object) ['source' => $source];
        $exporter = new self($first);
        $exporter->grids = array_values($grids);
        $exporter->source = $source;
        $exporter->sourcename = $sourcename;
        $exporter->feedmode = true;
        return $exporter;
    }

    /**
     * Returns the stable exacomp id of an item in a single export: the imported id or the offset id.
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
     * Returns the id of an item in the current mode.
     *
     * @param stdClass $item Level, topic or competency record.
     * @return int
     */
    protected function id_of(stdClass $item): int {
        return $this->feedmode ? self::ID_OFFSET + (int) $item->id : self::item_id($item);
    }

    /**
     * Returns the id of a wrapper element (educational level, school type, subject) of a grid.
     *
     * @param stdClass $grid Grid record.
     * @param string|null $sourceid Imported id.
     * @return int
     */
    protected function wrapper_id(stdClass $grid, ?string $sourceid): int {
        if (!$this->feedmode && $sourceid !== null && $sourceid !== '' && ctype_digit($sourceid)) {
            return (int) $sourceid;
        }
        return self::ID_OFFSET + (int) $grid->id;
    }

    /**
     * Suggested file name of the export.
     *
     * @return string
     */
    public function get_filename(): string {
        if ($this->feedmode) {
            return 'competencygrids_feed.xml';
        }
        return clean_filename('competencygrid_' . str_replace(' · ', ' - ', $this->grids[0]->name) . '.xml');
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
        $element->setAttribute('source', $this->source);
        $element->setAttribute('id', (string) $id);
        return $element;
    }

    /**
     * Loads levels, topics and competencies of a grid.
     *
     * Competencies are grouped per topic and ordered by level first, then by their own sort order.
     *
     * @param stdClass $grid Grid record.
     * @return array ['levels' => stdClass[], 'topics' => stdClass[], 'bytopic' => stdClass[][]]
     */
    protected function load(stdClass $grid): array {
        global $DB;
        $gridid = (int) $grid->id;
        $levels = $DB->get_records(grid::TABLE_LEVEL, ['gridid' => $gridid], 'sortorder ASC, id ASC');
        $topics = $DB->get_records(grid::TABLE_TOPIC, ['gridid' => $gridid], 'sortorder ASC, id ASC');
        $comps = $DB->get_records(grid::TABLE_COMP, ['gridid' => $gridid], 'sortorder ASC, id ASC');

        $levelorder = [];
        $position = 0;
        foreach ($levels as $level) {
            $levelorder[(int) $level->id] = ++$position;
        }
        $sorter = function ($a, $b) use ($levelorder) {
            $la = $levelorder[(int) $a->levelid] ?? PHP_INT_MAX;
            $lb = $levelorder[(int) $b->levelid] ?? PHP_INT_MAX;
            return [$la, (int) $a->sortorder, (int) $a->id] <=> [$lb, (int) $b->sortorder, (int) $b->id];
        };
        $bytopic = [];
        foreach ($comps as $comp) {
            $bytopic[(int) $comp->topicid][] = $comp;
        }
        foreach ($bytopic as $topicid => $list) {
            usort($list, $sorter);
            $bytopic[$topicid] = $list;
        }
        return ['levels' => $levels, 'topics' => $topics, 'bytopic' => $bytopic];
    }

    /**
     * Builds the XML document.
     *
     * @return string
     */
    public function export(): string {
        $data = [];
        foreach ($this->grids as $grid) {
            $data[(int) $grid->id] = $this->load($grid);
        }

        $this->doc = new DOMDocument('1.0', 'UTF-8');
        $this->doc->formatOutput = true;
        $root = $this->doc->createElement('exacomp');
        $root->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        $root->setAttributeNS(
            'http://www.w3.org/2001/XMLSchema-instance',
            'xsi:noNamespaceSchemaLocation',
            'https://github.com/gtn/edustandards/blob/master/new%20schema/exacomp.xsd'
        );
        $root->setAttribute('source', $this->source);
        $root->setAttribute('sourcename', $this->sourcename);
        $this->doc->appendChild($root);

        $this->add($root, 'skills');

        $niveaus = $this->add($root, 'niveaus');
        foreach ($this->grids as $grid) {
            $position = 0;
            foreach ($data[(int) $grid->id]['levels'] as $level) {
                $position++;
                $niveau = $this->add_item($niveaus, 'niveau', $this->id_of($level));
                $this->add($niveau, 'title', $level->name, true);
                $this->add($niveau, 'sorting', (string) ($position * 10));
                $this->add($niveau, 'span', '0');
                $this->add($niveau, 'numb', (string) $position);
            }
        }

        $this->add($root, 'taxonomies');
        $this->add($root, 'categories');
        $this->add($root, 'categoriesn');
        $this->add($root, 'examples');

        $descriptors = $this->add($root, 'descriptors');
        $sorting = 0;
        foreach ($this->grids as $grid) {
            $griddata = $data[(int) $grid->id];
            foreach ($griddata['topics'] as $topic) {
                foreach ($griddata['bytopic'][(int) $topic->id] ?? [] as $comp) {
                    $sorting += 10;
                    $descriptor = $this->add_item($descriptors, 'descriptor', $this->id_of($comp));
                    if (isset($griddata['levels'][$comp->levelid])) {
                        $this->add_item($descriptor, 'niveauid', $this->id_of($griddata['levels'][$comp->levelid]));
                    }
                    $this->add($descriptor, 'title', $comp->title, true);
                    $this->add($descriptor, 'sorting', (string) $sorting);
                    $this->add($descriptor, 'profoundness', '0');
                    $this->add($descriptor, 'epop', '0');
                    $this->add($descriptor, 'creator');
                    $this->add($descriptor, 'editor');
                }
            }
        }

        $this->add($root, 'crosssubjects');

        $edulevels = $this->add($root, 'edulevels');
        $gridposition = 0;
        foreach ($this->grids as $grid) {
            $gridposition++;
            $this->add_grid_wrappers($edulevels, $grid, $data[(int) $grid->id], $gridposition * 10);
        }

        return $this->doc->saveXML();
    }

    /**
     * Adds educational level, school type, subject and topics of one grid.
     *
     * In feed mode the educational level carries the grid name, so every grid appears as its
     * own entry in block_exacomp, and the subject carries the feed version.
     *
     * @param DOMElement $edulevels The edulevels element.
     * @param stdClass $grid Grid record.
     * @param array $griddata Data loaded by load().
     * @param int $sorting Sort value of the wrapper elements.
     * @return void
     */
    protected function add_grid_wrappers(DOMElement $edulevels, stdClass $grid, array $griddata, int $sorting): void {
        $notspecified = get_string('notspecified', 'local_competencygrid');
        if ($this->feedmode) {
            $eduleveltitle = $grid->name;
            $parts = array_filter([(string) $grid->edulevel, (string) $grid->schooltype]);
            $schooltypetitle = $parts ? implode(' · ', $parts) : $notspecified;
            $version = (string) (int) ($grid->feedversion ?? 0);
        } else {
            $eduleveltitle = $grid->edulevel ?: $notspecified;
            $schooltypetitle = $grid->schooltype ?: $notspecified;
            $version = '';
        }

        $edulevel = $this->add_item($edulevels, 'edulevel', $this->wrapper_id($grid, $grid->edulevelsourceid));
        $this->add($edulevel, 'title', $eduleveltitle, true);
        $this->add($edulevel, 'sorting', (string) $sorting);
        $schooltypes = $this->add($edulevel, 'schooltypes');
        $schooltype = $this->add_item($schooltypes, 'schooltype', $this->wrapper_id($grid, $grid->schooltypesourceid));
        $this->add($schooltype, 'title', $schooltypetitle, true);
        $this->add($schooltype, 'sorting', (string) $sorting);
        $this->add($schooltype, 'isoez', '0');
        $this->add($schooltype, 'epop', '0');
        $subjects = $this->add($schooltype, 'subjects');
        $subject = $this->add_item($subjects, 'subject', $this->wrapper_id($grid, $grid->subjectsourceid));
        $this->add($subject, 'title', $grid->subject ?: $grid->name, true);
        $this->add($subject, 'titleshort');
        $this->add($subject, 'sorting', (string) $sorting);
        $this->add($subject, 'numb', '0');
        $this->add($subject, 'class', (string) $grid->classlevel, true);
        $this->add($subject, 'author');
        $this->add($subject, 'editor');
        $this->add($subject, 'infolink');
        $this->add($subject, 'epop', '0');
        $this->add($subject, 'isglobal', '0');
        $this->add($subject, 'version', $version);
        $this->add($subject, 'is_editable', '0');

        $topicsnode = $this->add($subject, 'topics');
        $position = 0;
        foreach ($griddata['topics'] as $topic) {
            $position++;
            $topicnode = $this->add_item($topicsnode, 'topic', $this->id_of($topic));
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
            foreach ($griddata['bytopic'][(int) $topic->id] ?? [] as $comp) {
                $refsorting++;
                $ref = $this->add_item($refs, 'descriptorid', $this->id_of($comp));
                $ref->setAttribute('sorting', (string) $refsorting);
            }
        }
    }
}
