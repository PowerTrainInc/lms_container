<?php
/**
 * @package		local_completion_tracker
 * @subpackage	completion_tracker
 * @copyright	2022 PowerTrain Inc {@link https://powertrain.com/}
 * @license		All Rights Reserved
 */

/**
 * List of features supported in completion_tracker module
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed True if module supports feature, false if not, null if unknown
 */

defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');

/**
 * Serve the files from the trainingupload file areas
 *
 * @param stdClass $course the course object
 * @param stdClass $cm the course module object
 * @param stdClass $context the context
 * @param string $filearea the name of the file area
 * @param array $args extra arguments (itemid, path)
 * @param bool $forcedownload force download
 * @param array $options additional options affecting the file serving
 * @return bool false if the file not found, just send the file otherwise and do not return anything
 * @throws coding_exception
 * @throws dml_exception
 * @throws moodle_exception
 * @throws require_login_exception
 */
function local_completion_tracker_pluginfile($course, $cm, stdClass $context, string $filearea, array $args,
											 bool $forcedownload, array $options=array()): bool {
	// Check the context level is as expected
	if ($context->contextlevel != CONTEXT_SYSTEM) {
		return false;
	}

	// Make sure the filearea is one of those used by the plugin.
	if ($filearea !== 'public') {
		return false;
	}

	// Make sure the user is logged
	require_login();

	$context = context_system::instance();

	// Check the user has the capability to view this file
	if (!has_capability('local/completion_tracker:archive', $context)) {
		return false;
	}

	// Leave this line out if you set the itemid to null in make_pluginfile_url (set $itemid to 0 instead).
	$itemid = array_shift($args); // The first item in the $args array.

	// Use the itemid to retrieve any relevant data records and perform any security checks to see if the
	// user really does have access to the file in question.

	// Extract the filename / filepath from the $args array.
	$filename = array_pop($args); // The last item in the $args array.

	if (!$args) {
		$filepath = '/'; // $args is empty => the path is '/'
	} else {
		$filepath = '/'.implode('/', $args).'/'; // $args contains elements of the filepath
	}

	// Retrieve the file from the Files API.
	$fs = get_file_storage();
	$file = $fs->get_file($context->id, 'local_completion_tracker', $filearea, $itemid, $filepath, $filename);

	if (!$file) {
		return false; // The file does not exist.
	}

	// We can now send the file back to the browser - in this case with a cache lifetime of 1 day and no filtering.
	send_stored_file($file, 86400, 0, $forcedownload, $options);

	return true;
}