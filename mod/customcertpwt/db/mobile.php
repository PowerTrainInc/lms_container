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

/**
 * Defines mobile handlers.
 *
 * @package   mod_customcertpwt
 * @copyright 2021 PowerTrain Inc
 * @license   All Rights Reserved
 */

defined('MOODLE_INTERNAL') || die();

$addons = [
    'mod_customcertpwt' => [ // Plugin identifier.
        'handlers' => [ // Different places where the plugin will display content.
            'issueview' => [ // Handler unique name.
                'displaydata' => [
                    'icon' => $CFG->wwwroot . '/mod/customcertpwt/pix/icon.png',
                    'class' => 'core-course-module-customcertpwt-handler',
                ],
                'delegate' => 'CoreCourseModuleDelegate', // Delegate (where to display the link to the plugin).
                'method' => 'mobile_view_activity', // Main function in \mod_customcertpwt\output\mobile.
                'styles' => [
                    'url' => '/mod/customcertpwt/mobile/styles.css',
                    'version' => 1
                ]
            ]
        ],
        'lang' => [ // Language strings that are used in all the handlers.
            ['deleteissueconfirm', 'customcertpwt'],
            ['getcustomcertpwt', 'customcertpwt'],
            ['listofissues', 'customcertpwt'],
            ['nothingtodisplay', 'moodle'],
            ['notissued', 'customcertpwt'],
            ['pluginname', 'customcertpwt'],
            ['receiveddate', 'customcertpwt'],
            ['requiredtimenotmet', 'customcertpwt'],
            ['selectagroup', 'moodle']
        ]
    ]
];
