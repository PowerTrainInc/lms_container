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

function scorm_sco(array $config, $cmid, $scorm, $lang, $sco) {
    $scormname = property_exists($scorm, 'name') ? $scorm->name : 'SCORM';
    $scotitle = property_exists($sco, 'title')? $sco->title : $scormname;
    $scoid = property_exists($sco, 'identifier') ? $sco->identifier : $cmid;

    $id = $config['app_url'].'/mod/scorm/view.php?id='.$scoid;
    try {
        $courseid = $sco->scorm;
        $scoid = $sco->id;
        $url = $config['app_url'];
        $id = "$url/mod/scorm/$courseid/sco/$scoid";
    } catch (\Throwable $th) { }

    return [
        'id' => $id,
        'definition' => [
            'type' => 'http://adlnet.gov/expapi/activities/lesson',
            'name' => [
                $lang => $scotitle,
            ],
        ],
    ];
}