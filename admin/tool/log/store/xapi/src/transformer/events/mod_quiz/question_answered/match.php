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

namespace src\transformer\events\mod_quiz\question_answered;

defined('MOODLE_INTERNAL') || die();

use src\transformer\utils as utils;

function match(array $config, \stdClass $event, \stdClass $questionattempt, \stdClass $question) {
    $repo = $config['repo'];
    $user = $repo->read_record_by_id('user', $event->relateduserid);
    $course = $repo->read_record_by_id('course', $event->courseid);
    $attempt = $repo->read_record('quiz_attempts', ['uniqueid' => $questionattempt->questionusageid]);
    $quiz = $repo->read_record_by_id('quiz', $attempt->quiz);
    $coursemodule = $repo->read_record_by_id('course_modules', $event->contextinstanceid);
    $lang = utils\get_course_lang($course);
    $selections = array_reduce(
        explode('; ', $questionattempt->responsesummary),
        function ($reduction, $selection) {
            $split = explode("\n -> ", $selection);
            if (count($split) > 1) {
                $selectionkey = trim($split[0]);
                $selectionvalue = $split[1];
                $reduction = $reduction.$selectionkey.'[.]'.$selectionvalue.'[,]';
            }
            return $reduction;
        },
        ''
    );

    $parsedResponse = explode('; ', $questionattempt->rightanswer);
    $indexes = array_keys($parsedResponse);

    $responsePattern = array_reduce(
        $parsedResponse,
        function ($reduction, $selection) {
            $split = explode("\n -> ", $selection);
            if (count($split) > 1) {
                $selectionkey = trim($split[0]);
                $selectionvalue = $split[1];
                $reduction = $reduction.$selectionkey.'[.]'.$selectionvalue.'[,]';
            }
            return $reduction;
        },
        ''
    );

    $source = array_map(function ($item, $index) use ($lang) {
        $split = explode("\n -> ", $item);
        return array(
            'id' => strval($index+1),
            'description' => [
                $lang => trim($split[0])
            ]
            );
    }, $parsedResponse, $indexes);

    $target = array_map(function ($item, $index) use ($lang) {
        $split = explode("\n -> ", $item);
        return array(
            'id' => strval($index+1),
            'description' => [
                $lang => trim($split[1])
            ]
            );
    }, $parsedResponse, $indexes);
  

    // $json_pretty_string = json_encode($responsePattern, JSON_PRETTY_PRINT);
    // error_log($json_pretty_string .PHP_EOL, 3, 'error.log');

    $cResponsePattern = substr_replace($responsePattern, '', $responsePattern.length - 3);
    $formattedResult = substr_replace($selections, '', $selections.length - 3);

    $stmnt = [[
        'actor' => utils\get_user($config, $user),
        'verb' => [
            'id' => 'http://adlnet.gov/expapi/verbs/responded',
            'display' => [
                $lang => 'responded'
            ],
        ],
        'object' => [
            'id' => 'https://navy.mil/netc/xapi/activities/cmi.interactions/'.$question->id,
            'definition' => [
                'type' => 'http://adlnet.gov/expapi/activities/cmi.interaction',
                'name' => [
                    $lang => utils\get_string_html_removed($question->name)
                ],
                'description' => [
                    $lang => utils\get_string_html_removed($question->questiontext)
                ],
                'interactionType' => 'matching',
                'correctResponsesPattern' => [$cResponsePattern],
                'source' => $source,
                'target' => $target,
            ]
        ],
        'timestamp' => utils\get_event_timestamp($event),
        'result' => [
            'response' => isset($questionattempt->responsesummary) ? $formattedResult : "",
            'completion' => ($questionattempt->responsesummary !== null || $questionattempt->responsesummary !== '') ? true : false,
        ],
        'context' => [
            'platform' => $config['source_name'],
            'language' => $lang,
            'extensions' => utils\extensions\base($config, $event, $course),
            'contextActivities' => [
                'parent' => [
                    utils\get_activity\quiz_profile($config, $course, $event->contextinstanceid, $quiz->id),
                ],
                'grouping' => [
                    utils\get_activity\course_quiz($config, $course, $event->contextinstanceid),
                ],
                'category' => [
                    utils\get_activity\scorm_profile(),
                    utils\get_activity\netc_profile(),
                    utils\get_activity\netc_elearning_profile(),
                ]
            ],
        ]
    ]];

    if (isset($questionattempt->responsesummary) && $questionattempt->responsesummary != "") {
        $stmnt[0]['result']['success'] = $questionattempt->rightanswer === $questionattempt->responsesummary ? true : false;
    }

    return $stmnt;
}