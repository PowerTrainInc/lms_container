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
 * customcertpwt module core interaction API
 *
 * @package    mod_customcertpwt
 * @copyright  2021 PowerTrain Inc
 * @license    All Rights Reserved
 */

defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');

/**
 * Add customcertpwt instance.
 *
 * @param stdClass $data
 * @param mod_customcertpwt_mod_form $mform
 * @return int new customcertpwt instance id
 */
function customcertpwt_add_instance($data, $mform) {
    global $DB;

    // Create a template for this customcertpwt to use.
    $context = context_module::instance($data->coursemodule);
    $template = \mod_customcertpwt\template::create($data->name, $context->id);

    // Add the data to the DB.
    $data->templateid = $template->get_id();
    $data->protection = \mod_customcertpwt\certificate::set_protection($data);
    $data->timecreated = time();
    $data->timemodified = $data->timecreated;
    $data->id = $DB->insert_record('customcertpwt', $data);

    // Add a page to this customcertpwt.
    $template->add_page();

    return $data->id;
}

/**
 * Update customcertpwt instance.
 *
 * @param stdClass $data
 * @param mod_customcertpwt_mod_form $mform
 * @return bool true
 */
function customcertpwt_update_instance($data, $mform) {
    global $DB;

    $data->protection = \mod_customcertpwt\certificate::set_protection($data);
    $data->timemodified = time();
    $data->id = $data->instance;

    return $DB->update_record('customcertpwt', $data);
}

/**
 * Given an ID of an instance of this module,
 * this function will permanently delete the instance
 * and any data that depends on it.
 *
 * @param int $id
 * @return bool true if successful
 */
function customcertpwt_delete_instance($id) {
    global $CFG, $DB;

    // Ensure the customcertpwt exists.
    if (!$customcertpwt = $DB->get_record('customcertpwt', array('id' => $id))) {
        return false;
    }

    // Get the course module as it is used when deleting files.
    if (!$cm = get_coursemodule_from_instance('customcertpwt', $id)) {
        return false;
    }

    // Delete the customcertpwt instance.
    if (!$DB->delete_records('customcertpwt', array('id' => $id))) {
        return false;
    }

	$context = context_module::instance($cm->id);

    // Now, delete the template associated with this certificate.
    if ($template = $DB->get_record('customcertpwt_templates', array('contextid' => $context->id))) {
        $template = new \mod_customcertpwt\template($template);
        $template->delete();
    }

    // Delete the customcertpwt issues.
    if (!$DB->delete_records('customcertpwt_issues', array('customcertpwtid' => $id))) {
        return false;
    }

    // Delete any files associated with the customcertpwt.
    //$context = context_module::instance($cm->id);
    $fs = get_file_storage();
    $fs->delete_area_files($context->id);

    return true;
}

/**
 * This function is used by the reset_course_userdata function in moodlelib.
 * This function will remove all posts from the specified customcertpwt
 * and clean up any related data.
 *
 * @param stdClass $data the data submitted from the reset course.
 * @return array status array
 */
function customcertpwt_reset_userdata($data) {
    global $DB;

    $componentstr = get_string('modulenameplural', 'customcertpwt');
    $status = array();

    if (!empty($data->reset_customcertpwt)) {
        $sql = "SELECT cert.id
                  FROM {customcertpwt} cert
                 WHERE cert.course = :courseid";
        $DB->delete_records_select('customcertpwt_issues', "customcertpwtid IN ($sql)", array('courseid' => $data->courseid));
        $status[] = array('component' => $componentstr, 'item' => get_string('deleteissuedcertificates', 'customcertpwt'),
            'error' => false);
    }

    return $status;
}

/**
 * Implementation of the function for printing the form elements that control
 * whether the course reset functionality affects the customcertpwt.
 *
 * @param mod_customcertpwt_mod_form $mform form passed by reference
 */
function customcertpwt_reset_course_form_definition(&$mform) {
    $mform->addElement('header', 'customcertpwtheader', get_string('modulenameplural', 'customcertpwt'));
    $mform->addElement('advcheckbox', 'reset_customcertpwt', get_string('deleteissuedcertificates', 'customcertpwt'));
}

/**
 * Course reset form defaults.
 *
 * @param stdClass $course
 * @return array
 */
function customcertpwt_reset_course_form_defaults($course) {
    return array('reset_customcertpwt' => 1);
}

/**
 * Returns information about received customcertpwt.
 * Used for user activity reports.
 *
 * @param stdClass $course
 * @param stdClass $user
 * @param stdClass $mod
 * @param stdClass $customcertpwt
 * @return stdClass the user outline object
 */
function customcertpwt_user_outline($course, $user, $mod, $customcertpwt) {
    global $DB;

    $result = new stdClass();
    if ($issue = $DB->get_record('customcertpwt_issues', array('customcertpwtid' => $customcertpwt->id, 'userid' => $user->id))) {
        $result->info = get_string('receiveddate', 'customcertpwt');
        $result->time = $issue->timecreated;
    } else {
        $result->info = get_string('notissued', 'customcertpwt');
    }

    return $result;
}

/**
 * Returns information about received customcertpwt.
 * Used for user activity reports.
 *
 * @param stdClass $course
 * @param stdClass $user
 * @param stdClass $mod
 * @param stdClass $customcertpwt
 * @return string the user complete information
 */
function customcertpwt_user_complete($course, $user, $mod, $customcertpwt) {
    global $DB, $OUTPUT;

    if ($issue = $DB->get_record('customcertpwt_issues', array('customcertpwtid' => $customcertpwt->id, 'userid' => $user->id))) {
        echo $OUTPUT->box_start();
        echo get_string('receiveddate', 'customcertpwt') . ": ";
        echo userdate($issue->timecreated);
        echo $OUTPUT->box_end();
    } else {
        print_string('notissued', 'customcertpwt');
    }
}

/**
 * Serves certificate issues and other files.
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param context $context
 * @param string $filearea
 * @param array $args
 * @param bool $forcedownload
 * @return bool|null false if file not found, does not return anything if found - just send the file
 */
function customcertpwt_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload) {
    global $CFG;

    require_once($CFG->libdir . '/filelib.php');

    // We are positioning the elements.
    if ($filearea === 'image') {
        if ($context->contextlevel == CONTEXT_MODULE) {
            require_login($course, false, $cm);
        } else if ($context->contextlevel == CONTEXT_SYSTEM && !has_capability('mod/customcertpwt:manage', $context)) {
            return false;
        }

        $relativepath = implode('/', $args);
        $fullpath = '/' . $context->id . '/mod_customcertpwt/image/' . $relativepath;

        $fs = get_file_storage();
        if (!$file = $fs->get_file_by_hash(sha1($fullpath)) or $file->is_directory()) {
            return false;
        }

        send_stored_file($file, 0, 0, $forcedownload);
    }
}

/**
 * The features this activity supports.
 *
 * @uses FEATURE_GROUPS
 * @uses FEATURE_GROUPINGS
 * @uses FEATURE_GROUPMEMBERSONLY
 * @uses FEATURE_MOD_INTRO
 * @uses FEATURE_COMPLETION_TRACKS_VIEWS
 * @uses FEATURE_GRADE_HAS_GRADE
 * @uses FEATURE_GRADE_OUTCOMES
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed True if module supports feature, null if doesn't know
 */
function customcertpwt_supports($feature) {
    switch ($feature) {
        case FEATURE_GROUPS:
            return true;
        case FEATURE_GROUPINGS:
            return true;
        case FEATURE_MOD_INTRO:
            return true;
        case FEATURE_SHOW_DESCRIPTION:
            return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS:
            return true;
        case FEATURE_BACKUP_MOODLE2:
            return true;
        default:
            return null;
    }
}

/**
 * Used for course participation report (in case customcertpwt is added).
 *
 * @return array
 */
function customcertpwt_get_view_actions() {
    return array('view', 'view all', 'view report');
}

/**
 * Used for course participation report (in case customcertpwt is added).
 *
 * @return array
 */
function customcertpwt_get_post_actions() {
    return array('received');
}

/**
 * Function to be run periodically according to the moodle cron.
 */
function customcertpwt_cron() {
    return true;
}

/**
 * Serve the edit element as a fragment.
 *
 * @param array $args List of named arguments for the fragment loader.
 * @return string
 */
function mod_customcertpwt_output_fragment_editelement($args) {
    global $DB;

    // Get the element.
    $element = $DB->get_record('customcertpwt_elements', array('id' => $args['elementid']), '*', MUST_EXIST);

    $pageurl = new moodle_url('/mod/customcertpwt/rearrange.php', array('pid' => $element->pageid));
    $form = new \mod_customcertpwt\edit_element_form($pageurl, array('element' => $element));

    return $form->render();
}

/**
 * This function extends the settings navigation block for the site.
 *
 * It is safe to rely on PAGE here as we will only ever be within the module
 * context when this is called.
 *
 * @param settings_navigation $settings
 * @param navigation_node $customcertpwtnode
 */
function customcertpwt_extend_settings_navigation(settings_navigation $settings, navigation_node $customcertpwtnode) {
    global $DB, $PAGE;

    $keys = $customcertpwtnode->get_children_key_list();
    $beforekey = null;
    $i = array_search('modedit', $keys);
    if ($i === false and array_key_exists(0, $keys)) {
        $beforekey = $keys[0];
    } else if (array_key_exists($i + 1, $keys)) {
        $beforekey = $keys[$i + 1];
    }

    if (has_capability('mod/customcertpwt:manage', $PAGE->cm->context)) {
        // Get the template id.
        $templateid = $DB->get_field('customcertpwt', 'templateid', array('id' => $PAGE->cm->instance));
        $node = navigation_node::create(get_string('editcustomcertpwt', 'customcertpwt'),
                new moodle_url('/mod/customcertpwt/edit.php', array('tid' => $templateid)),
                navigation_node::TYPE_SETTING, null, 'mod_customcertpwt_edit',
                new pix_icon('t/edit', ''));
        $customcertpwtnode->add_node($node, $beforekey);
    }

    if (has_capability('mod/customcertpwt:verifycertificate', $PAGE->cm->context)) {
        $node = navigation_node::create(get_string('verifycertificate', 'customcertpwt'),
            new moodle_url('/mod/customcertpwt/verify_certificate.php', array('contextid' => $PAGE->cm->context->id)),
            navigation_node::TYPE_SETTING, null, 'mod_customcertpwt_verify_certificate',
            new pix_icon('t/check', ''));
        $customcertpwtnode->add_node($node, $beforekey);
    }

    return $customcertpwtnode->trim_if_empty();
}

/**
 * Add nodes to myprofile page.
 *
 * @param \core_user\output\myprofile\tree $tree Tree object
 * @param stdClass $user user object
 * @param bool $iscurrentuser
 * @param stdClass $course Course object
 * @return bool
 */
function mod_customcertpwt_myprofile_navigation(core_user\output\myprofile\tree $tree, $user, $iscurrentuser, $course) {
    $url = new moodle_url('/mod/customcertpwt/my_certificates.php', array('userid' => $user->id));
    $node = new core_user\output\myprofile\node('miscellaneous', 'mycustomcertpwts',
        get_string('mycertificates', 'customcertpwt'), null, $url);
    $tree->add_node($node);
}

/**
 * Handles editing the 'name' of the element in a list.
 *
 * @param string $itemtype
 * @param int $itemid
 * @param string $newvalue
 * @return \core\output\inplace_editable
 */
function mod_customcertpwt_inplace_editable($itemtype, $itemid, $newvalue) {
    global $DB, $PAGE;

    if ($itemtype === 'elementname') {
        $element = $DB->get_record('customcertpwt_elements', array('id' => $itemid), '*', MUST_EXIST);
        $page = $DB->get_record('customcertpwt_pages', array('id' => $element->pageid), '*', MUST_EXIST);
        $template = $DB->get_record('customcertpwt_templates', array('id' => $page->templateid), '*', MUST_EXIST);

        // Set the template object.
        $template = new \mod_customcertpwt\template($template);
        // Perform checks.
        if ($cm = $template->get_cm()) {
            require_login($cm->course, false, $cm);
        } else {
            $PAGE->set_context(context_system::instance());
            require_login();
        }
        // Make sure the user has the required capabilities.
        $template->require_manage();

        // Clean input and update the record.
        $updateelement = new stdClass();
        $updateelement->id = $element->id;
        $updateelement->name = clean_param($newvalue, PARAM_TEXT);
        $DB->update_record('customcertpwt_elements', $updateelement);

        return new \core\output\inplace_editable('mod_customcertpwt', 'elementname', $element->id, true,
            $updateelement->name, $updateelement->name);
    }
}

/**
 * Get icon mapping for font-awesome.
 */
function mod_customcertpwt_get_fontawesome_icon_map() {
    return [
        'mod_customcertpwt:download' => 'fa-download'
    ];
}
