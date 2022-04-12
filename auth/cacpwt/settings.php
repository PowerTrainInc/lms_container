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
 * PowerTrain CAC Plugin Settings
 *
 * @package   auth_cacpwt
 * @copyright 2021 PowerTrain Inc {@link https://powertrain.com}
 * @license   All Rights Reserved
 */
defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot . '/cohort/lib.php');

if (!function_exists('getallheaders')) {
    /**
     * Replacement function for the Apache compatible getallheaders() function.
     *
     * @return array
     */
    function getallheaders(): array {
        $headers = array();

        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }

        return $headers;
    }
}

if (!function_exists('cacpwt_fieldcheck')) {
    /**
     * Function to check if fields exist.
     *
     * @param string $fieldname
     * @param string $default
     * @param int $type
     * @return string Return facode.
     * @throws dml_exception
     */
    function cacpwt_fieldcheck(string $fieldname, string $default, int $type = 0): string {
        // Get current settings if any.
        $currentsettings = get_config('auth_cacpwt');
        $headers = getallheaders();
        $fieldmatch = false;

        if (!isset($currentsettings->$fieldname)) {
            $settingsdefined = false;
        } else {
            $settingsdefined = true;
        }

        if ($type == 0) {
            // PHP Headers.
            $headercheck = $_SERVER;
        } else {
            // Default to Moodle headers. If wrong type was picked, this will fail anyway.
            $headercheck = $headers;
        }

        if ($settingsdefined && isset($currentsettings->$fieldname)) {
            if (isset($headercheck[$currentsettings->$fieldname])) {
                $fieldmatch = true;
            }
        } else if (!$settingsdefined) {
            if (isset($headercheck[$default])) {
                $fieldmatch = true;
            }
        }

        if ($fieldmatch === true) {
            $facode = '<i class="fa fa-check" style="color: green;" aria-hidden="true"></i>';
        } else {
            $facode = '<i class="fa fa-times" style="color: red;" aria-hidden="true"></i>';
        }

        return $facode;
    }
}

if ($ADMIN->fulltree) {
    // Set No/Yes Dropdown Options.
    $noyesoptions = array(
        0 => get_string('no'),
        1 => get_string('yes')
    );

    // Set Header Options.
    $headeroptions = array(
        0 => get_string('headeroptions:php', 'auth_cacpwt'),
        1 => get_string('headeroptions:moodle', 'auth_cacpwt')
    );

    // Get Cohorts.
    $cohorts = cohort_get_all_cohorts(0, 999999);
    $cohortlist = array();

    foreach ($cohorts['cohorts'] as $cohort) {
        if ($cohort->visible == '1' && $cohort->contextid = '1') {
            $cohortlist[$cohort->id] = $cohort->name;
        }
    }

    unset($cohorts, $cohort);

    asort($cohortlist);

    $cohortlist['-1'] = get_string('none', 'auth_cacpwt');
    $cohortnone = count($cohortlist);

    // Set Data Source Options.
    $cacpwtdatasource = array(
        0 => get_string('profile_field_standard', 'auth_cacpwt')
    );

    if ($DB->record_exists('user_info_field', array('shortname' => 'cacid'))) {
        $cacpwtdatasource[1] = get_string('profile_field_custom', 'auth_cacpwt');
    }

    $cacpwtaudit = array();

    if ($results = $DB->get_records('user_info_field', array('datatype' => 'textarea'), 'shortname,name')) {
        $cacpwtaudit['None'] = get_string('none', 'auth_cacpwt');

        foreach ($results as $result) {
            $cacpwtaudit[$result->shortname] = $result->name;
        }
    } else {
        $cacpwtaudit['none'] = get_string('nofields', 'auth_cacpwt');
    }

    unset($results, $result);

    require_once($CFG->dirroot.'/lib/outputlib.php');

    // Get Moodle headers and save to string.
    ob_start();
    echo '<pre>';
    var_dump(getallheaders());
    echo '</pre>';

    $moodleheaders = ob_get_contents();

    ob_end_clean();
    // End Moodle header get.

    // Introductory explanation.
    $settings->add(new admin_setting_heading('auth_cacpwt/pluginname', '',
        new lang_string('auth_cacdescription', 'auth_cacpwt')));

    // Header Option.
    $settings->add(new admin_setting_configselect('auth_cacpwt/headeroption',
        get_string('headeroption', 'auth_cacpwt'),
        get_string('headeroption_desc', 'auth_cacpwt'), 0, $headeroptions));

    // Test Mode Option.
    $settings->add(new admin_setting_configselect('auth_cacpwt/testmode',
        get_string('testmode', 'auth_cacpwt'),
        get_string('testmode_desc', 'auth_cacpwt'), 0, $noyesoptions));

    // CAC ID data source.
    $settings->add(new admin_setting_configselect('auth_cacpwt/data_source',
        get_string('data_source', 'auth_cacpwt'),
        get_string('data_source_desc', 'auth_cacpwt'), 0, $cacpwtdatasource));

    // CAC signup audit field.
    $settings->add(new admin_setting_configselect('auth_cacpwt/audit_field',
        get_string('data_audit', 'auth_cacpwt'),
        get_string('data_audit_desc', 'auth_cacpwt'), get_string('none', 'auth_cacpwt'), $cacpwtaudit));

    // Unrestricted Cohort Option.
    $settings->add(new admin_setting_configselect('auth_cacpwt/unrestricted_cohort',
        get_string('unrestricted_cohort', 'auth_cacpwt'),
        get_string('unrestricted_cohort_desc', 'auth_cacpwt'), '-1', $cohortlist));

    // Restricted Cohort Option.
    $settings->add(new admin_setting_configselect('auth_cacpwt/restricted_cohort',
        get_string('restricted_cohort', 'auth_cacpwt'),
        get_string('restricted_cohort_desc', 'auth_cacpwt'), '-1', $cohortlist));

    // No Access Page Settings.
    $settings->add(new admin_setting_heading('auth_cacpwt/noaccesssetting', get_string('noaccess_header', 'auth_cacpwt'),
        get_string('noaccess_header_desc', 'auth_cacpwt')));

    // No Access Content.
    $settings->add(new admin_setting_confightmleditor('auth_cacpwt/noaccess',
        get_string('noaccess', 'auth_cacpwt'), get_string('noaccess_desc', 'auth_cacpwt'),
        get_string('noaccess_default', 'auth_cacpwt'), PARAM_RAW_TRIMMED));

    // No Access Width.
    $settings->add(new admin_setting_configtext('auth_cacpwt/noaccess_width',
        get_string('noaccess_width', 'auth_cacpwt'), get_string('noaccess_width_desc', 'auth_cacpwt'), '800px'));

    // No Access Padding.
    $settings->add(new admin_setting_configtext('auth_cacpwt/noaccess_padding',
        get_string('noaccess_padding', 'auth_cacpwt'), get_string('noaccess_padding_desc', 'auth_cacpwt'), '20px'));

    // No Access Background.
    $settings->add(new admin_setting_configtext('auth_cacpwt/noaccess_background',
        get_string('noaccess_background', 'auth_cacpwt'), get_string('noaccess_background_desc', 'auth_cacpwt'), '#f2f2f2'));

    // Restricted Access Page Settings.
    $settings->add(new admin_setting_heading('auth_cacpwt/restrictedsetting',
        get_string('restrictedaccess_header', 'auth_cacpwt'),
        get_string('restrictedaccess_header_desc', 'auth_cacpwt')));

    // Restricted Access Content.
    $settings->add(new admin_setting_confightmleditor('auth_cacpwt/restrictedaccess',
        get_string('restrictedaccess', 'auth_cacpwt'), get_string('restrictedaccess_desc', 'auth_cacpwt'),
        get_string('restrictedaccess_default', 'auth_cacpwt'), PARAM_RAW_TRIMMED));

    // Restricted Access Width.
    $settings->add(new admin_setting_configtext('auth_cacpwt/restrictedaccess_width',
        get_string('restrictedaccess_width', 'auth_cacpwt'),
        get_string('restrictedaccess_width_desc', 'auth_cacpwt'), '800px'));

    // Restricted Access Padding.
    $settings->add(new admin_setting_configtext('auth_cacpwt/restrictedaccess_padding',
        get_string('restrictedaccess_padding', 'auth_cacpwt'),
        get_string('restrictedaccess_padding_desc', 'auth_cacpwt'), '20px'));

    // Restricted Access Background.
    $settings->add(new admin_setting_configtext('auth_cacpwt/restrictedaccess_background',
        get_string('restrictedaccess_background', 'auth_cacpwt'),
        get_string('restrictedaccess_background_desc', 'auth_cacpwt'), '#f2f2f2'));

    // Moodle headers settings heading.
    $settings->add(new admin_setting_heading('auth_cacpwt/moodlesetting', get_string('moodle_header', 'auth_cacpwt'),
                    get_string('moodle_header_desc', 'auth_cacpwt')));

    // Moodle SSL_CLIENT_S_DN.
    $settings->add(new admin_setting_configtext('auth_cacpwt/moodle_SSL_CLIENT_S_DN',
        cacpwt_fieldcheck('moodle_SSL_CLIENT_S_DN', 'SSL_CLIENT_S_DN', 1) .
        get_string('moodle_SSL_CLIENT_S_DN', 'auth_cacpwt'), null, 'SSL_CLIENT_S_DN'));

    // Moodle SSL_CLIENT_I_DN.
    $settings->add(new admin_setting_configtext('auth_cacpwt/moodle_SSL_CLIENT_I_DN',
        cacpwt_fieldcheck('moodle_SSL_CLIENT_I_DN', 'SSL_CLIENT_I_DN', 1) .
        get_string('moodle_SSL_CLIENT_I_DN', 'auth_cacpwt'), null, 'SSL_CLIENT_I_DN'));

    // Moodle SSL_CLIENT_VERIFY.
    $settings->add(new admin_setting_configtext('auth_cacpwt/moodle_SSL_CLIENT_VERIFY',
        cacpwt_fieldcheck('moodle_SSL_CLIENT_VERIFY', 'SSL_CLIENT_VERIFY', 1) .
        get_string('moodle_SSL_CLIENT_VERIFY', 'auth_cacpwt'), null, 'SSL_CLIENT_VERIFY'));

    // Moodle SSL_CLIENT_SAN_Email_0.
    $settings->add(new admin_setting_configtext('auth_cacpwt/moodle_SSL_CLIENT_SAN_Email_0',
        cacpwt_fieldcheck('moodle_SSL_CLIENT_SAN_Email_0', 'SSL_CLIENT_SAN_OTHER_msUPN_0', 1) .
        get_string('moodle_SSL_CLIENT_SAN_Email_0', 'auth_cacpwt'), null, 'SSL_CLIENT_SAN_OTHER_msUPN_0'));

    // Moodle Available Headers Output.
    $settings->add(new admin_setting_description('auth_cacpwt/available_moodleheaders',
        get_string('headersavailable', 'auth_cacpwt'), $moodleheaders));

    // PHP header section heading.
    $settings->add(new admin_setting_heading('auth_cacpwt/phpsetting', get_string('php_header', 'auth_cacpwt'),
        get_string('php_header_desc', 'auth_cacpwt')));

    // PHP SSL_CLIENT_S_DN.
    $settings->add(new admin_setting_configtext('auth_cacpwt/php_SSL_CLIENT_S_DN',
        cacpwt_fieldcheck('php_SSL_CLIENT_S_DN', 'SSL_CLIENT_S_DN') .
        get_string('php_SSL_CLIENT_S_DN', 'auth_cacpwt'), null, 'SSL_CLIENT_S_DN'));

    // PHP SSL_CLIENT_I_DN.
    $settings->add(new admin_setting_configtext('auth_cacpwt/php_SSL_CLIENT_I_DN',
        cacpwt_fieldcheck('php_SSL_CLIENT_I_DN', 'SSL_CLIENT_I_DN') .
        get_string('php_SSL_CLIENT_I_DN', 'auth_cacpwt'), null, 'SSL_CLIENT_I_DN'));

    // PHP SSL_CLIENT_VERIFY.
    $settings->add(new admin_setting_configtext('auth_cacpwt/php_SSL_CLIENT_VERIFY',
        cacpwt_fieldcheck('php_SSL_CLIENT_VERIFY', 'SSL_CLIENT_VERIFY') .
        get_string('php_SSL_CLIENT_VERIFY', 'auth_cacpwt'), null, 'SSL_CLIENT_VERIFY'));

    // PHP SSL_CLIENT_SAN_Email_0.
    $settings->add(new admin_setting_configtext('auth_cacpwt/php_SSL_CLIENT_SAN_Email_0',
        cacpwt_fieldcheck('php_SSL_CLIENT_SAN_Email_0', 'SSL_CLIENT_SAN_OTHER_msUPN_0') .
        get_string('php_SSL_CLIENT_SAN_Email_0', 'auth_cacpwt'), null, 'SSL_CLIENT_SAN_OTHER_msUPN_0'));

    // Logout target section heading.
    $settings->add(new admin_setting_heading('auth_cacpwt/logoutsettings', get_string('logout_header', 'auth_cacpwt'),
        get_string('logout_header_desc', 'auth_cacpwt')));

    // Enable Alt Logout Target.
    $settings->add(new admin_setting_configcheckbox('auth_cacpwt/logout_alt_enabled',
        get_string('logout_alt_enabled', 'auth_cacpwt'), get_string('logout_alt_enabled_desc', 'auth_cacpwt'), 0));

    // Alt Logout Target URL.
    $settings->add(new admin_setting_configtext('auth_cacpwt/logout_alt_url',
        get_string('logout_alt_url', 'auth_cacpwt'), null, ''));

    // AWS section heading.
    $settings->add(new admin_setting_heading('auth_cacpwt/awssettings', get_string('aws_header', 'auth_cacpwt'),
        get_string('aws_header_desc', 'auth_cacpwt')));

    // Container Name.
    $settings->add(new admin_setting_configtext('auth_cacpwt/container_name',
        get_string('container_name', 'auth_cacpwt'), get_string('container_name_desc', 'auth_cacpwt'), ''));

    // Enable Imports from S3 Bucket.
    $settings->add(new admin_setting_configselect('auth_cacpwt/enable_s3_imports',
        get_string('enables3', 'auth_cacpwt'),
        get_string('enables3_desc', 'auth_cacpwt'), 0, $noyesoptions));

    // Enable Imports Test Mode.
    $settings->add(new admin_setting_configselect('auth_cacpwt/enable_imports_test_mode',
        get_string('enabletest', 'auth_cacpwt'),
        get_string('enabletest_desc', 'auth_cacpwt'), 0, $noyesoptions));

    // Import Filename.
    $settings->add(new admin_setting_configtext('auth_cacpwt/import_filename',
        get_string('import_filename', 'auth_cacpwt'), get_string('import_filename_desc', 'auth_cacpwt'), ''));

    // First Row Headers.
    $settings->add(new admin_setting_configselect('auth_cacpwt/first_row_headers',
        get_string('first_row_headers', 'auth_cacpwt'),
        get_string('first_row_headers_desc', 'auth_cacpwt'), 0, $noyesoptions));

    // Access Key.
    $settings->add(new admin_setting_configtext('auth_cacpwt/access_key',
        get_string('access_key', 'auth_cacpwt'), null, ''));

    // Secret Key.
    $settings->add(new admin_setting_configpasswordunmask('auth_cacpwt/secret_key',
        get_string('secret_key', 'auth_cacpwt'), get_string('secret_key_desc', 'auth_cacpwt'), ''));

}
