<?php
/**
 * This file contains the customcertpwt plugin functionality.
 *
 * @package    tool_structureimport
 * @copyright  2021 PowerTrain Inc {@link https://powertrain.com/}
 * @license    All Rights Reserved
 */

namespace tool_structureimport\activity;

defined('MOODLE_INTERNAL') || die();

class customcert {
	/**
	 * Constructor
	 * Must contain __construct -- even if empty
	 */
	public function __construct() {

	}
	
	/**
	 * Function returns validate_array for activity
	 * @return	Array
	 */
	public function get_validate_array() : array {
		return array(
			'name'
		);
	}
	
	/**
	 * Function returns required parent node for activity - required settings
	 * Return null if none required
	 * @return 	string Parent name
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
	 * Function creates the customcertpwt activity
	 * @param	array $node_values Array of values that make up activity settings
	 * @param	string $hash Node hash value
	 * @param	array $parent_stack Reference to $parent_stack
	 * @param	array $parent_stack_id Reference to $parent_stack_id
	 * @param	int $parent_id Reference to $parent_id
	 * @param	array $parent_stack_type Reference to $parent_stack_type
	 * @param	array $completed_node_stack Reference to $completed_node_stack
	 */
	public function create_customcertactivity(array $node_values, $hash, &$parent_stack, &$parent_stack_id, 
			&$parent_id, &$parent_stack_type, &$completed_node_stack) : void {
		global $CFG, $DB, $USER;

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

		$module = $DB->get_record('modules', array('name' => 'customcertpwt'), 'id');

		if (!$module) {
			throw new \Exception(get_string('criterrormissingmodule', 'tool_structureimport'));
		}

		$fromform = new \stdClass();
		
		$fromform->name = (string)$node_values['name'];
		$fromform->introeditor = array(
			'text' => (string)(isset($node_values['intro']) ? $node_values['intro'] : ''),
			'format' => '1',
			'itemid' => rand(1,999999999)
		);
		// Begin module specific settings
		$fromform->showdescription = (string)(isset($node_values['showdescription']) ? $node_values['showdescription'] : '0');
		$fromform->deliveryoption = (string)(isset($node_values['deliveryoption']) ? $node_values['deliveryoption'] : 'I');
		$fromform->emailstudents = (string)(isset($node_values['emailstudents']) ? $node_values['emailstudents'] : '0');
		$fromform->emailteachers = (string)(isset($node_values['emailteachers']) ? $node_values['emailteachers'] : '0');
		$fromform->emailothers = (string)(isset($node_values['emailothers']) ? $node_values['emailothers'] : '');
		$fromform->verifyany = (string)(isset($node_values['verifyany']) ? $node_values['verifyany'] : '0');
		$fromform->requiredtime = (int)(isset($node_values['requiredtime']) ? $node_values['requiredtime'] : 0);
		$fromform->protection_print = (int)(isset($node_values['protection_print']) ? $node_values['protection_print'] : '');
		$fromform->protection_modify = (int)(isset($node_values['protection_modify']) ? $node_values['protection_modify'] : '');
		$fromform->visibleoncoursepage = (int)(isset($node_values['visibleoncoursepage']) ? $node_values['visibleoncoursepage'] : 1);
		$fromform->groupmode = (string)(isset($node_values['groupmode']) ? $node_values['groupmode'] : '0');
		$fromform->groupingid = (string)(isset($node_values['groupingid']) ? $node_values['groupingid'] : '0');
		// End module specific settings
		$fromform->visible = (int)(isset($node_values['visible']) ? $node_values['visible'] : 1);
		$fromform->availabilityconditionsjson = (string)(isset($node_values['availabilityconditionsjson']) ? (new \tool_structureimport\core_functionality\structure)->availability_conditions($node_values['availabilityconditionsjson'], $activity_course_id) : base64_decode('eyJvcCI6IiYiLCJjIjpbXSwic2hvd2MiOltdfQ=='));
		$fromform->completionunlocked = (int)(isset($node_values['completionunlocked']) ? $node_values['completionunlocked'] : 1);
		$fromform->completion = (string)(isset($node_values['completion']) ? $node_values['completion'] : '1');
		$fromform->completionexpected = (int)(isset($node_values['completionexpected']) ? $node_values['completionexpected'] : 0);
		$fromform->tags = (array)(isset($node_values['showdescription']) ? $node_values['showdescription'] : array());
		$fromform->course = (int)$parent_id;
		$fromform->coursemodule = (int)(isset($node_values['coursemodule']) ? $node_values['coursemodule'] : 0);
		$fromform->section = $activity_section;
		$fromform->module = $module->id;
		$fromform->modulename = 'customcertpwt';
		$fromform->instance = (int)(isset($module_values['instance']) ? $module_values['instance'] : 0);
		$fromform->add = 'customcertpwt';
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

		// Check if template is set and available
		if (isset($node_values['template']) && $node_values['template'] != '') {
			$cert_template = $DB->get_records('customcertpwt_templates', array('name' => $node_values['template']), '', 'id', 0, 1);
			$template_id = array_key_first($cert_template);

			if (sizeof($cert_template) > 0) {
				$update = new \stdClass;
				
				$update->id = $fromform->instance;
				$update->templateid = $template_id;
				
				// Log update of certificate template
				(new \tool_structureimport\core_functionality\logging)->create($fromform->instance, $USER->id, 
						null, 'certificate_template_updated');

				$DB->update_record('customcertpwt', $update);

				unset($update);
			} else {
				throw new \Exception(get_string('criterrorinvalidtemplate', 'tool_structureimport', array('template' => $node_values['template'])));
			}
			
			unset($cert_template, $template_id);
			
		}

		echo get_string('successactivity', 'tool_structureimport', array('id' => $parent_id, 'name' => $fromform->name)) . '<br>';
		flush();

		unset($fromform, $course, $parent_id);

		$parent_stack_id[$hash] = $module->id;

		array_push($completed_node_stack, $hash);

	}

}