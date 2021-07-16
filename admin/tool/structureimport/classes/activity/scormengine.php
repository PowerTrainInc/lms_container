<?php
/**
 * This file contains the scormengine plugin functionality.
 *
 * @package    tool_structureimport
 * @copyright  2021 PowerTrain Inc {@link https://powertrain.com/}
 * @license    All Rights Reserved
 */

namespace tool_structureimport\activity;

defined('MOODLE_INTERNAL') || die();

class scormengine {
	/**
	 * Constructor
	 * Must contain __construct -- even if empty
	 */
	public function __construct() {
		
	}
	
	/**
	 * Function returns validate_array for activity - required settings
	 * @return	Array
	 */
	public function get_validate_array() : array {
		return array(
			'name'
		);
	}
	
	/**
	 * Function returns required parent node for activity
	 * Return null if none required
	 * @return 	string|array Parent name
	 */
	public function get_parent_node_req() : array {
		return array('course', 'topic');
	}
	
	/**
	 * Function returns parent node name if exempt
	 * Return empty string if not exempt
	 * @return	string Parent name
	 */
	public function get_parent_node_exempt() : string {
		return '';
	}
	
	/**
	 * Function returns exempt list
	 * Return empty string if no exempt list
	 * @return	string Exempt list
	 */
	public function get_parent_node_exempt_list() : string {
		return '';
	}

	/**
	 * Function creates the scormengine activity
	 * @param	array $node_values Array of values that make up activity settings
	 * @param	string $hash Node hash value
	 * @param	array $parent_stack Reference to $parent_stack
	 * @param	array $parent_stack_id Reference to $parent_stack_id
	 * @param	int $parent_id Reference to $parent_id
	 * @param	array $parent_stack_type Reference to $parent_stack_type
	 * @param	array $completed_node_stack Reference to $completed_node_stack
	 */
	public function create_scormengineactivity(array $node_values, $hash, &$parent_stack, &$parent_stack_id, 
			&$parent_id, &$parent_stack_type, &$completed_node_stack) : void {
		global $CFG, $DB;
		
		$activity_course_id = 0;
		
		if ($parent_stack_type[$parent_stack[$hash]] == 'topic') {
			$section = $DB->get_record('course_sections', array('id' => $parent_stack_id[$parent_stack[$hash]]), 'course,section');
			$activity_course_id = $section->course;
		}
		
		if ($activity_course_id == 0) {
			$activity_course_id = $parent_stack_id[$parent_stack[$hash]];
		}
	
		// Assign activity section
		$activity_section = 0;
		
		if (isset($node_values['section']) && $node_values['section'] != '') {
			$activity_section = (int)$node_values['section'];
		}
		
		// Override section if activity is in a topic
		if (isset($section) && $section->section) {
			$activity_section = $section->section;
		}

		if ($parent_stack[$hash] == null) {
			$parent_id = $parent_id;
		} else {
			$parent_id = $parent_stack_id[$parent_stack[$hash]];
		}

		$module = $DB->get_record('modules', array('name' => 'scormengine'), 'id');

		if (!$module) {
			throw new \Exception(get_string('criterrormissingmodule', 'tool_structureimport'));
		}

		$fromform = new \stdClass();
		
		$fromform->name = (string)$node_values['name'];
		$fromform->introeditor = array(
			'text' => (string)(isset($node_values['text']) ? $node_values['text'] : ''),
			'format' => '1',
			'itemid' => rand(1,999999999)
		);
		$fromform->package_id = (string)(isset($node_values['package_id']) ? $node_values['package_id'] : '');
		$fromform->visible = (int)(isset($node_values['visible']) ? $node_values['visible'] : 1);
		$fromform->visibleoncoursepage = (int)(isset($node_values['visibleoncoursepage']) ? $node_values['visibleoncoursepage'] : 1);
		$fromform->availabilityconditionsjson = (string)(isset($node_values['availabilityconditionsjson']) ? (new \tool_structureimport\core_functionality\structure)->availability_conditions($node_values['availabilityconditionsjson'], $activity_course_id) : base64_decode('eyJvcCI6IiYiLCJjIjpbXSwic2hvd2MiOltdfQ=='));
		$fromform->cmidnumber = (string)(isset($node_values['cmidnumber']) ? $node_values['cmidnumber'] : '');
		$fromform->completionunlocked = (int)(isset($node_values['completionunlocked']) ? $node_values['completionunlocked'] : 1);
		$fromform->completion = (string)(isset($node_values['completion']) ? $node_values['completion'] : '1');
		$fromform->completionexpected = (int)(isset($node_values['completionexpected']) ? $node_values['completionexpected'] : 0);
		$fromform->tags = (array)(isset($node_values['tags']) ? $node_values['tags'] : array());
		$fromform->course = (int)$parent_id;
		$fromform->coursemodule = (int)(isset($node_values['coursemodule']) ? $node_values['coursemodule'] : 0);
		$fromform->section = $activity_section;
		$fromform->module = $module->id;
		$fromform->modulename = 'scormengine';
		$fromform->instance = (int)(isset($module_values['instance']) ? $module_values['instance'] : 0);
		$fromform->add = 'scormengine';
		$fromform->update = 0;
		$fromform->return = 0;
		$fromform->sr = 0;
		$fromform->competencies = (array)(isset($module_values['competencies']) ? $module_values['competencies'] : array());
		$fromform->competency_rule = (string)(isset($module_values['competency_rule']) ? $module_values['competency_rule'] : '0');
		
		unset($activity_section, $activity_course_id);
		
		// Is this a section/topic or a course?
		if (isset($section)) {
			$course = $DB->get_record('course', array('id' => $section->course), '*');
		} else {
			$course = $DB->get_record('course', array('id' => $parent_id), '*');
		}

		if (!$course) {
			throw new \Exception(get_string('criterrormissingcourse', 'tool_structureimport'));
		}

		$moduleinfo = add_moduleinfo($fromform, $course);

		if (!$moduleinfo) {
			throw new \Exception(get_string('criterroractivitycreate', 'tool_structureimport'));
		}

		echo get_string('successactivity', 'tool_structureimport', array('id' => $parent_id, 'name' => $fromform->name)) . '<br>';
		flush();

		unset($fromform, $course, $parent_id);

		$parent_stack_id[$hash] = $module->id;

		array_push($completed_node_stack, $hash);

	}

}