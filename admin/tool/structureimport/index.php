<?php
/**
 * Version details.
 *
 * @package    tool_structureimport
 * @copyright  2021 PowerTrain Inc {@link https://powertrain.com/}
 * @license    All Rights Reserved
 */
 
define('NO_OUTPUT_BUFFERING', true);

require('../../../config.php');
require_once($CFG->libdir.'/adminlib.php');

require_admin();

admin_externalpage_setup('toolstructureimport');

$core = new tool_structureimport\structureimport();

echo $OUTPUT->header();
$title = get_string('pluginname', 'tool_structureimport');
echo $OUTPUT->heading($title);

$core->structure_select();

echo $OUTPUT->footer();