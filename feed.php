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

/**
 * Feed of the grids shared with the school instances (exacomp XML).
 *
 * @package    local_competencygrid
 * @copyright  2026 Moodle in Niedersachsen e. V.
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Require_login is not needed here: block_exacomp in the school instances fetches the feed
// without a session. The feed only contains public grids that were explicitly shared.
// phpcs:disable moodle.Files.RequireLogin.Missing
define('NO_MOODLE_COOKIES', true);

// phpcs:ignore moodle.Commenting.InlineComment
require(__DIR__ . '/../../config.php'); // nosemgrep: moodle-einstiegsdatei-ohne-login
require_once($CFG->libdir . '/filelib.php');

use local_competencygrid\local\feed;

$download = optional_param('download', 0, PARAM_BOOL);

if (!feed::enabled()) {
    send_header_404();
    die();
}

send_file(feed::get_xml(), 'competencygrids_feed.xml', 300, 0, true, (bool) $download, 'application/xml');
