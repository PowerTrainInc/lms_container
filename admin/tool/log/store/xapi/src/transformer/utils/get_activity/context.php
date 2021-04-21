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

function netc_context($config, $event, $course, $xapiobject = null, $attempt = null) {
    $lang = utils\get_course_lang($course);
    $extensions = utils\extensions\base($config, $event, $course);

    $context = [
        'platform' => $config['source_name'],
        'language' => $lang,
        'extensions' => $extensions,
        'contextActivities' => [
            // 'grouping' => [
            //     utils\get_activity\site($config)
            // ],
            'category' => [
                utils\get_activity\netc_profile(),
                utils\get_activity\netc_elearning_profile(),
            ]
        ],
    ];

    if (!is_null($xapiobject) && !is_null($attempt)) {
        $context['registration'] = utils\get_attempt_id($event->userid, $xapiobject['id'], $attempt);
    }

    return $context;
}
