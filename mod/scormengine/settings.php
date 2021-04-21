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
 * Plugin administration pages are defined here.
 *
 * @package     mod_scormengine
 * @category    admin
 * @copyright   Veracity
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {
     // TODO: Define the plugin settings page.
     // https://docs.moodle.org/dev/Admin_settings

     $settings->add(new admin_setting_configtext('scormengine/endpoint', "API Host and Protocol",
          "The ScormEngine install API host and protocol (http://localhost:3005)", ''));
     $settings->add(new admin_setting_configtext('scormengine/site_home', "Moodle Site Host",
          "The LMS host and protocol", ''));
     $settings->add(new admin_setting_configtext('scormengine/username', "API UserName",
          "The ScormEngine install API username", ''));
     $settings->add(new admin_setting_configtext('scormengine/password', "API Password",
          "The ScormEngine install API password", ''));
     $settings->add(new admin_setting_configtext('scormengine/lrs_endpoint', "LRS Statement Endpoint",
          "The LRS host and protocol", ''));
     $settings->add(new admin_setting_configtext('scormengine/lrs_username', "LRS API Key username",
          "The LRS API Key username", ''));
     $settings->add(new admin_setting_configtext('scormengine/lrs_password', "LRS API Key Password",
          "The LRS API Key password", ''));
}
