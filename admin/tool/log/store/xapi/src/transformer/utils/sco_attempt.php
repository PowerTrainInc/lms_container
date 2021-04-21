<?php
// This file is part of Moodle - http://moodle.org/
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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace src\transformer\utils;
defined('MOODLE_INTERNAL') || die();

/**
 * Get SCORM attempt count
 *
 * @param object $user Current context user
 * @param object $scorm a moodle scorm object - mdl_scorm
 * @return int - latest attempt
 */
function sco_attempt($userid, $scorm) {
    global $DB;

    // Find the last attempt number for the given user id and scorm id.
    $sql = "SELECT MAX(attempt)
              FROM {scorm_scoes_track}
             WHERE userid = ? AND scormid = ?";
    $lastattempt = $DB->get_field_sql($sql, array($userid, $scorm->id));
    error_log("sco_attempt: $lastattempt");
    return $lastattempt;
}