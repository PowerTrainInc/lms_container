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

require_once($CFG->dirroot . '/mod/customcertpwt/backup/moodle2/restore_customcertpwt_stepslib.php');

/**
 * The class definition for assigning tasks that provide the settings and steps to perform a restore of the activity.
 *
 * @package    mod_customcertpwt
 * @copyright  2021 PowerTrain Inc
 * @license    All Rights Reserved
 */
class restore_customcertpwt_activity_task extends restore_activity_task {

    /**
     * Define  particular settings this activity can have.
     */
    protected function define_my_settings() {
        // No particular settings for this activity.
    }

    /**
     * Define particular steps this activity can have.
     */
    protected function define_my_steps() {
        // The customcertpwt only has one structure step.
        $this->add_step(new restore_customcertpwt_activity_structure_step('customcertpwt_structure', 'customcertpwt.xml'));
    }

    /**
     * Define the contents in the activity that must be processed by the link decoder.
     */
    public static function define_decode_contents() {
        $contents = array();

        $contents[] = new restore_decode_content('customcertpwt', array('intro'), 'customcertpwt');

        return $contents;
    }

    /**
     * Define the decoding rules for links belonging to the activity to be executed by the link decoder.
     */
    public static function define_decode_rules() {
        $rules = array();

        $rules[] = new restore_decode_rule('CUSTOMCERTPWTVIEWBYID', '/mod/customcertpwt/view.php?id=$1', 'course_module');
        $rules[] = new restore_decode_rule('CUSTOMCERTPWTINDEX', '/mod/customcertpwt/index.php?id=$1', 'course');

        return $rules;

    }

    /**
     * Define the restore log rules that will be applied by the {@see restore_logs_processor} when restoring
     * customcertpwt logs. It must return one array of {@see restore_log_rule} objects.
     *
     * @return array the restore log rules
     */
    public static function define_restore_log_rules() {
        $rules = array();

        $rules[] = new restore_log_rule('customcertpwt', 'add', 'view.php?id={course_module}', '{customcertpwt}');
        $rules[] = new restore_log_rule('customcertpwt', 'update', 'view.php?id={course_module}', '{customcertpwt}');
        $rules[] = new restore_log_rule('customcertpwt', 'view', 'view.php?id={course_module}', '{customcertpwt}');
        $rules[] = new restore_log_rule('customcertpwt', 'received', 'view.php?id={course_module}', '{customcertpwt}');
        $rules[] = new restore_log_rule('customcertpwt', 'view report', 'view.php?id={course_module}', '{customcertpwt}');

        return $rules;
    }

    /**
     * This function is called after all the activities in the backup have been restored. This allows us to get
     * the new course module ids, as they may have been restored after the customcertpwt module, meaning no id
     * was available at the time.
     */
    public function after_restore() {
        global $DB;

        // Get the customcertpwt elements.
        $sql = "SELECT e.*
                  FROM {customcertpwt_elements} e
            INNER JOIN {customcertpwt_pages} p
                    ON e.pageid = p.id
            INNER JOIN {customcertpwt} c
                    ON p.templateid = c.templateid
                 WHERE c.id = :customcertpwtid";
        if ($elements = $DB->get_records_sql($sql, array('customcertpwtid' => $this->get_activityid()))) {
            // Go through the elements for the certificate.
            foreach ($elements as $e) {
                // Get an instance of the element class.
                if ($e = \mod_customcertpwt\element_factory::get_element_instance($e)) {
                    $e->after_restore($this);
                }
            }
        }
    }
}
