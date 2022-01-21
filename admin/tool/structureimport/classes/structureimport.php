<?php
/**
 * This file contains the core structureimport functionality.
 *
 * @package    tool_structureimport
 * @copyright  2021 PowerTrain Inc {@link https://powertrain.com/}
 * @license    All Rights Reserved
 */

namespace tool_structureimport;

defined('MOODLE_INTERNAL') || die();

class structureimport {
	/**
     * Constructor.
     */
    public function __construct() {

	}
	
	/**
	 * Function displays and processes main forms\plugin_forms
	 */
	public function structure_select() {
		global $CFG;
		
		$mform = new \tool_structureimport\forms\plugin_forms();
		
		// If cancel button click, redirect to admin search page
		if ($mform->is_cancelled()) {
			redirect($CFG->wwwroot . '/admin/search.php');
		// Process submitted data
		} else if ($fromform = $mform->get_data()) {
			
			// Check if file exists
			if (!$content = $mform->get_new_filename('structure_file')) {
				// No upload - how did we get here?
				throw new \coding_exception(get_string('criterrormissingfile', 'tool_structureimport'));
			}
			
			if (!is_writable($CFG->dataroot . '/temp/filestorage/')) {
				throw new \Exception(get_string('criterrorstoragefail', 'tool_structureimport', 
						array('path' => $CFG->dataroot . '/temp/filestorage/')));	
			}

			$xml_file = $CFG->dataroot . '/temp/filestorage/' . $fromform->structure_file . '.xml';
			$mform->save_file('structure_file', $xml_file, true);
			
			// Run file through validator
			$validate = new \tool_structureimport\xml\xml_validate($xml_file);
			
			$validate->verify_nodes();
			
			// Encode XML for import
			$encode = new \tool_structureimport\xml\xml_encode($xml_file);
			
			$encode->process();
			
			// Run file through importer
			$import = new \tool_structureimport\xml\xml_import($xml_file, $fromform->structure_select);
			
			$import->import_structure();

		// Display Form
		} else {
			$mform->display();
		}
	
	}

}