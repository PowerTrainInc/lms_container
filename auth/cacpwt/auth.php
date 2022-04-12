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
 * PowerTrain CAC Plugin Auth File
 *
 * @package   auth_cacpwt
 * @copyright 2021 PowerTrain Inc {@link https://powertrain.com}
 * @license   All Rights Reserved
 */

use auth_cacpwt\auth;
use core\session\manager;

defined('MOODLE_INTERNAL') || die;

global $CFG;

require_once($CFG->libdir . '/authlib.php');
require_once($CFG->dirroot . '/auth/cacpwt/classes/register.php');
require_once($CFG->dirroot . '/auth/cacpwt/classes/noaccess.php');
require_once($CFG->dirroot . '/cohort/lib.php');

/**
 * Plugin for cacpwt authentication.
 */
class auth_plugin_cacpwt extends auth {

    /**
     * @var string $cacpwtusername Contains username built from CAC information.
     */
    public $cacpwtusername = null;

    /**
     * @var string $cacid Contains the CAC ID built from CAC information.
     */
    public $cacid = null;

    /**
     * @var string[] User field that get sent to the user creation function.
     */
    public $userfields = ['firstname' => 'firstname', 'lastname' => 'lastname', 'email' => 'email', 'timezone'];

    /**
     * Class constructor.
     */
    public function __construct() {
        $this->authtype = 'cacpwt';

        $dodidfield = $this->get_dodid_field_name();
        $this->userfields['profile_field_' . $dodidfield] = 'profile_field_' . $dodidfield;

        $auditfield = $this->get_audit_field_name();

        if ($auditfield && $auditfield != get_string('none', 'auth_cacpwt')) {
            $this->userfields['profile_field_' . $auditfield] = 'profile_field_' . $auditfield;
        }
    }

    /**
     * Do not allow any login.
     *
     * @param string $username
     * @param string $password
     * @return bool
     */
    public function user_login($username, $password): bool {
        return false;
    }

    /**
     * Get headers from Moodle or $_SERVER.
     *
     * @return array
     * @throws dml_exception
     */
    private function get_all_cacpwt_headers(): array {
        $settings = get_config('auth_cacpwt');

        $headers = getallheaders();
        $returnheaders = array(
            'SSL_CLIENT_S_DN' => '',
            'SSL_CLIENT_I_DN' => '',
            'SSL_CLIENT_VERIFY' => '',
            'SSL_CLIENT_SAN_Email_0' => ''
        );
        $headersmatch = false;

        // Check the Moodle method first for obtaining headers.
        // A header option of 1 indicates Moodle.
        if ($settings->headeroption == "1" && isset($headers[$settings->moodle_SSL_CLIENT_S_DN])) {
            $returnheaders['SSL_CLIENT_S_DN'] = $headers[$settings->moodle_SSL_CLIENT_S_DN];

            // This header format appears to exist.  We will use this method.
            $headersmatch = true;
        }

        if ($headersmatch === true && isset($headers[$settings->moodle_SSL_CLIENT_I_DN])) {
            $returnheaders['SSL_CLIENT_I_DN'] = $headers[$settings->moodle_SSL_CLIENT_I_DN];
        }

        if ($headersmatch === true && isset($headers[$settings->moodle_SSL_CLIENT_SAN_Email_0])) {
            $returnheaders['SSL_CLIENT_SAN_Email_0'] = $headers[$settings->moodle_SSL_CLIENT_SAN_Email_0];
        }

        if ($headersmatch === true && isset($headers[$settings->moodle_SSL_CLIENT_VERIFY])) {
            $returnheaders['SSL_CLIENT_VERIFY'] = $headers[$settings->moodle_SSL_CLIENT_VERIFY];
        }

        // If Moodle method failed -- Get $_SERVER values.
        // A header option of 0 indicates PHP.
        if ($settings->headeroption == "0" && $headersmatch === false) {
            if (isset($_SERVER[$settings->php_SSL_CLIENT_S_DN])) {
                $returnheaders['SSL_CLIENT_S_DN'] = $_SERVER[$settings->php_SSL_CLIENT_S_DN];
                $headersmatch = true;
            }

            if ($headersmatch === true && isset($_SERVER[$settings->php_SSL_CLIENT_I_DN])) {
                $returnheaders['SSL_CLIENT_I_DN'] = $_SERVER[$settings->php_SSL_CLIENT_I_DN];
            }

            // Phpinfo did not show SSL_CLIENT_SAN_Email_0 -- maybe this needs to be changed in a real environment.
            if ($headersmatch === true && isset($_SERVER[$settings->php_SSL_CLIENT_SAN_Email_0])) {
                $returnheaders['SSL_CLIENT_SAN_Email_0'] = $_SERVER[$settings->php_SSL_CLIENT_SAN_Email_0];
            }

            if ($headersmatch === true && isset($_SERVER[$settings->php_SSL_CLIENT_VERIFY])) {
                $returnheaders['SSL_CLIENT_VERIFY'] = $_SERVER[$settings->php_SSL_CLIENT_VERIFY];
            }
        }

        // Check if site is in test mode. This is useful for cases where PowerTrain CAC is tested in a local environment.
        if ($settings->testmode == '1') {
            $returnheaders['SSL_CLIENT_VERIFY'] = 'SUCCESS';
        }

        unset($settings, $headersmatch, $headers);

        return $returnheaders;
    }

    /**
     * Login hook functionality for authentication method.
     */
    public function loginpage_hook(): void {
        global $DB, $CFG, $SESSION;

        $settings = get_config('auth_cacpwt');

        if (!isloggedin()) {
            try {
                $headers = $this->get_all_cacpwt_headers();

                $sslclientsdn = $headers['SSL_CLIENT_S_DN'];
                $sslclientidn = $headers['SSL_CLIENT_I_DN'];
                $sslclientemail = $headers['SSL_CLIENT_SAN_Email_0'];
                $sslsuccessline = $headers['SSL_CLIENT_VERIFY'];

                $proxycertdataparser = new auth();
                $proxycertdataparser->proxy_cert_data_parser($sslclientsdn, $sslclientidn, $sslclientemail,
                    $sslsuccessline);

                if ($proxycertdataparser->is_cert_valid()) {
                    // Get username from CAC first initial and full last name.
                    $this->cacpwtusername = $proxycertdataparser->cacpwtusername;

                    // Get cacid for later use.
                    $this->cacid = $proxycertdataparser->get_cert_user_dodid();

                    // Check if ID source is standard profile field (0) or custom profile field (1).
                    if ($settings->data_source == '0') {
                        $user = $DB->get_record('user', ['idnumber' => $proxycertdataparser->get_cert_user_dodid()]);
                    } else {
                        if ($DB->record_exists('user_info_field', array('shortname' => 'cacid'))) {
                            $sql = '
                                SELECT u.*
                                FROM {user} u,
                                    {user_info_data} uid,
                                    {user_info_field} uif
                                WHERE u.id = uid.userid
                                    AND uid.fieldid = uif.id
                                    AND uid.data = :cacid
                                    AND uif.shortname = \'cacid\'
                                    AND u.deleted = \'0\'
                            ';

                            $user = $DB->get_record_sql($sql, array('cacid' => $proxycertdataparser->get_cert_user_dodid()));
                        }
                    }

                    if ((!empty($user)) && ($user->confirmed)) {
                        if ($user->suspended == 0) {
                            complete_user_login($user);

                            // Use this method to record that user logged in with CAC.
                            $time = time();
                            $SESSION->cacpwt_code = md5($time);
                            $SESSION->cacpwt_time = $time;

                            redirect($CFG->wwwroot . '/my/');
                        } else {

                            redirect($CFG->wwwroot . '/auth/cacpwt/restricted.php');
                        }
                    } else {
                        // Capture CAC information and proceed to register page.
                        $signupsecurity = array();

                        $signupsecurity['REMOTE_ADDR'] = $_SERVER['REMOTE_ADDR'];
                        $signupsecurity['HTTP_USER_AGENT'] = $_SERVER['HTTP_USER_AGENT'];

                        foreach ($_SERVER as $key => $val) {
                            if (substr($key, 0, 11) == 'SSL_CLIENT_') {
                                $signupsecurity[$key] = $val;
                            }
                        }

                        unset($key, $val);

                        $this->register_prompt($signupsecurity, $proxycertdataparser->get_cert_user_dodid());
                    }
                } else {
                    // Need to add logging capability here.
                    $logmessage = 'cacpwt_loginpage_hook - proxycertdataparser - is_cert_valid is false';
                }
            } catch (moodle_exception $exception) {
                debugging('Caught ' . $exception);
            }
        } else {
            require_logout();
        }
    }

    /**
     * Logout hook functionality for authentication method.
     *
     * @throws dml_exception|moodle_exception
     */
    public function logoutpage_hook(): void {
        global $CFG, $SESSION;

        // Make sure we are using this authentication method.
        $isthisauth = (isset($SESSION->cacpwt_code) && isset($SESSION->cacpwt_time));

        if ($isthisauth && $SESSION->cacpwt_code == md5($SESSION->cacpwt_time)) {
            $settings = get_config('auth_cacpwt');

            require_logout();

            // If alt method is active and alt url is defined.
            if ($settings->logout_alt_enabled == 1 && $settings->logout_alt_url != '') {
                $url = new moodle_url($settings->logout_alt_url);
            } else {
                // No alt url defined -- redirect to home.
                $url = new moodle_url($CFG->wwwroot);
            }

            redirect($url);
        }
    }

    /**
     * No password updates.
     *
     * @param string $user
     * @param string $newpassword
     * @return bool
     */
    public function user_update_password($user, $newpassword): bool {
        return false;
    }

    /**
     * Prevent local passwords.
     *
     * @return bool
     */
    public function prevent_local_passwords(): bool {
        // Just in case, we do not want to lose the passwords.
        return false;
    }

    /**
     * No external data sync.
     *
     * @return bool
     */
    public function is_internal(): bool {
        // We do not know if it was internal or external originally.
        return true;
    }

    /**
     * No changing of password.
     *
     * @return bool
     */
    public function can_change_password(): bool {
        return false;
    }

    /**
     * No password resetting.
     *
     * @return bool
     */
    public function can_reset_password(): bool {
        return false;
    }

    /**
     * Returns true if plugin can be manually set.
     *
     * @return bool
     */
    public function can_be_manually_set(): bool {
        return false;
    }

    /**
     * Returns information on how the specified user can change their password.
     * User accounts with authentication type set to nologin are disabled accounts.
     * They cannot change their password.
     *
     * @param stdClass $user A user object
     * @return array An array of strings with keys subject and message
     * @throws coding_exception
     * @throws moodle_exception
     */
    public function get_password_change_info(stdClass $user): array {
        $site = get_site();
        $site->fullname = format_string($site->fullname);

        $data = new stdClass();
        $data->firstname = $user->firstname;
        $data->lastname  = $user->lastname;
        $data->username  = $user->username;
        $data->sitename  = $site->fullname;
        $data->admin     = generate_email_signoff();

        $message = get_string('emailpasswordchangeinfodisabled', '', $data);
        $subject = get_string('emailpasswordchangeinfosubject', '', $site->fullname);

        return array(
            'subject' => $subject,
            'message' => $message
        );
    }

    /**
     * Read user information from external database and returns it as array().
     *
     * @param string $username username
     * @return array Array with no magic quotes or false on error
     * @throws dml_exception|coding_exception
     */
    public function get_userinfo($username): array {
        global $DB;

        $userinfo = array();

        if ($result = $DB->get_record('auth_cacpwt', array('username' => $username))) {
            $userinfo['username'] = $username;
            $userinfo['firstname'] = $result->firstname;
            $userinfo['lastname'] = $result->lastname;
            $userinfo['email'] = $result->email;
            $userinfo['timezone'] = get_string('timezone', 'auth_cacpwt');

            $dodidfield = $this->get_dodid_field_name();
            $auditfield = $this->get_audit_field_name();

            if ($dodidfield) {
                $userinfo['profile_field_' . $dodidfield] = $result->dodid;
            }

            if ($auditfield && $auditfield != get_string('none', 'auth_cacpwt')) {
                $userinfo['profile_field_' . $auditfield] = $result->audit;
            }

            $DB->delete_records('auth_cacpwt', array('username' => $username));

            unset($dodidfield, $auditfield);
        }

        unset($result);

        return $userinfo;
    }

    /**
     * Get field name of profile field containing the DOD ID in the user profile.
     * @return bool|string
     * @throws dml_exception
     */
    private function get_dodid_field_name() {
        global $DB;

        $settings = get_config('auth_cacpwt');

        $dodidfield = null;

        $customrecord = $settings->data_source + 1;

        if ($result = $DB->get_record('user_info_field', array('id' => $customrecord), 'shortname')) {
            $dodidfield = $result->shortname;
        } else {
            $dodidfield = false;
        }

        unset($customrecord, $settings, $result);

        return $dodidfield;
    }

    /**
     * Get audit custom profile field name.
     *
     * @return bool|string
     * @throws dml_exception
     */
    private function get_audit_field_name() {
        if ($settings = get_config('auth_cacpwt')) {
            $return = $settings->audit_field;
        } else {
            $return = false;
        }

        return $return;
    }

    /**
     * CAC Registration Page
     *
     * @param array $signupsecurity
     * @param string $cacid
     * @throws coding_exception
     * @throws dml_exception
     * @throws moodle_exception
     */
    private function register_prompt(array $signupsecurity, string $cacid) {
        global $PAGE, $OUTPUT, $SESSION, $DB;

        $temphash = null;
        $username = null;

        if (isset($SESSION->cacpwt_sessionhash)) {
            $temphash = $SESSION->cacpwt_sessionhash;
        }

        // Set import batch to work from.
        $batch = 'auth_cacpwt_import_' . get_config('auth_cacpwt', 'batch');

        // If no matching DODID found, display a no access page with instructions.
        if (!$DB->record_exists($batch, array('dodid' => $cacid))) {
            // The following content needs to be revised/updated for no access content.
            $mform = new cacpwt_noaccess();

            $PAGE->set_url('/login/index.php');
            $PAGE->set_context(context_system::instance());
            $PAGE->set_title(get_string('page_title', 'auth_cacpwt'));
            $PAGE->set_pagelayout('standard');
            $PAGE->set_heading(get_string('page_heading', 'auth_cacpwt'));

            echo $OUTPUT->header();
            echo $OUTPUT->box_start('generalbox centerpara boxwidthnormal boxaligncenter');

            // Display registration form.
            echo '<div id="cacpwt_form">';
            $mform->display();
            echo '</div>';

            echo $OUTPUT->box_end();
            echo $OUTPUT->footer();
            exit;
        } else {
            $mform = new cacpwt_register();

            if ($data = $mform->get_data()) {
                // Add transaction support to prevent partial registrations.
                $transaction = $DB->start_delegated_transaction();

                $validmd5 = false;

                if (preg_match('/^[a-f0-9]{32}$/i', $data->registerid)) {
                    if ($data->registerid == $temphash) {
                        $validmd5 = true;
                        unset($SESSION->cacpwt_sessionhash, $SESSION->cacpwt_registration, $SESSION->cacpwt_temp_sessionhash);
                    }
                }

                if ($validmd5 !== true) {
                    // We have a bad hash.  Sent directly to the login/register page to try again.
                    redirect(new moodle_url('/login/index.php'));
                } else {
                    // Registration success.
                    $dodidfield = $this->get_dodid_field_name();
                    $auditfield = $this->get_audit_field_name();

                    $looping = true;

                    // Loop until we find a username that doesn't exist.  If this times out, play the lottery.
                    while ($looping) {
                        $username = $this->cacpwtusername . str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);

                        if (!core_user::get_user_by_username($username)) {
                            $looping = false;
                        }
                    }

                    unset($looping);

                    // Capture audit information.
                    ob_start();
                    var_dump($signupsecurity);
                    $audit = ob_get_contents();
                    ob_end_clean();

                    // Get import batch table info.
                    $batch = get_config('auth_cacpwt', 'batch');
                    $importtable = 'auth_cacpwt_import_' . $batch;

                    unset($batch);

                    // Get info to determine if users needs a suspended account.
                    $record = $DB->get_record($importtable, array('dodid' => $this->cacid));

                    if ($record->auth_required == 1 || $record->bypass_dmdc == 1) {
                        $suspended = 1;
                    } else {
                        $suspended = 0;
                    }

                    // Write temporary record to plugin table.
                    $insert = new stdClass;
                    $insert->username = $username;
                    $insert->email = $data->email;
                    $insert->firstname = $data->firstname;
                    $insert->lastname = $data->lastname;
                    $insert->suspended = $suspended;

                    // If we have a DOD ID field to map to, pass the CAC ID.  We must have it but just in case.
                    if ($dodidfield) {
                        $insert->dodid = $this->cacid;
                    }

                    // We should also have an audit field. But just in case we'll test for it.
                    if ($auditfield && $auditfield != get_string('none', 'auth_cacpwt')) {
                        $insert->audit = $audit;
                    } else {
                        $insert->audit = '';
                    }

                    $DB->insert_record('auth_cacpwt', $insert);

                    unset($insert);

                    $user = create_user_record($username, md5(rand(0, 9999999999) . time()), 'cacpwt');

                    // Suspend restricted user.
                    if ($suspended == 1) {
                        $user->suspended = 1;

                        // Force logout.
                        manager::kill_user_sessions($user->id);
                        user_update_user($user, false);
                    }

                    // This shouldn't fail, but if it does -- default to safe restricted cohort.
                    if (!$record) {
                        $cohort = get_config('auth_cacpwt', 'restricted_cohort');
                    } else {
                        // Get cohort id from plugin based on access rules.
                        if ($suspended == 1) {
                            $cohort = get_config('auth_cacpwt', 'restricted_cohort');
                        } else {
                            $cohort = get_config('auth_cacpwt', 'unrestricted_cohort');
                        }
                    }

                    // Add user to correct cohort.
                    cohort_add_member($cohort, $user->id);

                    unset($cohort, $record, $importtable, $suspended);
                }

                // Commit transaction once we've reached the end.
                $transaction->allow_commit();

                // Redirect after account creation to trigger automatic login of new CAC user.
                redirect(new moodle_url('/login/index.php'));
            } else {
                // This isn't a submission -- make sure the registration session variable is gone.
                if (!$data && !isset($SESSION->cacpwt_temp_sessionhash)) {
                    unset($SESSION->cacpwt_registration);
                }

                // Pass forward a session hash from a failed submission.
                if (isset($SESSION->cacpwt_temp_sessionhash) && preg_match('/^[a-f0-9]{32}$/i',
                        $SESSION->cacpwt_temp_sessionhash)) {
                    $SESSION->cacpwt_sessionhash = $SESSION->cacpwt_temp_sessionhash;

                    unset($SESSION->cacpwt_temp_sessionhash);
                }

                $PAGE->set_url('/login/index.php');
                $PAGE->set_context(context_system::instance());
                $PAGE->set_title(get_string('page_title', 'auth_cacpwt'));
                $PAGE->set_pagelayout('standard');
                $PAGE->set_heading(get_string('page_heading', 'auth_cacpwt'));

                echo $OUTPUT->header();
                echo $OUTPUT->box_start('generalbox centerpara boxwidthnormal boxaligncenter');

                // Display registration form.
                echo '<div id="cacpwt_form">';
                $mform->display();
                echo '</div>';

                echo $OUTPUT->box_end();
                echo $OUTPUT->footer();
                exit;
            }
        }
    }
}
