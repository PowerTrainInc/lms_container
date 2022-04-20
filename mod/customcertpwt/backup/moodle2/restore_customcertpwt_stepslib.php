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
 * Define all the restore steps that will be used by the restore_customcertpwt_activity_task.
 *
 * @package    mod_customcertpwt
 * @copyright  2021 PowerTrain Inc
 * @license    All Rights Reserved
 */

defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');

/**
 * Define the complete customcertpwt structure for restore, with file and id annotations.
 *
 * @package    mod_customcertpwt
 * @copyright  2021 PowerTrain Inc
 * @license    All Rights Reserved
 */
class restore_customcertpwt_activity_structure_step extends restore_activity_structure_step {

    /**
     * Define the different items to restore.
     *
     * @return array the restore paths
     */
    protected function define_structure() {
        // The array used to store the path to the items we want to restore.
        $paths = array();

        // The customcertpwt instance.
        $paths[] = new restore_path_element('customcertpwt', '/activity/customcertpwt');

        // The templates.
        $paths[] = new restore_path_element('customcertpwt_template', '/activity/customcertpwt/template');

        // The pages.
        $paths[] = new restore_path_element('customcertpwt_page', '/activity/customcertpwt/template/pages/page');

        // The elements.
        $paths[] = new restore_path_element('customcertpwt_element', '/activity/customcertpwt/template/pages/page/element');

        // Check if we want the issues as well.
        if ($this->get_setting_value('userinfo')) {
            $paths[] = new restore_path_element('customcertpwt_issue', '/activity/customcertpwt/issues/issue');
        }

        // Return the paths wrapped into standard activity structure.
        return $this->prepare_activity_structure($paths);
    }

    /**
     * Handles restoring the customcertpwt activity.
     *
     * @param stdClass $data the customcertpwt data
     */
    protected function process_customcertpwt($data) {
        global $DB;

        $data = (object) $data;
        $data->course = $this->get_courseid();
        $data->timecreated = $this->apply_date_offset($data->timecreated);
        $data->timemodified = $this->apply_date_offset($data->timemodified);

        // Insert the customcertpwt record.
        $newitemid = $DB->insert_record('customcertpwt', $data);

        // Immediately after inserting record call this.
        $this->apply_activity_instance($newitemid);
    }

    /**
     * Handles restoring a customcertpwt page.
     *
     * @param stdClass $data the customcertpwt data
     */
    protected function process_customcertpwt_template($data) {
        global $DB;

        $data = (object) $data;
        $oldid = $data->id;

        $data->contextid = $this->task->get_contextid();
        $data->timecreated = $this->apply_date_offset($data->timecreated);
        $data->timemodified = $this->apply_date_offset($data->timemodified);

        $newitemid = $DB->insert_record('customcertpwt_templates', $data);
        $this->set_mapping('customcertpwt_template', $oldid, $newitemid);

        // Update the template id for the customcertpwt.
        $customcertpwt = new stdClass();
        $customcertpwt->id = $this->get_new_parentid('customcertpwt');
        $customcertpwt->templateid = $newitemid;
        $DB->update_record('customcertpwt', $customcertpwt);
    }

    /**
     * Handles restoring a customcertpwt template.
     *
     * @param stdClass $data the customcertpwt data
     */
    protected function process_customcertpwt_page($data) {
        global $DB;

        $data = (object) $data;
        $oldid = $data->id;

        $data->templateid = $this->get_new_parentid('customcertpwt_template');
        $data->timecreated = $this->apply_date_offset($data->timecreated);
        $data->timemodified = $this->apply_date_offset($data->timemodified);

        $newitemid = $DB->insert_record('customcertpwt_pages', $data);
        $this->set_mapping('customcertpwt_page', $oldid, $newitemid);
    }

    /**
     * Handles restoring a customcertpwt element.
     *
     * @param stdclass $data the customcertpwt data
     */
    protected function process_customcertpwt_element($data) {
        global $DB;

        $data = (object) $data;
        $oldid = $data->id;

        $data->pageid = $this->get_new_parentid('customcertpwt_page');
        $data->timecreated = $this->apply_date_offset($data->timecreated);
        $data->timemodified = $this->apply_date_offset($data->timemodified);

        $newitemid = $DB->insert_record('customcertpwt_elements', $data);
        $this->set_mapping('customcertpwt_element', $oldid, $newitemid);
    }

    /**
     * Handles restoring a customcertpwt issue.
     *
     * @param stdClass $data the customcertpwt data
     */
    protected function process_customcertpwt_issue($data) {
        global $DB;

        $data = (object) $data;
        $oldid = $data->id;

        $data->customcertpwtid = $this->get_new_parentid('customcertpwt');
        $data->timecreated = $this->apply_date_offset($data->timecreated);

        $newitemid = $DB->insert_record('customcertpwt_issues', $data);
        $this->set_mapping('customcertpwt_issue', $oldid, $newitemid);
    }

    /**
     * Called immediately after all the other restore functions.
     */
    protected function after_execute() {
        parent::after_execute();

        // Add the files.
        $this->add_related_files('mod_customcertpwt', 'intro', null);

        // Note - we can't use get_old_contextid() as it refers to the module context.
        $this->add_related_files('mod_customcertpwt', 'image', null, $this->get_task()->get_info()->original_course_contextid);
    }
}
