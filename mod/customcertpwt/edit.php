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
 * Edit the customcertpwt settings.
 *
 * @package    mod_customcertpwt
 * @copyright  2021 PowerTrain Inc
 * @license    All Rights Reserved
 */

require_once('../../config.php');

$tid = optional_param('tid', 0, PARAM_INT);
$action = optional_param('action', '', PARAM_ALPHA);
if ($action) {
    $actionid = required_param('aid', PARAM_INT);
}
$confirm = optional_param('confirm', 0, PARAM_INT);

// Edit an existing template.
if ($tid) {
    // Create the template object.
    $template = $DB->get_record('customcertpwt_templates', array('id' => $tid), '*', MUST_EXIST);
    $template = new \mod_customcertpwt\template($template);
    // Set the context.
    $contextid = $template->get_contextid();
    // Set the page url.
    $pageurl = new moodle_url('/mod/customcertpwt/edit.php', array('tid' => $tid));
} else { // Adding a new template.
    // Need to supply the contextid.
    $contextid = required_param('contextid', PARAM_INT);
    // Set the page url.
    $pageurl = new moodle_url('/mod/customcertpwt/edit.php', array('contextid' => $contextid));
}

$context = context::instance_by_id($contextid);
if ($context->contextlevel == CONTEXT_MODULE) {
    $cm = get_coursemodule_from_id('customcertpwt', $context->instanceid, 0, false, MUST_EXIST);
    require_login($cm->course, false, $cm);

    $customcertpwt = $DB->get_record('customcertpwt', ['id' => $cm->instance], '*', MUST_EXIST);
    $title = $customcertpwt->name;
    $heading = format_string($title);
} else {
    require_login();
    $title = $SITE->fullname;
    $heading = $title;
}

require_capability('mod/customcertpwt:manage', $context);

// Set up the page.
\mod_customcertpwt\page_helper::page_setup($pageurl, $context, $title);

if ($context->contextlevel == CONTEXT_SYSTEM) {
    // We are managing a template - add some navigation.
    $PAGE->navbar->add(get_string('managetemplates', 'customcertpwt'),
        new moodle_url('/mod/customcertpwt/manage_templates.php'));
    if (!$tid) {
        $PAGE->navbar->add(get_string('editcustomcertpwt', 'customcertpwt'));
    } else {
        $PAGE->navbar->add(get_string('editcustomcertpwt', 'customcertpwt'),
            new moodle_url('/mod/customcertpwt/edit.php', ['tid' => $tid]));
    }
}

// Flag to determine if we are deleting anything.
$deleting = false;

if ($tid) {
    if ($action && confirm_sesskey()) {
        switch ($action) {
            case 'pmoveup' :
                $template->move_item('page', $actionid, 'up');
                break;
            case 'pmovedown' :
                $template->move_item('page', $actionid, 'down');
                break;
            case 'emoveup' :
                $template->move_item('element', $actionid, 'up');
                break;
            case 'emovedown' :
                $template->move_item('element', $actionid, 'down');
                break;
            case 'addpage' :
                $template->add_page();
                $url = new \moodle_url('/mod/customcertpwt/edit.php', array('tid' => $tid));
                redirect($url);
                break;
            case 'deletepage' :
                if (!empty($confirm)) { // Check they have confirmed the deletion.
                    $template->delete_page($actionid);
                    $url = new \moodle_url('/mod/customcertpwt/edit.php', array('tid' => $tid));
                    redirect($url);
                } else {
                    // Set deletion flag to true.
                    $deleting = true;
                    // Create the message.
                    $message = get_string('deletepageconfirm', 'customcertpwt');
                    // Create the link options.
                    $nourl = new moodle_url('/mod/customcertpwt/edit.php', array('tid' => $tid));
                    $yesurl = new moodle_url('/mod/customcertpwt/edit.php',
                        array(
                            'tid' => $tid,
                            'action' => 'deletepage',
                            'aid' => $actionid,
                            'confirm' => 1,
                            'sesskey' => sesskey()
                        )
                    );
                }
                break;
            case 'deleteelement' :
                if (!empty($confirm)) { // Check they have confirmed the deletion.
                    $template->delete_element($actionid);
                } else {
                    // Set deletion flag to true.
                    $deleting = true;
                    // Create the message.
                    $message = get_string('deleteelementconfirm', 'customcertpwt');
                    // Create the link options.
                    $nourl = new moodle_url('/mod/customcertpwt/edit.php', array('tid' => $tid));
                    $yesurl = new moodle_url('/mod/customcertpwt/edit.php',
                        array(
                            'tid' => $tid,
                            'action' => 'deleteelement',
                            'aid' => $actionid,
                            'confirm' => 1,
                            'sesskey' => sesskey()
                        )
                    );
                }
                break;
        }
    }
}

// Check if we are deleting either a page or an element.
if ($deleting) {
    // Show a confirmation page.
    $PAGE->navbar->add(get_string('deleteconfirm', 'customcertpwt'));
    echo $OUTPUT->header();
    echo $OUTPUT->heading($heading);
    echo $OUTPUT->confirm($message, $yesurl, $nourl);
    echo $OUTPUT->footer();
    exit();
}

if ($tid) {
    $mform = new \mod_customcertpwt\edit_form($pageurl, array('tid' => $tid));
    // Set the name for the form.
    $mform->set_data(array('name' => $template->get_name()));
} else {
    $mform = new \mod_customcertpwt\edit_form($pageurl);
}

if ($data = $mform->get_data()) {
    // If there is no id, then we are creating a template.
    if (!$tid) {
        $template = \mod_customcertpwt\template::create($data->name, $contextid);

        // Create a page for this template.
        $pageid = $template->add_page();

        // Associate all the data from the form to the newly created page.
        $width = 'pagewidth_' . $pageid;
        $height = 'pageheight_' . $pageid;
        $leftmargin = 'pageleftmargin_' . $pageid;
        $rightmargin = 'pagerightmargin_' . $pageid;
        $rightmargin = 'pagerightmargin_' . $pageid;

        $data->$width = $data->pagewidth_0;
        $data->$height = $data->pageheight_0;
        $data->$leftmargin = $data->pageleftmargin_0;
        $data->$rightmargin = $data->pagerightmargin_0;

        // We may also have clicked to add an element, so these need changing as well.
        if (isset($data->element_0) && isset($data->addelement_0)) {
            $element = 'element_' . $pageid;
            $addelement = 'addelement_' . $pageid;
            $data->$element = $data->element_0;
            $data->$addelement = $data->addelement_0;

            // Need to remove the temporary element and add element placeholders so we
            // don't try add an element to the wrong page.
            unset($data->element_0);
            unset($data->addelement_0);
        }
    }

    // Save any data for the template.
    $template->save($data);

    // Save any page data.
    $template->save_page($data);

    // Loop through the data.
    foreach ($data as $key => $value) {
        // Check if they chose to add an element to a page.
        if (strpos($key, 'addelement_') !== false) {
            // Get the page id.
            $pageid = str_replace('addelement_', '', $key);
            // Get the element.
            $element = "element_" . $pageid;
            $element = $data->$element;
            // Create the URL to redirect to to add this element.
            $params = array();
            $params['tid'] = $template->get_id();
            $params['action'] = 'add';
            $params['element'] = $element;
            $params['pageid'] = $pageid;
            $url = new moodle_url('/mod/customcertpwt/edit_element.php', $params);
            redirect($url);
        }
    }

    // Check if we want to preview this custom certificate.
    if (!empty($data->previewbtn)) {
        $template->generate_pdf(true);
        exit();
    }

    // Redirect to the editing page to show form with recent updates.
    $url = new moodle_url('/mod/customcertpwt/edit.php', array('tid' => $template->get_id()));
    redirect($url);
}

echo $OUTPUT->header();
echo $OUTPUT->heading($heading);
$mform->display();
if ($tid && $context->contextlevel == CONTEXT_MODULE) {
    $loadtemplateurl = new moodle_url('/mod/customcertpwt/load_template.php', array('tid' => $tid));
    $loadtemplateform = new \mod_customcertpwt\load_template_form($loadtemplateurl, array('context' => $context), 'post',
        '', array('id' => 'loadtemplateform'));
    $loadtemplateform->display();
}
echo $OUTPUT->footer();
