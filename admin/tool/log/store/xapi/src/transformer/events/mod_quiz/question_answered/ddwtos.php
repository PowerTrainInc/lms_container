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

function ddwtos(array $config, \stdClass $event, \stdClass $questionattempt, \stdClass $question) {
    global $DB;

    $repo = $config['repo'];
    $user = $repo->read_record_by_id('user', $event->relateduserid);
    $course = $repo->read_record_by_id('course', $event->courseid);
    $attempt = $repo->read_record('quiz_attempts', ['uniqueid' => $questionattempt->questionusageid]);
    $quiz = $repo->read_record_by_id('quiz', $attempt->quiz);
    // $coursemodule = $repo->read_record_by_id('course_modules', $event->contextinstanceid);
    $lang = utils\get_course_lang($course);

    $formattedRightAnswer = explode('} {', rtrim(ltrim(utils\get_string_html_removed($questionattempt->rightanswer), '{'), '}'));
    $interactionType = count($formattedRightAnswer) > 1 ? 'matching' : 'choice';

    $responsesummary = explode('} {', rtrim(ltrim(utils\get_string_html_removed($questionattempt->responsesummary), '{'), '}'));

    $delimiter = strpos($questionattempt->questionsummary, "\n\n") ? "\n\n" : "\n; ";

    $questionsummary = explode($delimiter, $questionattempt->questionsummary);

    $correctResponsesPattern = '';

    // matching type ddwtos with matching pairs
    if ($interactionType === 'matching') {
        $stepCounter = 1;
        $source = array_map(function ($item) use ($lang, &$stepCounter) {
            return [
                'id' => $stepCounter < 10 ? 'source0' . $stepCounter++ : 'source' . $stepCounter++,
                'description' => [
                    $lang => $item
                ]
            ];
        }, $formattedRightAnswer);

        $stepCounter = 1;
        $sourceAnswers = array_map(function ($item) use (&$stepCounter) {
            return array(
                'id' => $stepCounter < 10 ? 'source0' . $stepCounter++ : 'source' . $stepCounter++,
                'description' => $item
            );
        }, $formattedRightAnswer);

        $target = array();
        for ($i = 1; $i < count($questionsummary) - 1; $i++) {
            $target[] = [
                'id' => $i < 10 ? 'target0' . $i : 'target' . $i,
                'description' => [
                    $lang => $questionsummary[$i]
                ]
            ];
            $sCount = $i < 10 ? 'source0' . $i : 'source' . $i;
            $tCount = $i < 10 ? 'target0' . $i : 'target' . $i;
            $correctResponsesPattern = $correctResponsesPattern . $sCount . '[.]' . $tCount . '[,]';
        }

        $stepCounter = 1;
        $responsePattern = array_reduce(
            $responsesummary,
            function ($reduction, $selection) use ($sourceAnswers, &$stepCounter) {
                foreach ($sourceAnswers as $s) {
                    $v = utils\get_value($s, 'description');

                    if ($v === $selection) {
                        $selectionkey = utils\get_value($s, 'id');
                    }
                }
                $tCount = $stepCounter < 10 ? 'target0' . $stepCounter : 'target' . $stepCounter;
                $reduction = $reduction . $selectionkey . '[.]' . $tCount . '[,]';
                $stepCounter++;
                return $reduction;
            },
            ''
        );
    } else { // choice type ddwtos with one correct answer
        $userAnswer = rtrim(ltrim($responsesummary[0], '{'), '}');
        $parseQS = explode(' -> ', $questionsummary[1]);
        $removeBrackets = explode('} {', rtrim(ltrim($parseQS[1], '{'), '}'));
        $update = explode(' / ', stripslashes($removeBrackets[0]));

        for ($i = 1; $i <= count($update); $i++) {
            $choices[] = [
                'id' => $i < 10 ? 'choice0' . $i : 'choice' . $i,
                'description' => [
                    $lang => $update[$i - 1]
                ]
            ];

            if ($update[$i - 1] === $formattedRightAnswer[0]) {
                $correctResponsesPattern = $i < 10 ? 'choice0' . $i : 'choice' . $i;
            }
            if ($userAnswer === $update[$i - 1]) {
                $responsePattern = $i < 10 ? 'choice0' . $i : 'choice' . $i;
            }
        }
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
                'interactionType' => $interactionType,
                'correctResponsesPattern' => [$correctResponsesPattern]
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

    if ($interactionType === 'matching') {
        $stmnt[0]['object']['definition']['source'] = array_values($source);
        $stmnt[0]['object']['definition']['target'] = array_values($target);
    } else {
        $stmnt[0]['object']['definition']['choices'] = array_values($choices);
    }

    return $stmnt;
}
