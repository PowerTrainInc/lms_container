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
 * PowerTrain CAC Plugin English Language File
 *
 * @package   auth_cacpwt
 * @copyright 2021 PowerTrain Inc {@link https://powertrain.com}
 * @license   All Rights Reserved
 */

$string['access_key'] = 'Access Key';
$string['auth_cacdescription'] = 'Auxiliary plugin that allows the user to login into system with their CAC card.<br>
                                <b>Note:</b> The required headers will not be available without an https connection.';
$string['container_name'] = 'Container Name';
$string['container_name_desc'] = 'Amazon Web Services (AWS) S3 container name.';
$string['cron:error_connecting'] = 'Failed to connect to Amazon Web Services (AWS) S3 Bucket.';
$string['cron:error_container'] = 'Failed to access container.  Missing container or insufficient privileges.';
$string['cron:error_import_file'] = 'Failed to retrieve import file.  Missing file or insufficient privileges.';
$string['data_source'] = 'CAC ID Data Source';
$string['data_source_desc'] = 'Location of user CAC ID.  If the Custom Profile Field option is not available, create a
                            custom profile text field with the shortname of <i>cacid</i>.';
$string['data_audit'] = 'CAC Signup Audit';
$string['data_audit_desc'] = 'The textarea custom profile field that will store important user data obtained at the moment
                            of account creation.';
$string['data_import_task'] = 'S3 Bucket Data Importer';
$string['enables3'] = 'Enable AWS S3 Imports';
$string['enables3_desc'] = 'Enable AWS S3 imports on a scheduled basis.';
$string['enabletest'] = 'Enable Import Test Mode';
$string['enabletest_desc'] = 'Test mode limits imports to 1,000 records per run.';
$string['error:invalidemail'] = 'Invalid email address.';
$string['error:missingemail'] = 'Missing email address.';
$string['error:missingfirstname'] = 'Missing first name.';
$string['error:missinglastname'] = 'Missing last name.';
$string['first_row_headers'] = 'First Row Headers';
$string['first_row_headers_desc'] = 'Does the first row of the CSV import file contain column headers?';
$string['aws_header'] = 'Amazon Web Services Configuration';
$string['aws_header_desc'] = 'Amazon Web Services (AWS) S3 Bucket Configuration';
$string['headeroption'] = 'Header Validation Option';
$string['headeroption_desc'] = 'Choose which headers the plugin will use to validate CAC credentials.';
$string['headeroptions:moodle'] = 'Moodle';
$string['headeroptions:php'] = 'PHP';
$string['headersavailable'] = 'Available Moodle Headers';
$string['import_filename'] = 'Import Filename';
$string['import_filename_desc'] = 'Import file must be a CSV file named with the .csv extension. Do not include path
                                information.';
$string['logout_alt_enabled'] = 'Enable Alt Logout Target';
$string['logout_alt_enabled_desc'] = 'Enable redirection to an alternate URL after logout.';
$string['logout_alt_url'] = 'Alt Logout Target URL';
$string['logout_header'] = 'Logout Target';
$string['logout_header_desc'] = 'Set target URL for user logout.';
$string['moodle_header'] = 'Moodle header variables';
$string['moodle_header_desc'] = 'Client SSL variables passed through Moodle headers.';
$string['moodle_SSL_CLIENT_S_DN'] = 'Moodle SSL_CLIENT_S_DN';
$string['moodle_SSL_CLIENT_I_DN'] = 'Moodle SSL_CLIENT_I_DN';
$string['moodle_SSL_CLIENT_VERIFY'] = 'Moodle SSL_CLIENT_VERIFY';
$string['moodle_SSL_CLIENT_SAN_Email_0'] = 'Moodle SSL_CLIENT_SAN_Email_0';
$string['noaccess'] = 'No Access Message';
$string['noaccess_background'] = 'No Access Background';
$string['noaccess_background_desc'] = 'No access message body background color.';
$string['noaccess_default'] = 'Unable to locate your DODID in our system.';
$string['noaccess_desc'] = 'No access message displayed to users with a DODID not located in the import table.';
$string['noaccess_header'] = 'No Access Page';
$string['noaccess_header_desc'] = 'No access page settings.';
$string['noaccess_padding'] = 'No Access Padding';
$string['noaccess_padding_desc'] = 'Padding around the no access message body.';
$string['noaccess_width'] = 'No Access Width';
$string['noaccess_width_desc'] = 'Width of no access message body.';
$string['nofields'] = 'No available fields';
$string['none'] = 'None';
$string['page_heading'] = 'New User Registration';
$string['page_title'] = 'Site Registration';
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
$string['restrictedaccess'] = 'Restricted Access Message';
$string['restrictedaccess_background'] = 'Restricted Access Background';
$string['restrictedaccess_background_desc'] = 'Restricted access message body background color.';
$string['restrictedaccess_default'] = 'Access to this system has been restricted.';
$string['restrictedaccess_desc'] = 'Restricted access message displayed to foreign nationals and users with other
    account restrictions.';
$string['restrictedaccess_header'] = 'Restricted Access Page';
$string['restrictedaccess_header_desc'] = 'Restricted access page settings.';
$string['restrictedaccess_padding'] = 'Restricted Access Padding';
$string['restrictedaccess_padding_desc'] = 'Padding around the restricted access message body.';
$string['restrictedaccess_title'] = 'Restricted Access';
$string['restrictedaccess_width'] = 'Restricted Access Width';
$string['restrictedaccess_width_desc'] = 'Width of restricted access message body.';
$string['restricted_cohort'] = 'Restricted Cohort';
$string['restricted_cohort_desc'] = 'Typically reserved for foreign nationals.';
$string['secret_key'] = 'Secret Key';
$string['secret_key_desc'] = 'A valid access key and secret key must be provided for the importer to function.';
$string['testmode'] = 'Test Mode';
$string['testmode_desc'] = 'DO NOT USE IN PRODUCTION! Reduced client certificate authentication security.
    Used for testing authentication on a site with a locally generated SSL certificate.';
$string['timezone'] = 'America/New_York';
$string['unrestricted_cohort'] = 'Unrestricted Cohort';
$string['unrestricted_cohort_desc'] = 'Typically reserved for U.S. citizens.';
