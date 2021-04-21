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

function attemptid($object, $attempt) {
    $id = $object['id'];
    $attemptid = $id . ((strpos($id, '?') !== false) ? "&" : "?") . "attempt=" . $attempt;
    return $attemptid;
}

function create_attempt_id($userid, $activityid, $attempt) {
    global $DB;
    // assume if we're here it's new
    // generate uuid
    $newuuid = uuid4();
    // set it in mdl_logstore_xapi_attempts
    $attempt_record = new \stdClass();
    $attempt_record->userid = $userid;
    $attempt_record->activityid = $activityid;
    $attempt_record->attempt = $attempt;
    $attempt_record->registrationid = $newuuid;
    $attempt_record->timecreated = time();
    $DB->insert_record('logstore_xapi_attempts', $attempt_record);
    // return uuid
    return $newuuid;
}

function get_attempt_id($userid, $activityid, $attempt) {
    global $DB;
    // try to get a uuid from mdl_logstore_xapi_attempts
    // return it
    // if doesn't exist, create_attempt_id, return that

    // error_log("in get_attempt_id: $userid, $activityid, $attempt");

    if ($uuid = $DB->get_field('logstore_xapi_attempts', 'registrationid', array('userid' => $userid, 'activityid' => $activityid, 'attempt' => $attempt))) {
        // error_log("got a uuid from the db, going to use it...: $uuid");
        return $uuid;
    } else {
        $newid = create_attempt_id($userid, $activityid, $attempt);
        // error_log("new id: $newid");
        return $newid;
    }
}

function uuid4() {
    return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',

      // 32 bits for "time_low"
      mt_rand(0, 0xffff), mt_rand(0, 0xffff),

      // 16 bits for "time_mid"
      mt_rand(0, 0xffff),

      // 16 bits for "time_hi_and_version",
      // four most significant bits holds version number 4
      mt_rand(0, 0x0fff) | 0x4000,

      // 16 bits, 8 bits for "clk_seq_hi_res",
      // 8 bits for "clk_seq_low",
      // two most significant bits holds zero and one for variant DCE1.1
      mt_rand(0, 0x3fff) | 0x8000,

      // 48 bits for "node"
      mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
}