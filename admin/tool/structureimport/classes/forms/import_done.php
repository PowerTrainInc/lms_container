<?php
/**
 * This file contains the import done form.
 *
 * @package    tool_structureimport
 * @copyright  2021 PowerTrain Inc {@link https://powertrain.com/}
 * @license    All Rights Reserved
 */
 
namespace tool_structureimport\forms;

defined('MOODLE_INTERNAL') || die();

class import_done extends \moodleform {
	 /**
     * Form definiton.
     */
	function definition() {
		global $CFG;
		
		$mform =& $this->_form;
		
		$button_array[] = $mform->createElement('submit', 'submitbutton_done', get_string('importdonebutton', 'tool_structureimport'));
		$mform->addGroup($button_array, 'buttonar', '', ' ', false);

	}
	
}