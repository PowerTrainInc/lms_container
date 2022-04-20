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
 * Handles loading a customcertpwt template.
 *
 * @package    mod_customcertpwt
 * @copyright  2021 PowerTrain Inc
 * @license    All Rights Reserved
 */

require_once('../../config.php');

$tid = required_param('tid', PARAM_INT);
$ltid = required_param('ltid', PARAM_INT); // The template to load.
$confirm = optional_param('confirm', 0, PARAM_INT);

$template = $DB->get_record('customcertpwt_templates', array('id' => $tid), '*', MUST_EXIST);
$template = new \mod_customcertpwt\template($template);

$loadtemplate = $DB->get_record('customcertpwt_templates', array('id' => $ltid), '*', MUST_EXIST);
$loadtemplate = new \mod_customcertpwt\template($loadtemplate);

if ($cm = $template->get_cm()) {
    require_login($cm->course, false, $cm);
} else {
    require_login();
}
$template->require_manage();

if ($template->get_context()->contextlevel == CONTEXT_MODULE) {
    $customcertpwt = $DB->get_record('customcertpwt', ['id' => $cm->instance], '*', MUST_EXIST);
    $title = $customcertpwt->name;
    $heading = format_string($title);
} else {
    $title = $SITE->fullname;
    $heading = $title;
}

// Check that they have confirmed they wish to load the template.
if ($confirm && confirm_sesskey()) {
    // First, remove all the existing elements and pages.
    $sql = "SELECT e.*
              FROM {customcertpwt_elements} e
        INNER JOIN {customcertpwt_pages} p
                ON e.pageid = p.id
             WHERE p.templateid = :templateid";
    if ($elements = $DB->get_records_sql($sql, array('templateid' => $template->get_id()))) {
        foreach ($elements as $element) {
            // Get an instance of the element class.
            if ($e = \mod_customcertpwt\element_factory::get_element_instance($element)) {
                $e->delete();
            }
        }
    }

    // Delete the pages.
    $DB->delete_records('customcertpwt_pages', array('templateid' => $template->get_id()));

    // Copy the items across.
    $loadtemplate->copy_to_template($template->get_id());

    // Redirect.
    $url = new moodle_url('/mod/customcertpwt/edit.php', array('tid' => $tid));
    redirect($url);
}

// Create the link options.
$nourl = new moodle_url('/mod/customcertpwt/edit.php', array('tid' => $tid));
$yesurl = new moodle_url('/mod/customcertpwt/load_template.php', array('tid' => $tid,
                                                                    'ltid' => $ltid,
                                                                    'confirm' => 1,
                                                                    'sesskey' => sesskey()));

$pageurl = new moodle_url('/mod/customcertpwt/load_template.php', array('tid' => $tid, 'ltid' => $ltid));
\mod_customcertpwt\page_helper::page_setup($pageurl, $template->get_context(), $title);

$str = get_string('editcustomcertpwt', 'customcertpwt');
$link = new moodle_url('/mod/customcertpwt/edit.php', array('tid' => $template->get_id()));
$PAGE->navbar->add($str, new \action_link($link, $str));
$PAGE->navbar->add(get_string('loadtemplate', 'customcertpwt'));

// Show a confirmation page.
echo $OUTPUT->header();
echo $OUTPUT->heading($heading);
echo $OUTPUT->confirm(get_string('loadtemplatemsg', 'customcertpwt'), $yesurl, $nourl);
echo $OUTPUT->footer();