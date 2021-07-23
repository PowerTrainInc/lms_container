<?php
/**
 * This file contains the xml encoding functionality.
 *
 * @package    tool_structureimport
 * @copyright  2021 PowerTrain Inc {@link https://powertrain.com/}
 * @license    All Rights Reserved
 */

namespace tool_structureimport\xml;

defined('MOODLE_INTERNAL') || die();

class xml_encode {
	private $xml;
	private $xml_filename;
	private $hash_stack = array();
	private $match_stack = array();
	
	/**
     * Constructor.
	 * @param	string $filename Filename for xml file
     */
    public function __construct($filename) {
		global $CFG;
		
		$this->xml_filename = $filename;
		
		libxml_use_internal_errors(true);
		$this->xml = simplexml_load_file($filename);
			
		if (!$this->xml) {
			$error_string = '';
				
			foreach (libxml_get_errors() as $error) {
				if ($error_string != '') {
					$error_string .= ', ';
				}
					
				$error_string .= $error->message;
			}
				
			libxml_clear_errors();
				
			throw new \file_exception($error_string);
		}

		$this->match_stack['category'] = array();
		$this->match_stack['course'] = array();
		$this->match_stack['topic'] = array();
		
		$path_to_activities = $CFG->dirroot . '/admin/tool/structureimport/classes/activity';
		
		$files = scandir($path_to_activities);
		
		foreach ($files as $val) {
			if (substr($val, -4) == '.php') {
				$activity_method = substr($val, 0, strlen($val) - 4);
				
				if (method_exists('\tool_structureimport\activity\\' . $activity_method, '__construct')) {
					$this->match_stack[$activity_method . 'activity'] = array();
				}
				
				unset($activity_method);
			}
		}
		
		unset($path_to_activities, $files, $val);

	}

	/**
     * Function adds a hash value to a category, course and activity open tag
	 * @param	array $val Array extracted by preg_replace_callback
	 * @return	string Returns tag name with attached hash
     */
	private function encode_open_item(array $val) : string {
		$looping = true;
	
		while ($looping === true) {
			$get_hash = md5(time() + rand(0,9999999999));
			if (!in_array($get_hash, $this->hash_stack)) {
				array_push($this->hash_stack, $get_hash);
				array_push($this->match_stack[$val[0]], $get_hash);
				$looping = false;
			}

		}

		unset($looping);
	
		return $val[0] . ':' . $get_hash;
	}
	
	/**
     * Function adds a hash value to a category, course and activity close tag
	 * @param	array $val Array extracted by preg_replace_callback
	 * @return	string Returns tag name with attached hash
     */
	private function encode_close_item(array $val) : string {
		$return_value = '';

		if (sizeof($this->match_stack[$val[0]]) > 0) {
			$get_hash = array_pop($this->match_stack[$val[0]]);
			array_push($this->hash_stack, $get_hash);
			$return_value = $val[0] . ':' . $get_hash;
			
			unset($get_hash);
		} else {
			// do nothing if the stack is empty
			$return_value = $val[0];
		}
		
		return $return_value;
	}
	
	/**
	 * Function gets list of activites
	 * @return String of activities
	 */
	private function get_activities() : string {
		global $CFG;
		
		$activity_stack = array();
		$path_to_activities = $CFG->dirroot . '/admin/tool/structureimport/classes/activity';
		
		$files = scandir($path_to_activities);
		
		foreach ($files as $val) {
			if (substr($val, -4) == '.php') {
				$activity_method = substr($val, 0, strlen($val) - 4);
				
				if (method_exists('\tool_structureimport\activity\\' . $activity_method, '__construct')) {
					array_push($activity_stack, $activity_method . 'activity');
				}
				
				unset($activity_method);
			}
		}
		
		unset($files, $path_to_activities, $val, $activity_method);
		
		return implode('|', $activity_stack);
	}
	
	/**
	 * Function proceses xml file and attaches hashes where appropriate
	 */
	public function process() : void {
		$xml = file_get_contents($this->xml_filename);

		$xml_array = explode("\n", $xml); // must use double quotes in explode
		$activity_string = $this->get_activities();

		$dot_counter = 0;
		$dot_counter2 = 0;
		echo '<br>';

		foreach ($xml_array as $key => $val) {
			// Encode opening tags
			$xml_array[$key] = preg_replace_callback(
				'/(?<=\<)(category|course|topic|' . $activity_string . ')(?=\>)/mi',
				function ($matches) {
					return $this->encode_open_item($matches);
				},
				$xml_array[$key], 1);
			
			// Encode closing tags
			$xml_array[$key] = preg_replace_callback(
				'/(?<=\<\/)(category|course|topic|' . $activity_string . ')(?=\>)/mi',
				function ($matches) {
					return $this->encode_close_item($matches);
				},
				$xml_array[$key], 1);
				
			$dot_counter++;
				
			if ($dot_counter == 500) {
				echo '+';
				flush();
						
				$dot_counter = 0;
						
				$dot_counter2++;
						
				if ($dot_counter2 == 70) {
					echo '<br>';
					flush();
							
					$dot_counter2 = 0;
				}
			}

		}
		
		$xml_temp = implode("\n", $xml_array); // must use double quotes in implode
		
		$xml = $xml_temp;
		
		unset($xml_temp, $xml_array, $key, $val, $activity_string, $dot_counter, $dot_counter2);
		
		echo '<br>';
		
		file_put_contents($this->xml_filename, $xml);
	}
}