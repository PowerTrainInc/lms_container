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

namespace src\transformer\events\mod_feedback\item_answered;

defined('MOODLE_INTERNAL') || die();

use src\transformer\utils as utils;

function multichoicerated(array $config, \stdClass $event, \stdClass $feedbackvalue, \stdClass $feedbackitem) {
    $repo = $config['repo'];
    $user = $repo->read_record_by_id('user', $event->userid);
    $course = $repo->read_record_by_id('course', $event->courseid);
    $feedback = $repo->read_record_by_id('feedback', $feedbackitem->feedback);
    $lang = utils\get_course_lang($course);
    $presentedchoices = explode("|", substr($feedbackitem->presentation, 6));
    $choices = array_map(function ($presentation, $id) use ($lang) {
        $split = explode('####', $presentation);
        $rating = $split[0];
        $name = $split[1];
        return (object) [
            'id' => strval($id),
            'description' => [
                $lang => str_replace('<<<<<1', '', $name),
            ],            
        ];
    }, $presentedchoices, array_keys($presentedchoices));

    $selectedchoice = $choices[intval($feedbackvalue->value) - 1];

    // $json_pretty_string = json_encode($presentedchoices, JSON_PRETTY_PRINT);
    // error_log($json_pretty_string .PHP_EOL, 3, 'error.log');

    return [[
        'actor' => utils\get_user($config, $user),
        'verb' => [
            'id' => 'http://adlnet.gov/expapi/verbs/responded',
            'display' => [
                $lang => 'responded'
            ],
        ],
        'object' => [
            'id' => 'https://navy.mil/netc/xapi/activities/cmi.interactions/'.$feedbackitem->id,
            'definition' => [
                'type' => 'http://adlnet.gov/expapi/activities/cmi.interaction',
                'name' => [
                    $lang => isset($feedbackitem->label) && $feedbackitem->label != '' ? $feedbackitem->label : $feedbackitem->name,
                ],
                'description' => [
                    $lang => $feedbackitem->name
                ],
                'interactionType' => 'likert',
                'scale' => $choices,
            ]
        ],
        'timestamp' => utils\get_event_timestamp($event),
        'result' => [
            'response' => strval($selectedchoice->id),
            'completion' => $feedbackvalue->value !== '',
        ],
        'context' => [
            'platform' => $config['source_name'],
            'language' => $lang,
            'extensions' => utils\extensions\base($config, $event, $course),
            'contextActivities' => [
                'parent' => [
                    utils\get_activity\survey_profile($lang, $feedback),
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