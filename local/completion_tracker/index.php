<?php
/**
 * Completion Tracker main page.
 *
 * @package    local_completion_tracker
 * @copyright  2022 PowerTrain Inc {@link https://powertrain.com/}
 * @license    All Rights Reserved
 */

use core\dataformat;

require('../../config.php');
/**
 * @var $CFG
 * @var $PAGE
 * @var $OUTPUT
 * @var $DB
 */

redirect_if_major_upgrade_required();

$PAGE->set_context(context_system::instance());

require_login();

require_capability('local/completion_tracker:view', context_system::instance());

$title = get_string('pluginname', 'local_completion_tracker');

$PAGE->set_url($CFG->wwwroot . '/local/completion_tracker/index.php');
$PAGE->set_title($title);
$PAGE->set_pagelayout('standard');

$PAGE->navbar->ignore_active();
$PAGE->navbar->add(get_string('home'), new moodle_url($CFG->wwwroot . '/my/index.php'));
$PAGE->navbar->add(get_string('pluginname', 'local_completion_tracker'),
	new moodle_url($CFG->wwwroot . '/local/completion_tracker/index.php'));

echo $OUTPUT->header();
echo $OUTPUT->heading($title);

echo $OUTPUT->download_dataformat_selector(get_string('completiontrackerdownload', 'local_completion_tracker'),
	'download.php');

echo $OUTPUT->footer();