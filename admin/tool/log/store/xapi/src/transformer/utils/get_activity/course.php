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

namespace src\transformer\utils\get_activity;
defined('MOODLE_INTERNAL') || die();

use src\transformer\utils as utils;

function htmlToPlainText($str) {
    $str = str_replace('$nbsp;', ' ', $str);
    $str = html_entity_decode($str, ENT_QUOTES | ENT_COMPAT, 'UTF-8');
    $str = html_entity_decode($str, ENT_HTML5, 'UTF-8');
    $str = html_entity_decode($str);
    $str = htmlspecialchars_decode($str);
    $str = strip_tags($str);
    return $str;
}

function course(array $config, \stdClass $course) {
    $coursename = $course->fullname ? $course->fullname : 'A Moodle course';
    $coursedescription = $course->summary;
    $courselang = utils\get_course_lang($course);

    $object = [
                  'id' => 'https://navy.mil/netc/xapi/activities/courses/'.$course->id,
                  'definition' => [
                      'type' => 'http://adlnet.gov/expapi/activities/course',
                      'name' => [
                          $courselang => $coursename,
                      ],
                  ],
              ];

    if (trim($coursedescription) !== '') {
        $object['definition']['description'] = [
            $courselang => htmlToPlainText($coursedescription)
        ];
    }

    // if (utils\is_enabled_config($config, 'send_short_course_id')) {
    //     $object['definition']['extensions']['https://w3id.org/learning-analytics/learning-management-system/short-id'] = $course->shortname;
    // }

    // if (utils\is_enabled_config($config, 'send_course_and_module_idnumber')) {
    //     $courseidnumber = property_exists($course, 'idnumber') ? $course->idnumber : null;
    //     $object['definition']['extensions']['https://w3id.org/learning-analytics/learning-management-system/external-id'] = $courseidnumber;
    // }

    return $object;
}
