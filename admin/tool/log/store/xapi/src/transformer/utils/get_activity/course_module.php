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

function course_module(array $config, $course, $cmid, $xapitype) {
    $repo = $config['repo'];
    $coursemodule = $repo->read_record_by_id('course_modules', $cmid);
    $module = $repo->read_record_by_id('modules', $coursemodule->module);
    $instance = $repo->read_record_by_id($module->name, $coursemodule->instance);

    $coursemoduleurl = 'https://navy.mil/netc/xapi/activities/courses/'.$cmid;
    $courselang = utils\get_course_lang($course);
    $instancename = property_exists($instance, 'name') ? $instance->name : $module->name;

    // $json_pretty_string = json_encode($course, JSON_PRETTY_PRINT);
    // error_log($json_pretty_string .PHP_EOL, 3, 'error.log');

    $object = [
        'id' => $coursemoduleurl,
        'definition' => [
            'type' => $xapitype,
            'name' => [
                $courselang => $course->fullname,
            ]
        ],
    ];

    if (property_exists($course, 'summary')) {
        $object = [
            'id' => $coursemoduleurl,
            'definition' => [
                'type' => $xapitype,
                'name' => [
                    $courselang => $course->fullname,
                ],
                'description' => [
                    $courselang => utils\get_string_html_removed($course->summary),
                ],
            ],
        ];
    }else{
        $object = [
            'id' => $coursemoduleurl,
            'definition' => [
                'type' => $xapitype,
                'name' => [
                    $courselang => $course->fullname,
                ]
            ],
        ];
    }

    if (utils\is_enabled_config($config, 'send_course_and_module_idnumber')) {
        $moduleidnumber = property_exists($coursemodule, 'idnumber') ? $coursemodule->idnumber : null;
        $object['definition']['extensions']['https://w3id.org/learning-analytics/learning-management-system/external-id'] = $moduleidnumber;
    }

    return $object;
}
