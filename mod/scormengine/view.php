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


$id = optional_param('id', 0, PARAM_INT);


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

require_login($course, true, $cm);

$modulecontext = context_module::instance($cm->id);


$PAGE->set_url('/mod/scormengine/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($moduleinstance->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($modulecontext);

console_log($moduleinstance);
console_log('coures module');
console_log($cm);
console_log($USER);

$button = '';
$settings = get_config('scormengine');
$existingreg = $DB->get_record('scormengine_registration',
array("completion" => 0, 'course_id' => $course->id, 'mod_id' => $moduleinstance->id, "user_id" => $USER->id, 'package_id' => $moduleinstance->package_id ),
 '*', IGNORE_MISSING);

if ($existingreg == false) {

    $uuid = uuid();
    $launchlink = se_postJSON('/registrations/withLaunchLink', array(
        'registration' => array (
            'courseId' => $moduleinstance->package_id,
            'learner' => array (
                'id' => empty($USER->idnumber) ? $USER->username : $USER->idnumber,
                'firstName' => $USER->firstname,
                'lastName' => $USER->lastname,
            ),
            'registrationId' => $uuid,
            'forCredit' => true,

        ),
        'launchLink' => array (
            "redirectOnExitUrl" => $settings->site_home.'/mod/scormengine/return.php?rid='.$uuid.'&id='.$id
            )
        ));

    if (!$launchlink) {
        echo "There was an error connecting to Scorm Engine.";
        return;
    }
    $newreg = array(
        'course_id' => $course->id,
        'mod_id' => $moduleinstance->id,
        "user_id" => $USER->id,
        "registration" => $uuid,
        'package_id' => $moduleinstance->package_id,
        "completion" => -1,
        "success" => 0,
        "score" => 0,
        "duration" => 0,
        "progress" => 0,
    );
    $DB->insert_record('scormengine_registration', $newreg);


    $settings = get_config('scormengine');

    $button = "<a  class='btn btn-raised btn-primary' href='"
        .$settings->site_home.'/mod/scormengine/xapi_initialize.php?rid='.$uuid
        .'&courselink='.urlencode($settings->launchPrefix.$launchlink->launchLink)."'>Start</a>";

    array('course_id' => $course->id, 'mod_id' => $moduleinstance->id, "user_id" => $USER->id );
} else {

    $launchlink = se_postJSON('/registrations/'.$existingreg->registration."/launchLink", array (
        "redirectOnExitUrl" => $settings->site_home.'/mod/scormengine/return.php?rid='.$existingreg->registration.'&id='.$id
    ));

    if (!$launchlink) {
        echo "There was an error connecting to Scorm Engine.";
        return;
    }

    if ($existingreg->completion == -1) {
        $button = "<a class='btn btn-raised btn-primary' href='".$settings->launchPrefix.$launchlink->launchLink."' >Start</a>";
    } else {
        $button = "<a class='btn btn-raised btn-primary' href='".$settings->launchPrefix.$launchlink->launchLink."' >Resume</a>";
    }

}

echo $OUTPUT->header();
echo '<h1>'.format_string($moduleinstance->name).'</h1>';
echo '<p>'.format_string($moduleinstance->intro).'</p>';
echo $button;
echo $OUTPUT->footer();
