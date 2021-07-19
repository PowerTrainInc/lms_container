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

function ddimageortext(array $config, \stdClass $event, \stdClass $questionattempt, \stdClass $question) {
    global $DB;

    $repo = $config['repo'];
    $user = $repo->read_record_by_id('user', $event->relateduserid);
    $course = $repo->read_record_by_id('course', $event->courseid);
    $attempt = $repo->read_record('quiz_attempts', ['uniqueid' => $questionattempt->questionusageid]);
    $quiz = $repo->read_record_by_id('quiz', $attempt->quiz);
    $coursemodule = $repo->read_record_by_id('course_modules', $event->contextinstanceid);
    $lang = utils\get_course_lang($course);   

    $responsePattern = '';
    $correctResponsesPattern = '';
    $parsedRightAnswer = explode('} ', $questionattempt->rightanswer);
    $responsesummary = explode('} ', $questionattempt->responsesummary);
    $target = array();
    $source = array();

    for ($i = 0; $i < count($parsedRightAnswer); $i++) {
        $id = $i + 1;
        $sourceID = $id < 10 ? 'source0'.$id : 'source'.$id;
        $targetID = $id < 10 ? 'target0'.$id : 'target'.$id;
        $str = explode(' -> '.'{'.$id, $parsedRightAnswer[$i]);
        $src = str_replace(['. ','}'], '', $str[1]);
        $source[] = [
            'id' => $sourceID,
            'description' => [
                $lang => $src
            ]
        ];
        $target[] = [
            'id' => $targetID,
            'description' => [
                $lang => 'Item'.$id
            ]
        ];

        $responseStr = explode(' -> ', $responsesummary[$i]);
        $responseSrc = explode('. ', $responseStr[0]);
        $itemID = preg_replace('/\D/', '', $responseStr[1]);
        $responseTarget = $itemID < 10 ? 'target0'.$itemID : 'target'.$itemID;
        $responsePattern = $responsePattern.$sourceID.'[.]'.$responseTarget.'[,]';
        $correctResponsesPattern = $correctResponsesPattern.$sourceID.'[.]'.$targetID.'[,]';
    }

    $correctResponsesPattern = utils\str_replace_last('[,]', '', $correctResponsesPattern);
    $formattedResult = utils\str_replace_last('[,]', '', $responsePattern);

    $stmnt = [[
        'actor' => utils\get_user($config, $user),
        'verb' => [
            'id' => 'http://adlnet.gov/expapi/verbs/responded',
            'display' => [
                $lang => 'responded'
            ],
        ],
        'object' => [
            'id' => 'https://navy.mil/netc/xapi/activities/cmi.interactions/' . $question->id,
            'definition' => [
                'type' => 'http://adlnet.gov/expapi/activities/cmi.interaction',
                'name' => [
                    $lang => utils\get_string_html_removed($question->name)
                ],
                'description' => [
                    $lang => utils\get_string_html_removed($question->questiontext)
                ],
                'interactionType' => 'matching',
                'correctResponsesPattern' => [$correctResponsesPattern],
                'source' => array_values($source),
                'target' => array_values($target),
            ]
        ],
        'timestamp' => utils\get_event_timestamp($event),
        'result' => [
            'response' => isset($questionattempt->responsesummary) ? $formattedResult : "",
            'success' => $questionattempt->rightanswer == $questionattempt->responsesummary,
            'completion' => ($questionattempt->responsesummary !== null || $questionattempt->responsesummary !== '') ? true : false            
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

    // if (isset($questionattempt->responsesummary) && $questionattempt->responsesummary != "") {
    //     $stmnt[0]['result']['success'] = $questionattempt->rightanswer === $questionattempt->responsesummary ? true : false;
    // }

    return $stmnt;
}
