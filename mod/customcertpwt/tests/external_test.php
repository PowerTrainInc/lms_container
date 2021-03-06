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
 * File contains the unit tests for the webservices.
 *
 * @package    mod_customcertpwt
 * @category   test
 * @copyright  2021 PowerTrain Inc
 * @license    All Rights Reserved
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;

/**
 * Unit tests for the webservices.
 *
 * @package    mod_customcertpwt
 * @category   test
 * @copyright  2021 PowerTrain Inc
 * @license    All Rights Reserved
 */
class mod_customcertpwt_external_test_testcase extends advanced_testcase {

    /**
     * Test set up.
     */
    public function setUp() {
        $this->resetAfterTest();
    }

    /**
     * Test the delete_issue web service.
     */
    public function test_delete_issue() {
        global $DB;

        $this->setAdminUser();

        // Create a course.
        $course = $this->getDataGenerator()->create_course();

        // Create a custom certificate in the course.
        $customcertpwt = $this->getDataGenerator()->create_module('customcertpwt', ['course' => $course->id]);

        // Create two users.
        $student1 = $this->getDataGenerator()->create_user();
        $student2 = $this->getDataGenerator()->create_user();

        // Enrol them into the course.
        $this->getDataGenerator()->enrol_user($student1->id, $course->id);
        $this->getDataGenerator()->enrol_user($student2->id, $course->id);

        // Issue them both certificates.
        $i1 = \mod_customcertpwt\certificate::issue_certificate($customcertpwt->id, $student1->id);
        $i2 = \mod_customcertpwt\certificate::issue_certificate($customcertpwt->id, $student2->id);

        $this->assertEquals(2, $DB->count_records('customcertpwt_issues'));

        $result = \mod_customcertpwt\external::delete_issue($customcertpwt->id, $i2);

        // We need to execute the return values cleaning process to simulate the web service server.
        external_api::clean_returnvalue(\mod_customcertpwt\external::delete_issue_returns(), $result);

        $issues = $DB->get_records('customcertpwt_issues');
        $this->assertCount(1, $issues);

        $issue = reset($issues);
        $this->assertEquals($student1->id, $issue->userid);
    }

    /**
     * Test the delete_issue web service.
     */
    public function test_delete_issue_no_login() {
        global $DB;

        // Create a course.
        $course = $this->getDataGenerator()->create_course();

        // Create a custom certificate in the course.
        $customcertpwt = $this->getDataGenerator()->create_module('customcertpwt', ['course' => $course->id]);

        // Create two users.
        $student1 = $this->getDataGenerator()->create_user();
        $student2 = $this->getDataGenerator()->create_user();

        // Enrol them into the course.
        $this->getDataGenerator()->enrol_user($student1->id, $course->id);
        $this->getDataGenerator()->enrol_user($student2->id, $course->id);

        // Issue them both certificates.
        $i1 = \mod_customcertpwt\certificate::issue_certificate($customcertpwt->id, $student1->id);
        $i2 = \mod_customcertpwt\certificate::issue_certificate($customcertpwt->id, $student2->id);

        $this->assertEquals(2, $DB->count_records('customcertpwt_issues'));

        // Try and delete without logging in.
        $this->expectException('require_login_exception');
        \mod_customcertpwt\external::delete_issue($customcertpwt->id, $i2);
    }

    /**
     * Test the delete_issue web service.
     */
    public function test_delete_issue_no_capability() {
        global $DB;

        // Create a course.
        $course = $this->getDataGenerator()->create_course();

        // Create a custom certificate in the course.
        $customcertpwt = $this->getDataGenerator()->create_module('customcertpwt', ['course' => $course->id]);

        // Create two users.
        $student1 = $this->getDataGenerator()->create_user();
        $student2 = $this->getDataGenerator()->create_user();

        $this->setUser($student1);

        // Enrol them into the course.
        $this->getDataGenerator()->enrol_user($student1->id, $course->id);
        $this->getDataGenerator()->enrol_user($student2->id, $course->id);

        // Issue them both certificates.
        $i1 = \mod_customcertpwt\certificate::issue_certificate($customcertpwt->id, $student1->id);
        $i2 = \mod_customcertpwt\certificate::issue_certificate($customcertpwt->id, $student2->id);

        $this->assertEquals(2, $DB->count_records('customcertpwt_issues'));

        // Try and delete without the required capability.
        $this->expectException('required_capability_exception');
        \mod_customcertpwt\external::delete_issue($customcertpwt->id, $i2);
    }
}
