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
use moodle_url;

/**
 * Feed of the grids shared with the school instances, read by block_exacomp via its server URL.
 *
 * The feed contains every public grid that is shared with the schools, in its current state,
 * under one fixed source identifier. It is cached and rebuilt after a change.
 *
 * @package    local_competencygrid
 * @copyright  2026 Moodle in Niedersachsen e. V.
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class feed {
    /**
     * Whether the feed is switched on.
     *
     * @return bool
     */
    public static function enabled(): bool {
        return (bool) get_config('local_competencygrid', 'feedenabled');
    }

    /**
     * Returns the fixed source identifier of the feed, creating it on first use.
     *
     * The identifier must never change once school instances use the feed, otherwise
     * block_exacomp treats the grids as new and stops updating the old ones.
     *
     * @return string
     */
    public static function source(): string {
        $source = (string) get_config('local_competencygrid', 'feedsource');
        if ($source === '') {
            $source = 'MOONI-CG-FEED-' . strtoupper(random_string(20));
            set_config('feedsource', $source, 'local_competencygrid');
        }
        return $source;
    }

    /**
     * Returns the source name shown in block_exacomp.
     *
     * @return string
     */
    public static function sourcename(): string {
        global $SITE;
        $name = trim((string) get_config('local_competencygrid', 'feedsourcename'));
        return $name !== '' ? $name : format_string($SITE->fullname);
    }

    /**
     * Returns the URL of the feed.
     *
     * @param bool $download Whether to deliver the feed as a file download.
     * @return moodle_url
     */
    public static function url(bool $download = false): moodle_url {
        return new moodle_url('/local/competencygrid/feed.php', $download ? ['download' => 1] : []);
    }

    /**
     * Returns the grids contained in the feed.
     *
     * @return \stdClass[]
     */
    public static function get_grids(): array {
        global $DB;
        return $DB->get_records(
            grid::TABLE,
            ['schoolsync' => 1, 'visibility' => grid::VISIBILITY_SITE],
            'name ASC, id ASC'
        );
    }

    /**
     * Builds the feed without using the cache.
     *
     * @return string
     */
    public static function build(): string {
        return exacomp_exporter::for_feed(self::get_grids(), self::source(), self::sourcename())->export();
    }

    /**
     * Returns the feed, from the cache if possible.
     *
     * @return string
     */
    public static function get_xml(): string {
        $cache = \cache::make('local_competencygrid', 'feed');
        $key = 'xml_' . substr(hash('sha256', self::source() . '|' . self::sourcename()), 0, 16);
        $xml = $cache->get($key);
        if ($xml === false) {
            $xml = self::build();
            $cache->set($key, $xml);
        }
        return $xml;
    }

    /**
     * Drops the cached feed after a change.
     *
     * @return void
     */
    public static function purge(): void {
        \cache::make('local_competencygrid', 'feed')->purge();
    }
}
