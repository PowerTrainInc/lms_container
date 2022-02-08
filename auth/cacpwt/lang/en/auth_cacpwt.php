<?php
/**
 * PowerTrain CAC Plugin English Language File
 *
 * @package   auth_cacpwt
 * @copyright 2021 PowerTrain Inc {@link https://powertrain.com}
 * @license   All Rights Reserved
 */

$string['auth_cacdescription'] = 'Auxiliary plugin that allows the user to login into system with their CAC card.<br>
								<b>Note:</b> The required headers will not be available without an https connection.';
$string['data_source'] = 'CAC ID Data Source';
$string['data_source_desc'] = 'Location of user CAC ID.  If the Custom Profile Field option is not available, create a
							custom profile text field with the shortname of <i>cacid</i>.';
$string['headeroption'] = 'Header Validation Option';
$string['headeroption_desc'] = 'Choose which headers the plugin will use to validate CAC credentials.';
$string['headeroptions:moodle'] = 'Moodle';
$string['headeroptions:php'] = 'PHP';
$string['headersavailable'] = 'Available Moodle Headers';
$string['logout_alt_enabled'] = 'Enable Alt Logout Target';
$string['logout_alt_enabled_desc'] = 'Enable redirection to an alternate URL after logout.';
$string['logout_alt_url'] = 'Alt Logout Target URL';
$string['logout_header'] = 'Logout Target';
$string['logout_header_desc'] = 'Set target URL for user logout.';
$string['moodle_header'] = 'Moodle header variables';
$string['moodle_header_desc'] = 'Client SSL variables passed through Moodle headers';
$string['moodle_SSL_CLIENT_S_DN'] = 'Moodle SSL_CLIENT_S_DN';
$string['moodle_SSL_CLIENT_I_DN'] = 'Moodle SSL_CLIENT_I_DN';
$string['moodle_SSL_CLIENT_VERIFY'] = 'Moodle SSL_CLIENT_VERIFY';
$string['moodle_SSL_CLIENT_SAN_Email_0'] = 'Moodle SSL_CLIENT_SAN_Email_0';
$string['php_header'] = 'PHP/$_SERVER header variables';
$string['php_header_desc'] = 'Client SSL variables passed through PHP headers/$_SERVER variables.';
$string['php_SSL_CLIENT_S_DN'] = 'PHP SSL_CLIENT_S_DN';
$string['php_SSL_CLIENT_I_DN'] = 'PHP SSL_CLIENT_I_DN';
$string['php_SSL_CLIENT_VERIFY'] = 'PHP SSL_CLIENT_VERIFY';
$string['php_SSL_CLIENT_SAN_Email_0'] = 'PHP SSL_CLIENT_SAN_Email_0';
$string['pluginname'] = 'PowerTrain CAC login';
$string['privacy:metadata'] = 'The PowerTrain CAC authentication plugin may store personal data.';
$string['profile_field_custom'] = 'Custom Profile Field - CAC ID (cacid)';
$string['profile_field_standard'] = 'Profile Field - ID Number';
$string['testmode'] = 'Test Mode';
$string['testmode_desc'] = 'DO NOT USE IN PRODUCTION! Reduced client certificate authentication security.  
							Used for testing authentication on a site with a locally generated SSL certificate.';
