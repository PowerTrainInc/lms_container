<?php
/**
 * Version details.
 *
 * @package    tool_structureimport
 * @copyright  2021 PowerTrain Inc {@link https://powertrain.com/}
 * @license    All Rights Reserved
 */
 
 defined('MOODLE_INTERNAL') || die;
 
 if ($hassiteconfig) {
    $ADMIN->add('courses', new admin_externalpage('toolstructureimport', get_string('pluginname', 'tool_structureimport'), "$CFG->wwwroot/$CFG->admin/tool/structureimport/index.php", 'moodle/site:config'));
}