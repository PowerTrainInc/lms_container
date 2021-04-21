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
 * Prints an instance of mod_scormengine.
 *
 * @package     mod_scormengine
 * @copyright   Veracity
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__.'/../../config.php');
require_once(__DIR__.'/lib.php');
require_once(__DIR__.'/seo_xapi.php');

// Course_module ID, or
$rid = optional_param('rid', 0, PARAM_TEXT);
// Course_module ID, or
$id = optional_param('id', 0, PARAM_INT);

// ... module instance id.
$s  = optional_param('s', 0, PARAM_INT);

if ($id) {
    $cm             = get_coursemodule_from_id('scormengine', $id, 0, false, MUST_EXIST);
    $course         = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $moduleinstance = $DB->get_record('scormengine', array('id' => $cm->instance), '*', MUST_EXIST);
} else if ($s) {
    $moduleinstance = $DB->get_record('scormengine', array('id' => $n), '*', MUST_EXIST);
    $course         = $DB->get_record('course', array('id' => $moduleinstance->course), '*', MUST_EXIST);
    $cm             = get_coursemodule_from_instance('scormengine', $moduleinstance->id, $course->id, false, MUST_EXIST);
} else {
    print_error(get_string('missingidandcmid', 'mod_scormengine'));
}


$existingReg = $DB->get_record('scormengine_registration', array('course_id' => $course->id, 'mod_id' => $moduleinstance->id, "user_id" => $USER->id, "registration" => $rid ), '*', IGNORE_MISSING);
require_login($course, true, $cm);

if($existingReg == false)
{
    echo $OUTPUT->header();

    echo "There was an error. This registration ID does not exist";
    echo $OUTPUT->footer();
    return;
}

// $settings = get_config('scormengine');
// header("Location: {$settings->site_home}/course/view.php?id=".$course->id."#section-0");
/*
$event = \mod_scormengine\event\course_module_viewed::create(array(
    'objectid' => $moduleinstance->id,
    'context' => $modulecontext
));

$event->add_record_snapshot('course', $course);
$event->add_record_snapshot('scormengine', $moduleinstance);
$event->trigger();
*/


console_log($USER);

$link = '/registrations/' . $rid . '?includeRuntime=true&includeInteractionsAndObjectives=true&includeChildResults=true';
$results = se_get($link);

$wasAlreadyCompleted = $existingReg->completion == 1 ? true : false;

$existingReg->completion = 0;
if($results->registrationCompletion == 'COMPLETED')
    $existingReg->completion = 1;


if($results->registrationSuccess == 'PASSED')
    $existingReg->success = 1;
$existingReg->duration = $results->totalSecondsTracked;
$existingReg->progress = $results->registrationCompletionAmount * 100;

if(isset($results->score->scaled))
{
    $existingReg->score = $results->score->scaled;
}
$DB->update_record('scormengine_registration', $existingReg);

$button = "<pre>".json_encode($results, JSON_PRETTY_PRINT)."</pre>";
$button2 = "<pre>".json_encode($existingReg, JSON_PRETTY_PRINT)."</pre>";

$settings = get_config('scormengine');

if($results->registrationCompletion == 'COMPLETED' && !$wasAlreadyCompleted)
{ 
    console_log($existingReg);
    $completion=new completion_info($course);
    $completion->update_state($cm,COMPLETION_COMPLETE );
    send_xapi_statements_from_seo('TERMINATE', $results, $settings->lrs_endpoint, $settings->lrs_username, $settings->lrs_password);
}

if($results->registrationCompletion == 'COMPLETED')
{
    if(isset($results->score->scaled))
    {
        $grade = new stdClass();
        $grade->feedback = $results->registrationSuccess;
        $grade->feedbackformat = 1; // FORMAT_HTML Plain HTML (with some tags stripped)
        $grade->rawgrade = $results->score->scaled; // A number that is limited to the maxgrade column setting in grade_items table
        $grade->yourmodule_id = $cm->id; // The unique index id of your module's main DB table
        $grade->timemodified = time();
        $grade->userid = $USER->id;
        $grade->usermodified = $USER->id;


        if (!function_exists('grade_update')) { //workaround for buggy PHP versions
            require_once($CFG->libdir.'/gradelib.php');
        }

        $params = array('itemname'=>$moduleinstance->name, 'idnumber'=> $cm->id);
            $params['gradetype'] = GRADE_TYPE_VALUE;
            $params['grademax']  = 100;
            $params['grademin']  = 0;


        grade_update('mod/scormengine', $moduleinstance->course, 'mod', 'scormengine', $grade->yourmodule_id, 0, $grade, $params);
    }
}

send_xapi_statements_from_seo(null, $results, $settings->lrs_endpoint, $settings->lrs_username, $settings->lrs_password);
// $settings = get_config('scormengine');
// header("Location: {$settings->site_home}/course/view.php?id=".$course->id."#section-0");
redirect($settings->site_home.'/course/view.php?id='.$course->id.'#section-0');


// echo $OUTPUT->header();
// echo $button2;
// echo $button;
// echo "back to course";
// echo $OUTPUT->footer();

