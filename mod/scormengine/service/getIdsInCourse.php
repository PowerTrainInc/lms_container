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


require(__DIR__.'/../../../config.php');
require_once(__DIR__.'/../lib.php');
require_once(__DIR__.'/../seo_xapi.php');
require_once(__DIR__.'/auth.php');
if (!$allowed) {
    return;
}

$queries = array();

parse_str($_SERVER['QUERY_STRING'], $queries);

$course = $DB->get_record('course', array('id' => $queries['context_id']), '*', MUST_EXIST);

$modinfo = get_fast_modinfo($course);
$scorms = $modinfo->get_instances_of("scormengine");

$scos = [];
foreach ($scorms as $scorm) {
    $t = $scorm->get_course_module_record(true);
    $t->name = $scorm->get_formatted_name();
    $moduleinstance = $DB->get_record('scormengine', array('id' => $t->instance), '*', MUST_EXIST);
    $t->activity_id = "https://navy.mil/netc/xapi/activities/courses/{$moduleinstance->package_id}";
    array_push($scos, $t);
}

$quizes = $modinfo->get_instances_of("quiz");


foreach ($quizes as $quiz) {
    $t = $quiz->get_course_module_record(true);
    $t->name = $quiz->get_formatted_name();
    // $moduleinstance = $DB->get_record('quiz', array('id' => $t->instance), '*', MUST_EXIST);
    $t->activity_id = "https://navy.mil/netc/xapi/activities/assessments/{$t->instance}";
    array_push($scos, $t);
}

header('Content-Type: application/json');
echo json_encode($scos, JSON_PRETTY_PRINT);
