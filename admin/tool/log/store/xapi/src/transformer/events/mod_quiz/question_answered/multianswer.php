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

function multianswer(array $config, \stdClass $event, \stdClass $questionattempt, \stdClass $question) {
    global $DB;

    $repo = $config['repo'];
    $user = $repo->read_record_by_id('user', $event->relateduserid);
    $course = $repo->read_record_by_id('course', $event->courseid);
    $attempt = $repo->read_record('quiz_attempts', ['uniqueid' => $questionattempt->questionusageid]);
    $quiz = $repo->read_record_by_id('quiz', $attempt->quiz);
    $coursemodule = $repo->read_record_by_id('course_modules', $event->contextinstanceid);
    $lang = utils\get_course_lang($course);
    
    $maquestion = array();
    $oldwrappedids = $DB->get_field('question_multianswer', 'sequence', array('question' => $question->id));
    $multianswersids = explode(",", $oldwrappedids);

    $maquestion = array_map(function($q) use($repo) { 
        return $repo->read_record_by_id('question', $q);
    }, $multianswersids);


    $responsesummary = explode('; ', $questionattempt->responsesummary);

    // get all answers
    $answers = array_map(function ($item) use($repo) { 
        return $repo->read_records('question_answers', ['question' => $item]);
    }, $multianswersids);

    // get all correct answers
    $correctAnswers = array_map(function($item){
        foreach (array_values($item) as $choice) {
            if (intval($choice->fraction) === 1) {
                return $choice;
            }
        }
    }, $answers);

    // build correct response pattern
    $stepCounter = 1;
    $correctResponsesPattern = array_reduce(
        $correctAnswers, 
        function ($reduction, $selection) use(&$stepCounter) {
            $count = $stepCounter < 10 ? 'step0'.$stepCounter++ : 'step'.$stepCounter++;
            $reduction = $reduction.$count.'[.]'.$selection->answer.'[,]';
            return $reduction;
        }, '');

    $correctResponsesPattern = utils\str_replace_last('[,]', '', $correctResponsesPattern);  

    $userAnswers = array_map(function($response){
        $ua = explode(': ', $response);
        return $ua[1];
    }, $responsesummary);


    $responsePattern = ''; 
    for ($i = 1; $i <= count($correctAnswers); $i++) {
        $count = $i < 10 ? 'step0'.$i : 'step'.$i ;
        $responsePattern = $responsePattern.$count.'[.]'.$userAnswers[$i].'[,]';
    }
    $responsePattern = utils\str_replace_last('[,]', '', $responsePattern);

    $stepCounter = 1;
    $steps = array_map(function ($item) use($lang, &$stepCounter) {
        return [
            'id' => $stepCounter < 10 ? 'step0'.$stepCounter++ : 'step'.$stepCounter++,
            'description' => [
                $lang => utils\get_question_type($item->qtype)
            ]
        ];
    }, $maquestion);


    return [[
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
                    $lang => utils\get_string_html_removed($questionattempt->questionsummary)
                ],
                'interactionType' => 'performance',
                'correctResponsesPattern' => [$correctResponsesPattern],
                'steps' => $steps
            ]
        ],
        'timestamp' => utils\get_event_timestamp($event),
        'result' => [
            'response' => $responsePattern,
            'completion' => ($questionattempt->responsesummary !== null || $questionattempt->responsesummary !== '') ? true : false,
            // 'success' => (strcasecmp($questionattempt->rightanswer, $questionattempt->responsesummary) == 0)
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
}