<?php
// This file is part of the customcertpwt module for Moodle - http://moodle.org/
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
 * This page lists all the instances of customcertpwt in a particular course.
 *
 * @package    mod_customcertpwt
 * @copyright  2021 PowerTrain Inc
 * @license    All Rights Reserved
 */

require_once('../../config.php');

$id = required_param('id', PARAM_INT); // Course ID.

$course = $DB->get_record('course', array('id' => $id), '*', MUST_EXIST);

// Requires a login.
require_login($course);

// Set up the page variables.
$pageurl = new moodle_url('/mod/customcertpwt/index.php', array('id' => $course->id));
\mod_customcertpwt\page_helper::page_setup($pageurl, context_course::instance($id),
    get_string('modulenameplural', 'customcertpwt'));

// Additional page setup needed.
$PAGE->set_pagelayout('incourse');
$PAGE->navbar->add(get_string('modulenameplural', 'customcertpwt'));

// Add the page view to the Moodle log.
$event = \mod_customcertpwt\event\course_module_instance_list_viewed::create(array(
    'context' => context_course::instance($course->id)
));
$event->add_record_snapshot('course', $course);
$event->trigger();

// Get the customcertpwts, if there are none display a notice.
if (!$customcertpwts = get_all_instances_in_course('customcertpwt', $course)) {
    echo $OUTPUT->header();
    notice(get_string('nocustomcertpwts', 'customcertpwt'), new moodle_url('/course/view.php', array('id' => $course->id)));
    echo $OUTPUT->footer();
    exit();
}

// Create the table to display the different custom certificates.
$table = new html_table();

if ($usesections = course_format_uses_sections($course->format)) {
    $table->head = array(get_string('sectionname', 'format_'.$course->format), get_string('name'),
        get_string('receiveddate', 'customcertpwt'));
} else {
    $table->head = array(get_string('name'), get_string('receiveddate', 'customcertpwt'));
}

$currentsection = '';
foreach ($customcertpwts as $customcertpwt) {
    // Check if the customcertpwt is visible, if so show text as normal, else show it as dimmed.
    if ($customcertpwt->visible) {
        $link = html_writer::tag('a', $customcertpwt->name, array('href' => new moodle_url('/mod/customcertpwt/view.php',
            array('id' => $customcertpwt->coursemodule))));
    } else {
        $link = html_writer::tag('a', $customcertpwt->name, array('class' => 'dimmed',
            'href' => new moodle_url('/mod/customcertpwt/view.php', array('id' => $customcertpwt->coursemodule))));
    }
    // If we are at a different section then print a horizontal rule.
    if ($customcertpwt->section !== $currentsection) {
        if ($currentsection !== '') {
            $table->data[] = 'hr';
        }
        $currentsection = $customcertpwt->section;
    }
    // Check if there is was an issue provided for this user.
    if ($certrecord = $DB->get_record('customcertpwt_issues', array('userid' => $USER->id, 'customcertpwtid' => $customcertpwt->id))) {
        $issued = userdate($certrecord->timecreated);
    } else {
        $issued = get_string('notissued', 'customcertpwt');
    }
    // Only display the section column if the course format uses sections.
    if ($usesections) {
        $table->data[] = array($customcertpwt->section, $link, $issued);
    } else {
        $table->data[] = array($link, $issued);
    }
}

echo $OUTPUT->header();
echo html_writer::table($table);
echo $OUTPUT->footer();
