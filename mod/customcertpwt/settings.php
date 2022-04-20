<?php
// This file is part of the Certificate module for Moodle - http://moodle.org/
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
 * Creates a link to the upload form on the settings page.
 *
 * @package    mod_customcertpwt
 * @copyright  2021 PowerTrain Inc
 * @license    All Rights Reserved
 */

defined('MOODLE_INTERNAL') || die;

$url = $CFG->wwwroot . '/mod/customcertpwt/verify_certificate.php';

$ADMIN->add('modsettings', new admin_category('customcertpwt', get_string('pluginname', 'mod_customcertpwt')));
$settings = new admin_settingpage('modsettingcustomcertpwt', new lang_string('customcertpwtsettings', 'mod_customcertpwt'));

$settings->add(new admin_setting_configcheckbox('customcertpwt/verifyallcertificates',
    get_string('verifyallcertificates', 'customcertpwt'),
    get_string('verifyallcertificates_desc', 'customcertpwt', $url),
    0));

$settings->add(new admin_setting_configcheckbox('customcertpwt/showposxy',
    get_string('showposxy', 'customcertpwt'),
    get_string('showposxy_desc', 'customcertpwt'),
    0));

$settings->add(new \mod_customcertpwt\admin_setting_link('customcertpwt/verifycertificate',
    get_string('verifycertificate', 'customcertpwt'), get_string('verifycertificatedesc', 'customcertpwt'),
    get_string('verifycertificate', 'customcertpwt'), new moodle_url('/mod/customcertpwt/verify_certificate.php'), ''));

$settings->add(new \mod_customcertpwt\admin_setting_link('customcertpwt/managetemplates',
    get_string('managetemplates', 'customcertpwt'), get_string('managetemplatesdesc', 'customcertpwt'),
    get_string('managetemplates', 'customcertpwt'), new moodle_url('/mod/customcertpwt/manage_templates.php'), ''));

$settings->add(new \mod_customcertpwt\admin_setting_link('customcertpwt/uploadimage',
    get_string('uploadimage', 'customcertpwt'), get_string('uploadimagedesc', 'customcertpwt'),
    get_string('uploadimage', 'customcertpwt'), new moodle_url('/mod/customcertpwt/upload_image.php'), ''));

$settings->add(new admin_setting_heading('defaults',
    get_string('modeditdefaults', 'admin'), get_string('condifmodeditdefaults', 'admin')));

$yesnooptions = [
    0 => get_string('no'),
    1 => get_string('yes'),
];

$settings->add(new admin_setting_configselect('customcertpwt/emailstudents',
    get_string('emailstudents', 'customcertpwt'), get_string('emailstudents_help', 'customcertpwt'), 0, $yesnooptions));
$settings->add(new admin_setting_configselect('customcertpwt/emailteachers',
    get_string('emailteachers', 'customcertpwt'), get_string('emailteachers_help', 'customcertpwt'), 0, $yesnooptions));
$settings->add(new admin_setting_configtext('customcertpwt/emailothers',
    get_string('emailothers', 'customcertpwt'), get_string('emailothers_help', 'customcertpwt'), '', PARAM_TEXT));
$settings->add(new admin_setting_configselect('customcertpwt/verifyany',
    get_string('verifycertificateanyone', 'customcertpwt'), get_string('verifycertificateanyone_help', 'customcertpwt'),
    0, $yesnooptions));
$settings->add(new admin_setting_configtext('customcertpwt/requiredtime',
    get_string('coursetimereq', 'customcertpwt'), get_string('coursetimereq_help', 'customcertpwt'), 0, PARAM_INT));
$settings->add(new admin_setting_configcheckbox('customcertpwt/protection_print',
    get_string('preventprint', 'customcertpwt'),
    get_string('preventprint_desc', 'customcertpwt'),
    0));
$settings->add(new admin_setting_configcheckbox('customcertpwt/protection_modify',
    get_string('preventmodify', 'customcertpwt'),
    get_string('preventmodify_desc', 'customcertpwt'),
    0));
$settings->add(new admin_setting_configcheckbox('customcertpwt/protection_copy',
    get_string('preventcopy', 'customcertpwt'),
    get_string('preventcopy_desc', 'customcertpwt'),
    0));

$ADMIN->add('customcertpwt', $settings);

// Element plugin settings.
$ADMIN->add('customcertpwt', new admin_category('customcertpwtelements', get_string('elementplugins', 'customcertpwt')));
$plugins = \core_plugin_manager::instance()->get_plugins_of_type('customcertpwtelement');
foreach ($plugins as $plugin) {
    $plugin->load_settings($ADMIN, 'customcertpwtelements', $hassiteconfig);
}

// Tell core we already added the settings structure.
$settings = null;
