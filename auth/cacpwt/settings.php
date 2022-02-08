<?php
/**
 * PowerTrain CAC Plugin Settings
 *
 * @package   auth_cacpwt
 * @copyright 2021 PowerTrain Inc {@link https://powertrain.com}
 * @license   All Rights Reserved
 */
defined('MOODLE_INTERNAL') || die;

/**
 * @var $ADMIN	  	stdClass Admin object
 * @var $CFG		stdClass Config object
 * @var $settings   stdClass Settings object
 */

global $DB;

if (!function_exists('cacpwt_fieldcheck')) {
	/**
	 * @param string $fieldname
	 * @param string $default
	 * @param int $type
	 * @return string
	 * @throws dml_exception
	 */
	function cacpwt_fieldcheck(string $fieldname, string $default, int $type = 0): string {
		// Get current settings if any
		$current_settings = get_config('auth_cacpwt');
		$headers = getallheaders();
		$field_match = false;

		if (!isset($current_settings->$fieldname)) {
			$settings_defined = false;
		} else {
			$settings_defined = true;
		}

		if ($type == 0) {
			// PHP Headers
			$header_check = $_SERVER;
		} else {
			// Default to Moodle headers. If wrong type was picked, this will fail anyway.
			$header_check = $headers;
		}

		if ($settings_defined && isset($current_settings->$fieldname)) {
			if (isset($header_check[$current_settings->$fieldname])) {
				$field_match = true;
			}
		} elseif (!$settings_defined) {
			if (isset($header_check[$default])) {
				$field_match = true;
			}
		}

		if ($field_match === true) {
			$fa_code = '<i class="fa fa-check" style="color: green;" aria-hidden="true"></i>';
		} else {
			$fa_code = '<i class="fa fa-times" style="color: red;" aria-hidden="true"></i>';
		}

		return $fa_code;
	}
}

if ($ADMIN->fulltree) {
	// Set No/Yes Dropdown Options
	$noyesoptions = array(
		0 => get_string('no'),
		1 => get_string('yes')
	);

	// Set Header Options
	$headeroptions = array(
		0 => get_string('headeroptions:php', 'auth_cacpwt'),
		1 => get_string('headeroptions:moodle', 'auth_cacpwt')
	);

	// Set Data Source Options
	$cacpwt_data_source = array(
		0 => get_string('profile_field_standard', 'auth_cacpwt')
	);

	if ($DB->record_exists('user_info_field', array('shortname' => 'cacid'))) {
		$cacpwt_data_source[1] = get_string('profile_field_custom', 'auth_cacpwt');
	}

	require_once($CFG->dirroot.'/lib/outputlib.php');

	// Get Moodle headers and save to string
	ob_start();
	echo '<pre>';
	var_dump(getallheaders());
	echo '</pre>';

	$moodle_headers = ob_get_contents();

	ob_end_clean();
	// End Moodle header get

	// Introductory explanation.
	$settings->add(new admin_setting_heading('auth_cacpwt/pluginname', '',
		new lang_string('auth_cacdescription', 'auth_cacpwt')));

	// Header Option.
	$settings->add(new admin_setting_configselect('auth_cacpwt/headeroption',
		get_string('headeroption', 'auth_cacpwt'),
		get_string('headeroption_desc', 'auth_cacpwt'),0, $headeroptions));

	// Test Mode Option.
	$settings->add(new admin_setting_configselect('auth_cacpwt/testmode',
		get_string('testmode', 'auth_cacpwt'),
		get_string('testmode_desc', 'auth_cacpwt'),0, $noyesoptions));

	// CAC ID data source
	$settings->add(new admin_setting_configselect('auth_cacpwt/data_source',
		get_string('data_source', 'auth_cacpwt'),
		get_string('data_source_desc', 'auth_cacpwt'),0, $cacpwt_data_source));

	// Moodle headers settings heading
	$settings->add(new admin_setting_heading('auth_cacpwt/moodlesetting', get_string('moodle_header', 'auth_cacpwt'),
					get_string('moodle_header_desc', 'auth_cacpwt')));

	// Moodle SSL_CLIENT_S_DN
	$settings->add(new admin_setting_configtext('auth_cacpwt/moodle_SSL_CLIENT_S_DN',
		cacpwt_fieldcheck('moodle_SSL_CLIENT_S_DN', 'SSL_CLIENT_S_DN', 1) .
		get_string('moodle_SSL_CLIENT_S_DN', 'auth_cacpwt'), null, 'SSL_CLIENT_S_DN'));

	// Moodle SSL_CLIENT_I_DN
	$settings->add(new admin_setting_configtext('auth_cacpwt/moodle_SSL_CLIENT_I_DN',
		cacpwt_fieldcheck('moodle_SSL_CLIENT_I_DN', 'SSL_CLIENT_I_DN', 1) .
		get_string('moodle_SSL_CLIENT_I_DN', 'auth_cacpwt'), null, 'SSL_CLIENT_I_DN'));

	// Moodle SSL_CLIENT_VERIFY
	$settings->add(new admin_setting_configtext('auth_cacpwt/moodle_SSL_CLIENT_VERIFY',
		cacpwt_fieldcheck('moodle_SSL_CLIENT_VERIFY', 'SSL_CLIENT_VERIFY', 1) .
		get_string('moodle_SSL_CLIENT_VERIFY', 'auth_cacpwt'), null, 'SSL_CLIENT_VERIFY'));

	// Moodle SSL_CLIENT_SAN_Email_0
	$settings->add(new admin_setting_configtext('auth_cacpwt/moodle_SSL_CLIENT_SAN_Email_0',
		cacpwt_fieldcheck('moodle_SSL_CLIENT_SAN_Email_0', 'SSL_CLIENT_SAN_OTHER_msUPN_0', 1) .
		get_string('moodle_SSL_CLIENT_SAN_Email_0', 'auth_cacpwt'), null, 'SSL_CLIENT_SAN_OTHER_msUPN_0'));

	// Moodle Available Headers Output
	$settings->add(new admin_setting_description('auth_cacpwt/available_moodle_headers',
		get_string('headersavailable','auth_cacpwt'), $moodle_headers));

	// PHP header section heading
	$settings->add(new admin_setting_heading('auth_cacpwt/phpsetting', get_string('php_header', 'auth_cacpwt'),
		get_string('php_header_desc', 'auth_cacpwt')));

	// PHP SSL_CLIENT_S_DN
	$settings->add(new admin_setting_configtext('auth_cacpwt/php_SSL_CLIENT_S_DN',
		cacpwt_fieldcheck('php_SSL_CLIENT_S_DN', 'SSL_CLIENT_S_DN') .
		get_string('php_SSL_CLIENT_S_DN', 'auth_cacpwt'), null, 'SSL_CLIENT_S_DN'));

	// PHP SSL_CLIENT_I_DN
	$settings->add(new admin_setting_configtext('auth_cacpwt/php_SSL_CLIENT_I_DN',
		cacpwt_fieldcheck('php_SSL_CLIENT_I_DN', 'SSL_CLIENT_I_DN') .
		get_string('php_SSL_CLIENT_I_DN', 'auth_cacpwt'), null, 'SSL_CLIENT_I_DN'));

	// PHP SSL_CLIENT_VERIFY
	$settings->add(new admin_setting_configtext('auth_cacpwt/php_SSL_CLIENT_VERIFY',
		cacpwt_fieldcheck('php_SSL_CLIENT_VERIFY', 'SSL_CLIENT_VERIFY') .
		get_string('php_SSL_CLIENT_VERIFY', 'auth_cacpwt'), null, 'SSL_CLIENT_VERIFY'));

	// PHP SSL_CLIENT_SAN_Email_0
	$settings->add(new admin_setting_configtext('auth_cacpwt/php_SSL_CLIENT_SAN_Email_0',
		cacpwt_fieldcheck('php_SSL_CLIENT_SAN_Email_0', 'SSL_CLIENT_SAN_OTHER_msUPN_0') .
		get_string('php_SSL_CLIENT_SAN_Email_0', 'auth_cacpwt'), null, 'SSL_CLIENT_SAN_OTHER_msUPN_0'));

	// Logout target section heading
	$settings->add(new admin_setting_heading('auth_cacpwt/logoutsettings', get_string('logout_header', 'auth_cacpwt'),
		get_string('logout_header_desc', 'auth_cacpwt')));

	// Enable Alt Logout Target
	$settings->add(new admin_setting_configcheckbox('auth_cacpwt/logout_alt_enabled',
		get_string('logout_alt_enabled', 'auth_cacpwt'), get_string('logout_alt_enabled_desc', 'auth_cacpwt'), 0));

	// Alt Logout Target URL
	$settings->add(new admin_setting_configtext('auth_cacpwt/logout_alt_url',
		get_string('logout_alt_url', 'auth_cacpwt'), null, ''));

}
