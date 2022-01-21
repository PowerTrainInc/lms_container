<?php
/**
 * Plugin settings.
 *
 * @package    local_completion_tracker
 * @copyright  2022 PowerTrain Inc {@link https://powertrain.com/}
 * @license    All Rights Reserved
 */

/**
 * @var $CFG
 * @var $ADMIN
 * @var $hassiteconfig
 */

defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {
	$no_yes_options = array(
		0 => get_string('no'),
		1 => get_string('yes'),
		2 => get_string('optional', 'local_completion_tracker')
	);

	$noyes = array(
		0 => get_string('no'),
		1 => get_string('yes')
	);

	$settings = new admin_settingpage('completion_tracker', get_string('pluginname', 'local_completion_tracker'));

	$ADMIN->add('localplugins', $settings);

	$settings->add(new admin_setting_heading('local_completion_tracker/settings', '',
		get_string('pluginname_settings', 'local_completion_tracker')));

	// Generic entry
	$settings->add(new admin_setting_configselect('local_completion_tracker/generic',
		get_string('generic_status', 'local_completion_tracker'),
		get_string('generic_status_desc', 'local_completion_tracker'), 0, $no_yes_options));

}
