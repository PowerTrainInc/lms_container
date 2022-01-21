<?php
/**
 * This file contains the completion_tracker upgrade functionality.
 *
 * @package		local_completion_tracker
 * @copyright	2022 PowerTrain Inc {@link https://powertrain.com/}
 * @license		All Rights Reserved
 */

defined('MOODLE_INTERNAL') || die();

/**
 * upgrade this completion_tracker
 * @param int $oldversion The old version of the assign module
 * @return bool
 */
function xmldb_local_completion_tracker_upgrade($oldversion) {
	global $DB;

	/*
	$dbman = $DB->get_manager();

	if ($oldversion < xxxxxxx) {
		// Define field yyyyyy to be added to local_completion_tracker.
		$table = new xmldb_table('local_completion_tracker');
		$field = new xmldb_field('yyyyyy', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'id');

		// Conditionally launch add field user_id.
		if (!$dbman->field_exists($table, $field)) {
			$dbman->add_field($table, $field);
		}

		// Completion tracker savepoint reached.
		upgrade_plugin_savepoint(true, xxxxxx, 'local', 'completion_tracker');
	}
	*/

	return true;
}
