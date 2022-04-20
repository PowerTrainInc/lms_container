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
 * This file contains the backup tasks that provides all the settings and steps to perform a backup of the activity.
 *
 * @package    mod_customcertpwt
 * @copyright  2021 PowerTrain Inc
 * @license    All Rights Reserved
 */

defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');

require_once($CFG->dirroot . '/mod/customcertpwt/backup/moodle2/backup_customcertpwt_stepslib.php');

/**
 * Handles creating tasks to peform in order to create the backup.
 *
 * @package    mod_customcertpwt
 * @copyright  2021 PowerTrain Inc
 * @license    All Rights Reserved
 */
class backup_customcertpwt_activity_task extends backup_activity_task {

    /**
     * Define particular settings this activity can have.
     */
    protected function define_my_settings() {
        // No particular settings for this activity.
    }

    /**
     * Define particular steps this activity can have.
     */
    protected function define_my_steps() {
        // The customcertpwt only has one structure step.
        $this->add_step(new backup_customcertpwt_activity_structure_step('customcertpwt_structure', 'customcertpwt.xml'));
    }

    /**
     * Code the transformations to perform in the activity in order to get transportable (encoded) links.
     *
     * @param string $content
     * @return mixed|string
     */
    public static function encode_content_links($content) {
        global $CFG;

        $base = preg_quote($CFG->wwwroot, "/");

        // Link to the list of customcertpwts.
        $search = "/(".$base."\/mod\/customcertpwt\/index.php\?id\=)([0-9]+)/";
        $content = preg_replace($search, '$@customcertpwtINDEX*$2@$', $content);

        // Link to customcertpwt view by moduleid.
        $search = "/(".$base."\/mod\/customcertpwt\/view.php\?id\=)([0-9]+)/";
        $content = preg_replace($search, '$@customcertpwtVIEWBYID*$2@$', $content);

        return $content;
    }
}
