<?php
/**
 * This file contains the main plugin form.
 *
 * @package    tool_structureimport
 * @copyright  2021 PowerTrain Inc {@link https://powertrain.com/}
 * @license    All Rights Reserved
 */
 
namespace tool_structureimport\forms;

defined('MOODLE_INTERNAL') || die();

class plugin_forms extends \moodleform {
	 /**
     * Form definiton.
     */
	function definition() : void {
		global $CFG;
		
		$mform =& $this->_form;
		$structure = new \tool_structureimport\core_functionality\structure;
		
		$mform->addElement(
			'select', 
			'structure_select', 
			get_string('structureinsertpoint', 'tool_structureimport'), 
			$structure->structure_select(), 
			array('size' => '5')
		);
		$mform->addRule(
			'structure_select', 
			get_string('selectacategoryerror', 'tool_structureimport'), 
			'required', 
			null, 
			'server'
		);
		
		$upload_max_size = ini_get('upload_max_filesize');
		$mform->addElement(
			'filepicker', 
			'structure_file', 
			get_string('structurefileselect', 'tool_structureimport'), 
			null, 
			array('maxbytes' => $upload_max_size, 'accepted_types' => array('.xml'))
		);
		$mform->addRule(
			'structure_file', 
			get_string('selectafileerror', 'tool_structureimport'), 
			'required', 
			null, 
			'server'
		);

		$button_array[] = $mform->createElement('submit', 'submitbutton', get_string('uploadfile', 'tool_structureimport'));
		$button_array[] = $mform->createElement('cancel', 'cancel');
		$mform->addGroup($button_array, 'buttonar', '', ' ', false);
		
		unset($button_array, $upload_max_size);
    }

	/**
     * Form input validation.
     * @param array $data array of ("fieldname"=>value) of submitted data
     * @param array $files array of uploaded files "element_name"=>tmp_file_path
     * @return array of "element_name"=>"error_description" if there are errors,
     *         or an empty array if everything is OK (true allowed for backwards compatibility too).
     */	
	function validation($data, $files) : array {
		$errors = parent::validation($data, $files);
		
		return $errors;
	}

}