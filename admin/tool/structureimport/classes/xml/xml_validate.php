<?php
/**
 * This file contains the xml validation functionality.
 *
 * @package    tool_structureimport
 * @copyright  2021 PowerTrain Inc {@link https://powertrain.com/}
 * @license    All Rights Reserved
 */

namespace tool_structureimport\xml;

defined('MOODLE_INTERNAL') || die();

class xml_validate {
	private $xml;
	private $xml_filename;
	
	/**
     * Constructor.
	 * @param	string $filename Filename for xml file
     */
    public function __construct($filename) {
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

	}
	
	/**
     * Function validates a node has all of the required child nodes
	 * @param	stdClass $xml XML object to inspect
	 * @param	array $must_contain Array of child nodes a node must contains
	 * @param	string $node Name of the node being checked
	 * @param	string $parent_node Parent node for node being processed
	 * @param	string|array $parent_node_req Required parent node for node being processed
	 * @param	string $parent_node_exempt Name of parent node exempt from dependency checks
	 * @param	array $parent_node_exempt_list List of parent nodes exempt from dependency checks
     */
	private function node_must_contain($xml, array $must_contain, $node, $parent_node = null, 
			$parent_node_req = null, $parent_node_exempt = null, $parent_node_exempt_list = null) : void {
		// If node had a required parent node and required parend node doesn't match -- throw error
		if ($parent_node && $parent_node_req && ($parent_node != $parent_node_req && !in_array($parent_node, $parent_node_req))) {
			throw new \file_exception(get_string('nodeinvalidparent', 'tool_structureimport', array('node' => $node, 'parent' => $parent_node_req)));
		}

		// Check node dependency if the parent node is not an excempt node
		foreach ($must_contain as $val) {
			if (!$parent_node_exempt && !$parent_node_exempt_list || (!in_array($parent_node_exempt, $parent_node_exempt_list) && 
					(substr($parent_node_exempt, -8) == 'activity' && !in_array(substr($parent_node_exempt, -8), $parent_node_exempt_list )))) {
				if (!property_exists($xml, $val)) {
					throw new \file_exception(get_string('nodemissingelement', 'tool_structureimport', array('node' => $node, 'element' => $val)));
				}
			}
		}
	
	}

	/**
     * Function Inspect individual nodes
	 * @param	string $node Name of the node being checked
	 * @param	stdClass $xml XML object to inspect
     */	
	private function node_inspect($node, $xml, $previous_node = null) : void {
		switch(strtolower($node)) {
			case 'category':
				$this->node_must_contain($xml, 
					array(
						'name'
					), 
				$node);
			break;
			case 'course':
				$this->node_must_contain($xml, 
					array(
						'fullname',
						'shortname',
						'summary',
						'format',
						'startdate'
					), 
				$node, null, null, $previous_node, array('activity'));
			break;
			case 'activity':
				$this->node_must_contain($xml, array(), $node, $previous_node, 'course');
			break;
			case 'topic':
				$this->node_must_contain($xml, array(), $node, $previous_node, 'course');
			break;
			default:
				// Make sure this is an activity tag ending in "activity"
				// ex: facetofaceactivity
				if (substr(strtolower($node), -8) == 'activity' && strtolower($node) != 'activity') {
					$activity_name = substr($node, 0, strlen($node) - 8);
					$path_to_activity = '\tool_structureimport\activity\\' . $activity_name;
					
					// Check activity type exists in importer
					if (!method_exists('\tool_structureimport\activity\\' . $activity_name, 'get_validate_array')) {
						throw new \file_exception(get_string('criterrorinvalidactivitytype', 'tool_structureimport', array('activity' => $activity_name)));
					}

					$activity_data = new $path_to_activity();

					$this->node_must_contain($xml, $activity_data->get_validate_array(), $node, $previous_node, 
							$activity_data->get_parent_node_req(), $activity_data->get_parent_node_exempt(),
							$activity_data->get_parent_node_exempt_list());
							
					unset($activity_name, $path_to_activity, $activity_data);
				}
			break;
		}

	}

	/**
     * Function to inspect all nodes in an XML structure
	 */
	public function verify_nodes() : void {
		$object_stack = array();
		
		// Get parent node(s)
		$firstpass = true;
		$parent_node = '';
		
		foreach ($this->xml as $key => $val) {
			if ($firstpass === true) {
				$parent_node = $key;
				$firstpass = false;
			}
			
			$this->node_inspect($key, $this->xml->$key);
			array_push($object_stack, array($parent_node, $val));
		}
		
		$firstpass = true;
		
		// Dig through full structure and verify required child nodes
		$dot_counter = 0;
		$dot_counter2 = 0;

		while (sizeof($object_stack) > 0 || $firstpass === true) {
			$firstpass = false;
			
			foreach ($object_stack as $key => $val) {
				foreach ($val[1] as $key2 => $val2) {
					$this->node_inspect($key2, $val2, $val[0]);
					array_push($object_stack, array($key2, $val2));
										
					$dot_counter++;
					
					if ($dot_counter == 200) {
						echo '.';
						flush();
						
						$dot_counter = 0;
						
						$dot_counter2++;
						
						if ($dot_counter2 == 200) {
							echo '<br>';
							flush();
							
							$dot_counter2 = 0;
						}
					}
				}
				
				$parent_node = $key;
				unset($object_stack[$key], $key2, $val2);
			}
			unset($key, $val);
		}
		
		unset($firstpass, $object_stack, $parent_node, $dot_counter, $dot_counter2);
	}
	
}