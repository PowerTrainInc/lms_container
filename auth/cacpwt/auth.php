<?php
/**
 * PowerTrain CAC Plugin Auth File
 *
 * @package   auth_cacpwt
 * @copyright 2021 PowerTrain Inc {@link https://powertrain.com}
 * @license   All Rights Reserved
 */

use auth_cacpwt\auth;

defined('MOODLE_INTERNAL') || die();

/**
 * @var $CFG stdClass Config object
 */

require_once($CFG->libdir . '/authlib.php');

/**
 * Plugin for cacpwt authentication.
 */
class auth_plugin_cacpwt extends auth {
	public function __construct() {
		$this->authtype = 'cacpwt';
	}

	/**
	 * Do not allow any login.
	 *
	 * @param $username
	 * @param $password
	 * @return bool
	 */
	function user_login($username, $password): bool {
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
		$return_headers = array(
			'SSL_CLIENT_S_DN' => '',
			'SSL_CLIENT_I_DN' => '',
			'SSL_CLIENT_VERIFY' => '',
			'SSL_CLIENT_SAN_Email_0' => ''
		);
		$headers_match = false;

		// Check the Moodle method first for obtaining headers
		// A header option of 1 indicates Moodle
		if ($settings->headeroption == "1" && isset($headers[$settings->moodle_SSL_CLIENT_S_DN])) {
			$return_headers['SSL_CLIENT_S_DN'] = $headers[$settings->moodle_SSL_CLIENT_S_DN];

			// This header format appears to exist.  We will use this method
			$headers_match = true;
		}

		if ($headers_match === true && isset($headers[$settings->moodle_SSL_CLIENT_I_DN])) {
			$return_headers['SSL_CLIENT_I_DN'] = $headers[$settings->moodle_SSL_CLIENT_I_DN];
		}

		if ($headers_match === true && isset($headers[$settings->moodle_SSL_CLIENT_SAN_Email_0])) {
			$return_headers['SSL_CLIENT_SAN_Email_0'] = $headers[$settings->moodle_SSL_CLIENT_SAN_Email_0];
		}

		if ($headers_match === true && isset($headers[$settings->moodle_SSL_CLIENT_VERIFY])) {
			$return_headers['SSL_CLIENT_VERIFY'] = $headers[$settings->moodle_SSL_CLIENT_VERIFY];
		}

		// If Moodle method failed -- Get $_SERVER values
		// A header option of 0 indicates PHP
		if ($settings->headeroption == "0" && $headers_match === false) {
			if (isset($_SERVER[$settings->php_SSL_CLIENT_S_DN])) {
				$return_headers['SSL_CLIENT_S_DN'] = $_SERVER[$settings->php_SSL_CLIENT_S_DN];
				$headers_match = true;
			}

			if ($headers_match === true && isset($_SERVER[$settings->php_SSL_CLIENT_I_DN])) {
				$return_headers['SSL_CLIENT_I_DN'] = $_SERVER[$settings->php_SSL_CLIENT_I_DN];
			}

			// phpinfo did not show SSL_CLIENT_SAN_Email_0 -- maybe this needs to be changed in a real environment.
			if ($headers_match === true && isset($_SERVER[$settings->php_SSL_CLIENT_SAN_Email_0])) {
				$return_headers['SSL_CLIENT_SAN_Email_0'] = $_SERVER[$settings->php_SSL_CLIENT_SAN_Email_0];
			}

			if ($headers_match === true && isset($_SERVER[$settings->php_SSL_CLIENT_VERIFY])) {
				$return_headers['SSL_CLIENT_VERIFY'] = $_SERVER[$settings->php_SSL_CLIENT_VERIFY];
			}
		}

		// Check if site is in test mode. This is useful for cases where PowerTrain CAC is tested in a local environment
		if ($settings->testmode == '1') {
			$return_headers['SSL_CLIENT_VERIFY'] = 'SUCCESS';
		}

		unset($settings, $headers_match, $headers);

		return $return_headers;
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

				$SSL_CLIENT_S_DN = $headers['SSL_CLIENT_S_DN'];
				$SSL_CLIENT_I_DN = $headers['SSL_CLIENT_I_DN'];
				$SSL_CLIENT_EMAIL = $headers['SSL_CLIENT_SAN_Email_0'];
				$SSL_SUCCESS_LINE = $headers['SSL_CLIENT_VERIFY'];

				$proxyCertDataParser = new auth();
				$proxyCertDataParser->proxyCertDataParser($SSL_CLIENT_S_DN, $SSL_CLIENT_I_DN, $SSL_CLIENT_EMAIL,
					$SSL_SUCCESS_LINE);

				if ($proxyCertDataParser->isCertValid()) {
					// Check if ID source is standard profile field (0) or custom profile field (1)
					if ($settings->data_source == '0') {
						$user = $DB->get_record('user', ['idnumber' => $proxyCertDataParser->getCertUserDODID()]);
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
									AND u.suspended = \'0\'
									AND u.deleted = \'0\'
							';

							$user = $DB->get_record_sql($sql, array('cacid' => $proxyCertDataParser->getCertUserDODID()));
						}
					}

					if ((!empty($user)) && ($user->confirmed)) {
						complete_user_login($user);

						// Use this method to record that user logged in with CAC
						$time = time();
						$SESSION->cacpwt_code = md5($time);
						$SESSION->cacpwt_time = $time;

						redirect($CFG->wwwroot . '/my/');
					} else {
						error_log('cacpwt_loginpage_hook - either empty user or user not confirmed');
					}
				} else {
					error_log('cacpwt_loginpage_hook - proxyCertDataParser - isCertValid is false');
				}
			} catch (moodle_exception $exception) {
				error_log('Caught ' . $exception);
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

		// Make sure we are using this authentication method
		$is_this_auth = (isset($SESSION->cacpwt_code) && isset($SESSION->cacpwt_time));

		if ($is_this_auth && $SESSION->cacpwt_code == md5($SESSION->cacpwt_time)) {
			$settings = get_config('auth_cacpwt');

			require_logout();

			// If alt method is active and alt url is defined
			if ($settings->logout_alt_enabled == 1 && $settings->logout_alt_url != '') {
				$url = new moodle_url($settings->logout_alt_url);
			} else {
				// No alt url defined -- redirect to home
				$url = new moodle_url($CFG->wwwroot);
			}

			redirect($url);
		}
	}

	/**
	 * No password updates.
	 *
	 * @param $user
	 * @param $newpassword
	 * @return bool
	 */
	function user_update_password($user, $newpassword): bool {
		return false;
	}

	/**
	 * Prevent local passwords.
	 *
	 * @return bool
	 */
	function prevent_local_passwords(): bool {
		// just in case, we do not want to lose the passwords
		return false;
	}

	/**
	 * No external data sync.
	 *
	 * @return bool
	 */
	function is_internal(): bool {
		// We do not know if it was internal or external originally
		return true;
	}

	/**
	 * No changing of password.
	 *
	 * @return bool
	 */
	function can_change_password(): bool {
		return false;
	}

	/**
	 * No password resetting.
	 *
	 * @return bool
	 */
	function can_reset_password(): bool {
		return false;
	}

	/**
	 * Returns true if plugin can be manually set.
	 *
	 * @return bool
	 */
	function can_be_manually_set(): bool {
		return true;
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
		$data->admin	 = generate_email_signoff();

		$message = get_string('emailpasswordchangeinfodisabled', '', $data);
		$subject = get_string('emailpasswordchangeinfosubject', '', $site->fullname);

		return array(
			'subject' => $subject,
			'message' => $message
		);
	}
}
