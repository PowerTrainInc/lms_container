<?php
/**
 * This file contains the xml import functionality.
 *
 * @package    tool_structureimport
 * @copyright  2021 PowerTrain Inc {@link https://powertrain.com/}
 * @license    All Rights Reserved
 */

namespace tool_structureimport\xml;

defined('MOODLE_INTERNAL') || die();

require_once('../../../course/lib.php');
require_once('../../../course/modlib.php');

class xml_import {
	private $xml;
	private $xml_filename;
	private $transaction;
	public $parent_id;
	public $parent_stack = array();
	public $parent_stack_id = array();
	private $parent_stack_type = array();
	private $match_stack = array();
	public $completed_node_stack = array();

	/**
     * Constructor.
	 * @param	string $filename Filename for xml file
	 * @param	int $parent_id Selected parent for import
     */
    public function __construct(string $filename, int $parent_id) {
		global $DB;
		
		$this->xml_filename = $filename;
		$this->parent_id = $parent_id;
		
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

		$this->get_hashes();

		// Require transactions for the process
		$this->transaction = $DB->start_delegated_transaction();

	}

	/**
     * Function removes a hash value from a category, course and activity open tag
	 * @param	array $val Array extracted by preg_replace_callback
	 * @return	string Returns tag name without attached hash
     */
	private function decode_open_item(array $val) : string {
		$node_array = explode(':', $val[0]);
		
		$previous_node = end($this->match_stack);
		$this->parent_stack[$node_array[1]] = $previous_node;
		$this->parent_stack_type[$node_array[1]] = $node_array[0];

		array_push($this->match_stack, $node_array[1]);

		unset($previous_node);

		return $node_array[0];
	}
	
	/**
     * Function removes a hash value from a category, course and activity close tag
	 * @param	array $val Array extracted by preg_replace_callback
	 * @return	string Returns tag name without attached hash
     */
	private function decode_close_item(array $val) : string {
		$node_array = explode(':', $val[0]);

		if (sizeof($this->match_stack) > 0) {
			$get_hash = array_pop($this->match_stack);

			$return_value = $val[0] . ':' . $get_hash;
			
			unset($get_hash);
		}
		
		unset($node_array);
		
		return $val[0];
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
	 * Function gets hashes for all qualifying nodes
	 */
	private function get_hashes() : void {
		$xml = file_get_contents($this->xml_filename);

		$xml_array = explode("\n", $xml); // must use double quotes in explode
		$activity_string = $this->get_activities();

		foreach ($xml_array as $key => $val) {
			// Encode opening tags
			$xml_array[$key] = preg_replace_callback(
				'/(?<=\<)((category|course|topic|' . $activity_string . '):[a-z0-9]{32})(?=\>)/mi',
				function ($matches) {
					return $this->decode_open_item($matches);
				},
				$xml_array[$key], 1);
				
			// Encode closing tags
			$xml_array[$key] = preg_replace_callback(
				'/(?<=\<\/)((category|course|topic|' . $activity_string . '):[a-z0-9]{32})(?=\>)/mi',
				function ($matches) {
					return $this->decode_close_item($matches);
				},
				$xml_array[$key], 1);
		}
		
		unset($xml, $xml_array, $key, $val, $activity_string);
	}
	
	/**
	 * Function creates category in Moodle
	 * @param	array $node_values Array of all values for the category
	 * @param	string $hash Category hash value
	 */
	private function create_category(array $node_values, $hash) : void {
		if ($this->parent_stack[$hash] == null) {
			$parent_id = $this->parent_id;
		} else {
			$parent_id = $this->parent_stack_id[$this->parent_stack[$hash]];
		}

		$context = \context_coursecat::instance($parent_id);
		
		$data = new \stdClass();
		
		$data->parent = (string)$parent_id;
		$data->name = $node_values['name'];
		$data->idnumber = (isset($node_values['idnumber'])? $node_values['idnumber'] : '');
		$data->description_editor = array('text' => (isset($node_values['description']) ? $node_values['description'] : ''), 
			'format' => '1', 'itemid' => null);
		$data->id = 0;
		
		$form = array(
			'maxfiles' => -1,
			'maxbytes' => '0',
			'trusttext' => false,
			'noclean' => true,
			'context' => $context,
			'subdirs' => false
		);

		try {
			if ($category = \core_course_category::create($data, $form)) {
				// success
			} else {
				throw new \Exception(get_string('criterrorcategoryfail', 'tool_structureimport'));
			}
		} catch(Exception $e) {
			$this->transaction->rollback($e);
		}

		echo get_string('successcategory', 'tool_structureimport', array('id' => $category->id, 'name' => $data->name)) . '<br>';
		flush();
		
		$this->parent_stack_id[$hash] = $category->id;
		
		array_push($this->completed_node_stack, $hash);
	}
	
	/**
	 * Function creates course in Moodle
	 * @param	array $node_values Array of all values for the course
	 * @param	string $hash course hash value
	 */
	private function create_course(array $node_values, $hash) : void {
		global $CFG;

		if ($this->parent_stack[$hash] == null) {
			$parent_id = $this->parent_id;
		} else {
			$parent_id = $this->parent_stack_id[$this->parent_stack[$hash]];
		}

		$context = \context_coursecat::instance($parent_id);

		$data = new \stdClass();
		$data->fullname = $node_values['fullname'];
		$data->shortname = $node_values['shortname'];
		$data->category = (string)$parent_id;
		$data->visible = (isset($node_values['visible']) ? (string)$node_values['visible'] : '1');
		$data->startdate = (int)$node_values['startdate'];
		$data->enddate = (int)(isset($node_values['enddate']) ? $node_values['enddate'] : 0);
		$data->idnumber = (isset($node_values['courseid']) ? $node_values['courseid'] : ''); // optional
		$data->mform_isexpanded_id_descriptionhdr = 1;
		$data->summary_editor = 
			array(
				'text' => (isset($node_values['summary']) ? $node_values['summary'] : ''),
				'format' => (isset($node_values['summaryformat']) ? (string)$node_values['summaryformat'] : '1'),
				'itemid' => rand(1,999999999)
			);
		$data->overviewfiles_filemanager = rand(1,999999999);
		$data->format = $node_values['format'];
		$data->activitytype = (isset($node_values['activitytype']) ? $node_values['activitytype'] : '');
		$data->addcourseformatoptionshere = (isset($node_values['courseformatoptions']) ? (int)$node_values['courseformatoptions'] : 0);
		$data->lang = (isset($node_values['lang']) ? $node_values['lang'] : $CFG->lang);
		$data->showgrades = (isset($node_values['showgrades']) ? (string)$node_values['showgrades'] : '1');
		$data->showreports = (isset($node_values['showreports']) ? (string)$node_values['showreports'] : '0');
		$data->maxbytes = (isset($node_values['maxbytes']) ? (string)$node_values['maxbytes'] : '0');
		$data->enablecompletion = (isset($node_values['enablecompletion']) ? (string)$node_values['enablecompletion'] : '1');
		$data->groupmode = (isset($node_values['groupmode']) ? (string)$node_values['groupmode'] : '0');
		$data->groupmodeenforce = (isset($node_values['groupmodeenforce']) ? (string)$node_values['groupmodeenforce'] : '1');
		$data->defaultgroupingid = (isset($node_values['defaultgroupingid']) ? (string)$node_values['defaultgroupingid'] : '0');
		$data->role_1 = (isset($node_values['role_1']) ? (string)$node_values['role_1'] : '');
		$data->role_2 = (isset($node_values['role_2']) ? (string)$node_values['role_2'] : '');
		$data->role_3 = (isset($node_values['role_3']) ? (string)$node_values['role_3'] : '');
		$data->role_4 = (isset($node_values['role_4']) ? (string)$node_values['role_4'] : '');
		$data->role_5 = (isset($node_values['role_5']) ? (string)$node_values['role_5'] : '');
		$data->role_6 = (isset($node_values['role_6']) ? (string)$node_values['role_6'] : '');
		$data->role_7 = (isset($node_values['role_7']) ? (string)$node_values['role_7'] : '');
		$data->role_8 = (isset($node_values['role_8']) ? (string)$node_values['role_8'] : '');
		$data->tags = array();
		$data->id = 0;
		
		try {
			if ($course = create_course($data)) {
				// success
			} else {
				throw new \Exception(get_string('criterrorcoursefail', 'tool_structureimport'));
			}
		} catch(Exception $e) {
			$this->transaction->rollback($e);
		}
		
		echo get_string('successcourse', 'tool_structureimport', array('id' => $course->id, 'name' => $data->fullname)) . '<br>';
		flush();
		
		$this->parent_stack_id[$hash] = $course->id;
		
		array_push($this->completed_node_stack, $hash);
	}
	
	/**
	 * Function creates course topic in Moodle
	 * @param	array $node_values Array of all values for the topic
	 * @param	string $hash topic hash value
	 */
	private function create_topic(array $node_values, $hash) : void {
		global $CFG, $DB;

		if ($this->parent_stack[$hash] == null) {
			$parent_id = $this->parent_id;
		} else {
			$parent_id = $this->parent_stack_id[$this->parent_stack[$hash]];
		}

		$course = $DB->get_record('course', array('id' => $parent_id), '*', MUST_EXIST);
		$count = $DB->count_records('course_sections', array('course' => $course->id));
		
		if ($count == 1) {
			course_create_sections_if_missing($course, 1);
			
			$first_section = $DB->get_record('course_sections', array('course' => $course->id, 'section' => 1), 'id,name');

			$section_id = $first_section->id;
			$section_name = $first_section->name;
			
			unset($first_section);
		} else if ($count > 1) {
			$next_section = course_create_section($course, 0);	
			
			$section_id = $next_section->id;
			$section_name = $next_section->name;
			
			unset($next_section);
		}
		
		if (isset($node_values['name']) && $node_values['name'] != '') {
			$section = $DB->get_record('course_sections', array('course' => $course->id, 'id' => $section_id), '*');
			
			$data = new \stdClass;
			
			$data->name = $node_values['name'];
			$data->summary = (isset($node_values['summary']) ? $node_values['summary'] : '');
			$data->summarytrust = (int)(isset($node_values['summarytrust']) ? $node_values['summarytrust'] : 0);
			$data->summaryformat = (string)(isset($node_values['summaryformat']) ? $node_values['summaryformat'] : '1');
			$data->availabilityconditionsjson = (isset($node_values['availabilityconditionsjson']) ? (string)base64_decode($node_values['availabilityconditionsjson']) : (string)base64_decode('eyJvcCI6IiYiLCJjIjpbXSwic2hvd2MiOltdfQ=='));
			$section_name = $node_values['name'];
			
			course_update_section($course, $section, $data);
		}
		
		if ($section_name == '') {
			$section_name = get_string('none', 'tool_structureimport');
		}
		
		echo get_string('successtopic', 'tool_structureimport', array('id' => $section_id, 'name' => $section_name)) . '<br>';
		flush();
		
		array_push($this->completed_node_stack, $hash);
		
		$this->parent_stack_id[$hash] = $section_id;

		unset($section_id, $section_name);
	}
	
	/**
	 * Function checks if node has been completed
	 * @param	string $hash Hash identifier for the node
	 * @return	bool
	 */
	private function node_complete($hash) : bool {
		$return_val = false;

		if (in_array($hash, $this->completed_node_stack)) {
			$return_val = true;
		}
		
		return $return_val;
	}
	
	/**
	 * Import xml structure into database
	 */
	public function import_structure() : void {
		global $CFG;
		
		$mform = new \tool_structureimport\forms\import_done($CFG->wwwroot . '/admin/search.php');
		
		$object_stack = array();
		$parent_stack = array();

		// Get parent node(s)
		$firstpass = true;
		$parent_node = '';

		foreach ($this->xml as $key => $val) {
			if ($firstpass === true) {
				$parent_node = $key;
				$firstpass = false;
			}

			// check for node with hash
			if (strpos($key, ':')) {
				$node_array = explode(':', $key);
				$this->parent_stack_id[$node_array[1]] = $this->parent_id;; // set default ID for node
			}

			array_push($parent_stack, $parent_node);
			array_push($object_stack, array($key, $val));
		}
		
		unset($key, $val, $node_array, $firstpass);

		$firstpass = true;
		
		// Dig through full structure and verify required child nodes
		while (sizeof($object_stack) > 0 || $firstpass === true) {
			$firstpass = false;

			foreach ($object_stack as $key => $val) {
				// check for node with hash
				if (strpos($val[0], ':')) {
					$node_array = explode(':', $val[0]);
					$this->node_id_stack[$node_array[1]] = 0; // set default ID for node
				}
				
				$node_values = array();
				
				foreach ($val[1] as $key2 => $val2) {
					$node_values[trim((string)$key2)] = trim((string)$val2);

					array_push($object_stack, array($key2, $val2));
				}
				
				unset($key2, $val2);

				switch(trim(strtolower($node_array[0]))) {
					case 'category':
						if (!$this->node_complete($node_array[1])) {
							$this->create_category($node_values, $node_array[1]);
						}

					break;
					case 'course':
						if (!$this->node_complete($node_array[1])) {
							$parent_node = $this->parent_stack[$node_array[1]];
							$parent_type = $this->parent_stack_type[$parent_node];

							if (substr($parent_type, -8) != 'activity') {
								$this->create_course($node_values, $node_array[1]);
							}
							
							unset($parent_node, $parent_type);
						}
					
					break;
					case 'topic':
						if (!$this->node_complete($node_array[1])) {
							$this->create_topic($node_values, $node_array[1]);
						}
					
					break;
					default:
						// Make sure this is an activity tag ending in "activity"
						// ex: facetofaceactivity
						if (substr(strtolower($node_array[0]), -8) == 'activity' && strtolower($node_array[0]) != 'activity') {
							$activity_name = substr($node_array[0], 0, strlen($node_array[0]) - 8);
							$path_to_activity = '\tool_structureimport\activity\\' . $activity_name;
					
							// Check activity type exists in importer
							if (!method_exists('\tool_structureimport\activity\\' . $activity_name, 'get_validate_array')) {
								throw new \file_exception(get_string('critererorinvalidactivitytype', 'tool_structureimport', array('activity' => $activity_name)));
							}
							
							$activity_data = new $path_to_activity();
							
							if (!$this->node_complete($node_array[1])) {
								$activity_function = 'create_' . $activity_name . 'activity';
								
								$activity_data->$activity_function($node_values, $node_array[1], 
									$this->parent_stack, $this->parent_stack_id, $this->parent_id,
									$this->parent_stack_type, $this->completed_node_stack);
									
								unset($activity_function);
							}
						}
					break;
					
				}

				$previous_node = $node_array[0];
				
				unset($object_stack[$key], $node_values);
			}

			unset($key, $val);
			
		}
		
		unset($firstpass, $object_stack, $parent_node, $parent_stack, $previous_node);
	
		// Submit transactions
		$this->transaction->allow_commit();
		
		echo '<br>' . get_string('importdone', 'tool_structureimport') . '<br>';
		
		$mform->display();
	}

}