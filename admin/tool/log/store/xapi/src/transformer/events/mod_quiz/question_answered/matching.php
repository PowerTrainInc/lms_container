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

function matching(array $config, \stdClass $event, \stdClass $questionattempt, \stdClass $question) {
    global $DB;

    $repo = $config['repo'];
    $user = $repo->read_record_by_id('user', $event->relateduserid);
    $course = $repo->read_record_by_id('course', $event->courseid);
    $attempt = $repo->read_record('quiz_attempts', ['uniqueid' => $questionattempt->questionusageid]);
    $quiz = $repo->read_record_by_id('quiz', $attempt->quiz);
    $coursemodule = $repo->read_record_by_id('course_modules', $event->contextinstanceid);
    $lang = utils\get_course_lang($course);

    $subquestions = $DB->get_records('qtype_match_subquestions', array('questionid' => $question->id), 'id ASC');

    $correctResponsesPattern = array_reduce(
        $subquestions,
        function ($reduction, $q){
            $selectionkey = (intval($q->id) < 10) ? 'source0'.$q->id : 'source'.$q->id;
            $selectionvalue = (intval($q->id) < 10) ? 'target0'.$q->id : 'target'.$q->id;
            $reduction = $reduction.$selectionkey.'[.]'.$selectionvalue.'[,]';
            return $reduction;
        },
        ''
    );

    $sourceAnswers = array_map(function ($item){
        return array(
            'id' => (intval($item->id) < 10) ? 'source0'.$item->id : 'source'.$item->id,
            'description' => utils\get_string_html_removed($item->questiontext)
        );
    }, $subquestions);

    $targetAnswers = array_map(function ($item) {
        return array(
            'id' => (intval($item->id) < 10) ? 'target0'.$item->id : 'target'.$item->id,
            'description' => $item->answertext
            );
    }, $subquestions);

    $source = array_map(function ($item) use ($lang) {
        return array(
            'id' => (intval($item->id) < 10) ? 'source0'.$item->id : 'source'.$item->id,
            'description' => [
                $lang => utils\get_string_html_removed($item->questiontext)
            ]
            );
    }, $subquestions);

    $target = array_map(function ($item) use ($lang) {
        return array(
            'id' => (intval($item->id) < 10) ? 'target0'.$item->id : 'target'.$item->id,
            'description' => [
                $lang => $item->answertext
            ]
            );
    }, $subquestions);

 
    $responsePattern = array_reduce(
        explode('; ', $questionattempt->responsesummary),
        function ($reduction, $selection) use($sourceAnswers, $targetAnswers) {
            $split = explode("\n -> ", $selection);

            foreach($sourceAnswers as $s) {
                $v = utils\get_value($s, 'description');
                $ss = utils\get_string_html_removed(trim($split[0]));

                if(strtoupper($v) === $ss) {
                    $selectionkey = utils\get_value($s, 'id');
                }
            }

            foreach($targetAnswers as $s) {
                $v = utils\get_value($s, 'description');
                $ss = $split[1];

                if($v === $ss) {
                    $selectionvalue = utils\get_value($s, 'id');
                }
            }

            if (count($split) > 1) {
                $reduction = $reduction.$selectionkey.'[.]'.$selectionvalue.'[,]';
            }
            return $reduction;
        },
        ''
    );

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
                'correctResponsesPattern' => [$correctResponsesPattern],
                'source' => array_values($source),
                'target' => array_values($target),
            ]
        ],
        'timestamp' => utils\get_event_timestamp($event),
        'result' => [
            'response' => isset($questionattempt->responsesummary) ? $formattedResult : "",
            'success' => (strcasecmp($questionattempt->rightanswer, $questionattempt->responsesummary) == 0),
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