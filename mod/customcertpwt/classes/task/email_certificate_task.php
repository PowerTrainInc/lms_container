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
 * A scheduled task for emailing certificates.
 *
 * @package    mod_customcertpwt
 * @copyright  2021 PowerTrain Inc
 * @license    All Rights Reserved
 */
namespace mod_customcertpwt\task;

defined('MOODLE_INTERNAL') || die();

/**
 * A scheduled task for emailing certificates.
 *
 * @package    mod_customcertpwt
 * @copyright  2021 PowerTrain Inc
 * @license    All rights Reserved
 */
class email_certificate_task extends \core\task\scheduled_task {

    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name() {
        return get_string('taskemailcertificate', 'customcertpwt');
    }

    /**
     * Execute.
     */
    public function execute() {
        global $DB, $PAGE;

        // Get all the certificates that have requested someone get emailed.
        $emailotherslengthsql = $DB->sql_length('c.emailothers');
        $sql = "SELECT c.*, ct.id as templateid, ct.name as templatename, ct.contextid, co.id as courseid,
                       co.fullname as coursefullname, co.shortname as courseshortname
                  FROM {customcertpwt} c
                  JOIN {customcertpwt_templates} ct
                    ON c.templateid = ct.id
                  JOIN {course} co
                    ON c.course = co.id
                 WHERE (c.emailstudents = :emailstudents
                        OR c.emailteachers = :emailteachers
                        OR $emailotherslengthsql >= 3)";
        if (!$customcertpwts = $DB->get_records_sql($sql, array('emailstudents' => 1, 'emailteachers' => 1))) {
            return;
        }

        // The renderers used for sending emails.
        $htmlrenderer = $PAGE->get_renderer('mod_customcertpwt', 'email', 'htmlemail');
        $textrenderer = $PAGE->get_renderer('mod_customcertpwt', 'email', 'textemail');
        foreach ($customcertpwts as $customcertpwt) {
            // Do not process an empty certificate.
            $sql = "SELECT ce.*
                      FROM {customcertpwt_elements} ce
                      JOIN {customcertpwt_pages} cp
                        ON cp.id = ce.pageid
                      JOIN {customcertpwt_templates} ct
                        ON ct.id = cp.templateid
                     WHERE ct.contextid = :contextid";
            if (!$DB->record_exists_sql($sql, ['contextid' => $customcertpwt->contextid])) {
                continue;
            }

            // Get the context.
            $context = \context::instance_by_id($customcertpwt->contextid);

            // Set the $PAGE context - this ensure settings, such as language, are kept and don't default to the site settings.
            $PAGE->set_context($context);

            // Get the person we are going to send this email on behalf of.
            $userfrom = \core_user::get_noreply_user();

            // Store teachers for later.
            $teachers = get_enrolled_users($context, 'moodle/course:update');

            $courseshortname = format_string($customcertpwt->courseshortname, true, array('context' => $context));
            $coursefullname = format_string($customcertpwt->coursefullname, true, array('context' => $context));
            $certificatename = format_string($customcertpwt->name, true, array('context' => $context));

            // Used to create the email subject.
            $info = new \stdClass;
            $info->coursename = $courseshortname; // Added for BC, so users who have edited the string don't lose this value.
            $info->courseshortname = $courseshortname;
            $info->coursefullname = $coursefullname;
            $info->certificatename = $certificatename;

            // Get a list of all the issues.
            $userfields = get_all_user_name_fields(true, 'u');
            $sql = "SELECT u.id, u.username, $userfields, u.email, ci.id as issueid, ci.emailed
                      FROM {customcertpwt_issues} ci
                      JOIN {user} u
                        ON ci.userid = u.id
                     WHERE ci.customcertpwtid = :customcertpwtid";
            $issuedusers = $DB->get_records_sql($sql, array('customcertpwtid' => $customcertpwt->id));

            // Now, get a list of users who can access the certificate but have not yet.
            $enrolledusers = get_enrolled_users(\context_course::instance($customcertpwt->courseid), 'mod/customcertpwt:view');
            foreach ($enrolledusers as $enroluser) {
                // Check if the user has already been issued.
                if (in_array($enroluser->id, array_keys((array) $issuedusers))) {
                    continue;
                }

                // Now check if the certificate is not visible to the current user.
                $cm = get_fast_modinfo($customcertpwt->courseid, $enroluser->id)->instances['customcertpwt'][$customcertpwt->id];
                if (!$cm->uservisible) {
                    continue;
                }

                // Don't want to email those with the capability to manage the certificate.
                if (has_capability('mod/customcertpwt:manage', $context, $enroluser->id)) {
                    continue;
                }

                // Only email those with the capability to receive the certificate.
                if (!has_capability('mod/customcertpwt:receiveissue', $context, $enroluser->id)) {
                    continue;
                }

                // Check that they have passed the required time.
                if (!empty($customcertpwt->requiredtime)) {
                    if (\mod_customcertpwt\certificate::get_course_time($customcertpwt->courseid,
                            $enroluser->id) < ($customcertpwt->requiredtime * 60)) {
                        continue;
                    }
                }

                // Ensure the cert hasn't already been issued, e.g via the UI (view.php) - a race condition.
                $issueid = $DB->get_field('customcertpwt_issues', 'id',
                    array('userid' => $enroluser->id, 'customcertpwtid' => $customcertpwt->id), IGNORE_MULTIPLE);
                if (empty($issueid)) {
                    // Ok, issue them the certificate.
                    $issueid = \mod_customcertpwt\certificate::issue_certificate($customcertpwt->id, $enroluser->id);
                }

                // Add them to the array so we email them.
                $enroluser->issueid = $issueid;
                $enroluser->emailed = 0;
                $issuedusers[] = $enroluser;
            }

            // Remove all the users who have already been emailed.
            foreach ($issuedusers as $key => $issueduser) {
                if ($issueduser->emailed) {
                    unset($issuedusers[$key]);
                }
            }

            // If there are no users to email we can return early.
            if (!$issuedusers) {
                continue;
            }

            // Create a directory to store the PDF we will be sending.
            $tempdir = make_temp_directory('certificate/attachment');
            if (!$tempdir) {
                return;
            }

            // Now, email the people we need to.
            foreach ($issuedusers as $user) {
                // Set up the user.
                cron_setup_user($user);

                $userfullname = fullname($user);
                $info->userfullname = $userfullname;

                // Now, get the PDF.
                $template = new \stdClass();
                $template->id = $customcertpwt->templateid;
                $template->name = $customcertpwt->templatename;
                $template->contextid = $customcertpwt->contextid;
                $template = new \mod_customcertpwt\template($template);
                $filecontents = $template->generate_pdf(false, $user->id, true);

                // Set the name of the file we are going to send.
                $filename = $courseshortname . '_' . $certificatename;
                $filename = \core_text::entities_to_utf8($filename);
                $filename = strip_tags($filename);
                $filename = rtrim($filename, '.');
                $filename = str_replace('&', '_', $filename) . '.pdf';

                // Create the file we will be sending.
                $tempfile = $tempdir . '/' . md5(microtime() . $user->id) . '.pdf';
                file_put_contents($tempfile, $filecontents);

                if ($customcertpwt->emailstudents) {
                    $renderable = new \mod_customcertpwt\output\email_certificate(true, $userfullname, $courseshortname,
                        $coursefullname, $certificatename, $customcertpwt->contextid);

                    $subject = get_string('emailstudentsubject', 'customcertpwt', $info);
                    $message = $textrenderer->render($renderable);
                    $messagehtml = $htmlrenderer->render($renderable);
                    email_to_user($user, fullname($userfrom), $subject, $message, $messagehtml, $tempfile, $filename);
                }

                if ($customcertpwt->emailteachers) {
                    $renderable = new \mod_customcertpwt\output\email_certificate(false, $userfullname, $courseshortname,
                        $coursefullname, $certificatename, $customcertpwt->contextid);

                    $subject = get_string('emailnonstudentsubject', 'customcertpwt', $info);
                    $message = $textrenderer->render($renderable);
                    $messagehtml = $htmlrenderer->render($renderable);
                    foreach ($teachers as $teacher) {
                        email_to_user($teacher, fullname($userfrom), $subject, $message, $messagehtml, $tempfile,
                            $filename);
                    }
                }

                if (!empty($customcertpwt->emailothers)) {
                    $others = explode(',', $customcertpwt->emailothers);
                    foreach ($others as $email) {
                        $email = trim($email);
                        if (validate_email($email)) {
                            $renderable = new \mod_customcertpwt\output\email_certificate(false, $userfullname,
                                $courseshortname, $coursefullname, $certificatename, $customcertpwt->contextid);

                            $subject = get_string('emailnonstudentsubject', 'customcertpwt', $info);
                            $message = $textrenderer->render($renderable);
                            $messagehtml = $htmlrenderer->render($renderable);

                            $emailuser = new \stdClass();
                            $emailuser->id = -1;
                            $emailuser->email = $email;
                            email_to_user($emailuser, fullname($userfrom), $subject, $message, $messagehtml, $tempfile,
                                $filename);
                        }
                    }
                }

                // Set the field so that it is emailed.
                $DB->set_field('customcertpwt_issues', 'emailed', 1, array('id' => $user->issueid));
            }
        }
    }
}
