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

namespace src\transformer\events\mod_scorm;

defined('MOODLE_INTERNAL') || die();

use src\transformer\utils as utils;

// for future reference.. moodle logstore's scoreraw event is about the course, not the sco
function scoreraw_submitted(array $config, \stdClass $event) {
    try {

    
    $repo = $config['repo'];
    $user = $repo->read_record_by_id('user', $event->userid);
    $course = $repo->read_record_by_id('course', $event->courseid);
    $lang = utils\get_course_lang($course);
    // scorm is the scorm course
    $scorm = $repo->read_record_by_id('scorm', $event->objectid);

    $unserializedcmi = unserialize($event->other);
    $attempt = $unserializedcmi['attemptid'];

    $object = null;
    $sco = null;
    $scormscoestracks = null;
    
    if ($event->objecttable == 'scorm_scoes_track') {
        $scormscoestracks = $repo->read_record('scorm_scoes_track', [
            'userid' => $user->id,
            'scormid' => $event->objectid,
            'attempt' => $attempt,
            'timemodified' => $event->timecreated,
            'element' => $unserializedcmi['cmielement']
        ]);
        if (isset($scormscoestracks) && isset($scormscoestracks->scoid)) {
            $sco = $repo->read_record_by_id("scorm_scoes", $scormscoestracks->scoid);
        } else {
            return [];
        }
    }

    if (isset($sco)) {
        $object = utils\get_activity\scorm_sco($config, $attempt, $scorm, $lang, $sco);
    } else {
        $object = utils\get_activity\course_scorm($config, $event->objectid, $scorm, $lang);
    }

    $rawscore = floatval($unserializedcmi['cmivalue']);
    
    $ctxscormprofile = utils\get_activity\scorm_profile();
    $ctxmoodlecourse = utils\get_activity\course($config, $course);

    $context = utils\get_activity\netc_context($config, $event, $course, $object, $attempt);
    array_push($context['contextActivities']['grouping'], $ctxmoodlecourse);
    array_push($context['contextActivities']['category'], $ctxscormprofile);

    if (isset($sco)) {
        array_push($context['contextActivities']['grouping'], 
            utils\get_activity\course_scorm($config, $event->objectid, $scorm, $lang));
    }

    return [[
        'actor' => utils\get_user($config, $user),
        'verb' => [
            'id' => 'http://adlnet.gov/expapi/verbs/scored',
            'display' => [
                $lang => 'scored'
            ]
        ],
        'object' => $object,
        'timestamp' => utils\get_event_timestamp($event),
        'result' => [
            'score' => [
                'raw' => $rawscore,
            ],
        ],
        'context' => $context
    ]];
    } catch (\Throwable $th) {
        error_log(print_r($th->getMessage(), true));
        return [];
    }
}