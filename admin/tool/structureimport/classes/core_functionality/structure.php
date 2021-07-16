<?php
/**
 * This file contains the core structureimport functionality.
 *
 * @package    tool_structureimport
 * @copyright  2021 PowerTrain Inc {@link https://powertrain.com/}
 * @license    All Rights Reserved
 */

namespace tool_structureimport\core_functionality;

defined('MOODLE_INTERNAL') || die();

class structure {
	private $structure_select_array = array();
	
	/**
     * Constructor.
     */
    public function __construct() {

	}
	
	/**
	 * Generates course category structure for Moodle form
	 * @param string	$key Individual entry in course category structure array
	 */
	private function structure_output($key) : void {
		$temp_array = explode('|', $key);
		$this->structure_select_array[$temp_array[0]] = str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', $temp_array[1]) . base64_decode($temp_array[2]);
		unset($temp_array);
	}
	
	/**
	 * Generate course category structure
	 * @return	array Return an array of course categories
	 */
	public function structure_select() : array {
		global $DB;

		$structure_array = array();

		$entries = $DB->get_records('course_categories', null, 'depth ASC, sortorder ASC, id', 'id, name, sortorder, parent, visible, depth');
	
		foreach($entries as $val) {
			// Creates first part of course category entry
			$structure_array[$val->id] = 'name=' . urlencode($val->name) . '&id=' . $val->id . '&depth=' . $val->depth . '&parentstack[]=' . $val->id;
			
			// Add to parent stack
			if ($val->parent != '0') {
				$structure_array[$val->id] .= '&parentstack[]=' . $val->parent;
			}
			
			// If this is not a top level category then build out child(ren) structure
			if ($val->depth > 1) {
				$parent_exists = true;
				$parent_id = $val->parent;
				
				while($parent_exists) {
					$child = $DB->get_record('course_categories', array('id' => $parent_id), 'parent');
					
					if ($child) {
						$structure_array[$val->id] .= '&parentstack[]=' . $child->parent;
						$parent_id = $child->parent;
						
					} else {
						$parent_exists = false;
					}
					
					unset($child);
				}
				
				unset($parent_exists, $parent_id);
			} else {
				$structure_array[$val->id] .= '&parentstack[]=0';
			}
		}
		
		unset($entries, $val);

		$final_structure[0]['entry'] = '0|0|' . base64_encode('Top'); // Set Top as a placeholder for 0 value parent
		
		// Convert structure strings in array into a useable multidimensional array
		foreach($structure_array as $val) {
			parse_str($val, $output);
			
			$output['parentstack'] = array_reverse($output['parentstack'], true);
			$tempkey = 'tempstructure';
			
			foreach($output['parentstack'] as $key2 => $val2) {
				$tempkey .= '[' . $val2 . ']';
			}
			
			$tempkey .= '[entry]=' . $output['id'] . '|' . $output['depth'] . '|' . base64_encode($output['name']);
			
			parse_str($tempkey, $tempoutput);
			
			$final_structure = array_replace_recursive($final_structure, $tempoutput['tempstructure']);
			
			unset($output, $tempkey, $tempoutput);
		}
		
		// Generate output for Moodle form
		array_walk_recursive($final_structure, array($this, 'structure_output'));

		unset($parent, $val, $structure_array, $final_structure);

		return $this->structure_select_array;
	}
	
	private function get_module_id_from_name($name, $course_id) : int {
		$activities = get_array_of_activities($course_id);
		$return_val = 0;
		
		foreach ($activities as $key => $val) {
			if ($name == $val->name) {
				$return_val = $key;
				
				break;
			}
		}
		
		unset($activities, $key, $val);
		
		return($return_val);
	}
	
	public function availability_conditions($data, $course_id) {
		$json = base64_decode($data);
		$json_array = json_decode($json, true);
		
		$loopcounter = 0;

		while ($loopcounter < sizeof($json_array['c'])) {
			if (is_string($json_array['c'][$loopcounter]['cm'])) {
				$cm = $this->get_module_id_from_name($json_array['c'][$loopcounter]['cm'], $course_id);
				
				// Any number greater than 0 is an id for a valid activity name
				if ($cm > 0) {
					$json_array['c'][$loopcounter]['cm'] = $cm;
				} else {
					// Throw error. Invalid activity name provided.
					throw new \Exception(get_string('criterrorinvalidactivityname', 'tool_structureimport'));
				}
				
				unset($cm);
			} else if (is_numeric($json_array['c'][$loopcounter]['cm'])) {
				if (is_numeric($json_array['c'][$loopcounter]['cm']) != -1) {
					// Throw error.  cannot be anything but -1
					throw new \Exception(get_string('criterrorinvalidactivitynum', 'tool_structureimport'));
				}
				
			}
			
			$loopcounter++;
		}
		
		unset($loopcounter, $json);

		return json_encode($json_array);
	}
}