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
 * Serves files for the mobile app.
 *
 * @package   mod_customcertpwt
 * @copyright 2021 PowerTrain Inc
 * @license   All Rights Reserved
 */

/**
 * AJAX_SCRIPT - exception will be converted into JSON.
 */
define('AJAX_SCRIPT', true);

/**
 * NO_MOODLE_COOKIES - we don't want any cookie.
 */
define('NO_MOODLE_COOKIES', true);

require_once('../../../config.php');
require_once($CFG->libdir . '/filelib.php');
require_once($CFG->libdir . '/completionlib.php');
require_once($CFG->dirroot . '/webservice/lib.php');

// Allow CORS requests.
header('Access-Control-Allow-Origin: *');

// Authenticate the user.
$token = required_param('token', PARAM_ALPHANUM);
$certificateid = required_param('certificateid', PARAM_INT);
$userid = required_param('userid', PARAM_INT);

$webservicelib = new webservice();
$authenticationinfo = $webservicelib->authenticate_user($token);

// Check the service allows file download.
$enabledfiledownload = (int) ($authenticationinfo['service']->downloadfiles);
if (empty($enabledfiledownload)) {
    throw new webservice_access_exception('Web service file downloading must be enabled in external service settings');
}

$cm = get_coursemodule_from_instance('customcertpwt', $certificateid, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$certificate = $DB->get_record('customcertpwt', ['id' => $certificateid], '*', MUST_EXIST);
$template = $DB->get_record('customcertpwt_templates', ['id' => $certificate->templateid], '*', MUST_EXIST);

// Capabilities check.
require_capability('mod/customcertpwt:view', \context_module::instance($cm->id));
if ($userid != $USER->id) {
    require_capability('mod/customcertpwt:viewreport', \context_module::instance($cm->id));
} else {
    // Make sure the user has met the required time.
    if ($certificate->requiredtime) {
        if (\mod_customcertpwt\certificate::get_course_time($certificate->course) < ($certificate->requiredtime * 60)) {
            exit();
        }
    }
}

$issue = $DB->get_record('customcertpwt_issues', ['customcertpwtid' => $certificateid, 'userid' => $userid]);

// If we are doing it for the logged in user then we want to issue the certificate.
if (!$issue) {
    // If the other user doesn't have an issue, then there is nothing to do.
    if ($userid != $USER->id) {
        exit();
    }

    \mod_customcertpwt\certificate::issue_certificate($certificate->id, $USER->id);

    // Set the custom certificate as viewed.
    $completion = new completion_info($course);
    $completion->set_module_viewed($cm);
}

// Now we want to generate the PDF.
$template = new \mod_customcertpwt\template($template);
$template->generate_pdf(false, $userid);
exit();
