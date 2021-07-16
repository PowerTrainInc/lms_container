<?php
/**
 * This file contains the facetoface plugin functionality.
 *
 * @package    tool_structureimport
 * @copyright  2021 PowerTrain Inc {@link https://powertrain.com/}
 * @license    All Rights Reserved
 */

namespace tool_structureimport\activity;

defined('MOODLE_INTERNAL') || die();

class facetoface {
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
	 * Function returns a decoded default value for a facetoface activity field
	 * @param	string $field Activity field name
	 * @return	string Return base64_decode value
	 */
	private function facetofaceactivity_defaults($field) : string {
		$defaults = array(
			'requestsubject' => '
				Q291cnNlIGJvb2tpbmcgcmVxdWVzdDogW2ZhY2V0b2ZhY2VuYW1lXSwgW3N0YXJ0dGltZV0tW2ZpbmlzaHRpbWVd
			',
			'requestmessage' => '
				WW91ciByZXF1ZXN0IHRvIGJvb2sgaW50byB0aGUgZm9sbG93aW5nIGNvdXJzZSBoYXMgYmVlbiBzZW50IHRvIHlv
				dXIgbWFuYWdlcjoKClBhcnRpY2lwYW50OiAgIFtmaXJzdG5hbWVdIFtsYXN0bmFtZV0KQ291cnNlOiAgIFtmYWNl
				dG9mYWNlbmFtZV0KQ29zdDogICBbY29zdF0KCkR1cmF0aW9uOiAgIFtkdXJhdGlvbl0KRGF0ZShzKToKW2FsbGRh
				dGVzXQoKTG9jYXRpb246ICAgW3Nlc3Npb246bG9jYXRpb25dClZlbnVlOiAgIFtzZXNzaW9uOnZlbnVlXQpSb29t
				OiAgIFtzZXNzaW9uOnJvb21d
			',
			'requestinstrmngr' => '
				VGhpcyBpcyB0byBhZHZpc2UgdGhhdCBbZmlyc3RuYW1lXSBbbGFzdG5hbWVdIGhhcyByZXF1ZXN0ZWQgdG8gYmUg
				Ym9va2VkIGludG8gdGhlIGZvbGxvd2luZyBjb3Vyc2UsIGFuZCB5b3UgYXJlIGxpc3RlZCBhcyB0aGVpciBUZWFt
				IExlYWRlciAvIE1hbmFnZXIuCgpDb3Vyc2U6ICAgW2ZhY2V0b2ZhY2VuYW1lXQpDb3N0OiAgIFtjb3N0XQoKRHVy
				YXRpb246ICAgW2R1cmF0aW9uXQpEYXRlKHMpOgpbYWxsZGF0ZXNdCgpMb2NhdGlvbjogICBbc2Vzc2lvbjpsb2Nh
				dGlvbl0KVmVudWU6ICAgW3Nlc3Npb246dmVudWVdClJvb206ICAgW3Nlc3Npb246cm9vbV0KClBsZWFzZSBmb2xs
				b3cgdGhlIGxpbmsgYmVsb3cgdG8gYXBwcm92ZSB0aGUgcmVxdWVzdDoKW2F0dGVuZGVlc2xpbmtdI3VuYXBwcm92
				ZWQKCgoqKiogW2ZpcnN0bmFtZV0gW2xhc3RuYW1lXSdzIGJvb2tpbmcgcmVxdWVzdCBpcyBjb3BpZWQgYmVsb3cg
				KioqKg==
			',
			'confirmationsubject' => '
				Q291cnNlIGJvb2tpbmcgY29uZmlybWF0aW9uOiBbZmFjZXRvZmFjZW5hbWVdLCBbc3RhcnR0aW1lXS1bZmluaXNo
				dGltZV0sIFtzZXNzaW9uZGF0ZV0=
			',
			'confirmationmessage' => '
				VGhpcyBpcyB0byBjb25maXJtIHRoYXQgeW91IGFyZSBub3cgYm9va2VkIG9uIHRoZSBmb2xsb3dpbmcgY291cnNl
				OgoKUGFydGljaXBhbnQ6ICAgW2ZpcnN0bmFtZV0gW2xhc3RuYW1lXQpDb3Vyc2U6ICAgW2ZhY2V0b2ZhY2VuYW1l
				XQpDb3N0OiAgIFtjb3N0XQoKRHVyYXRpb246ICAgIFtkdXJhdGlvbl0KRGF0ZShzKToKW2FsbGRhdGVzXQoKTG9j
				YXRpb246ICAgW3Nlc3Npb246bG9jYXRpb25dClZlbnVlOiAgIFtzZXNzaW9uOnZlbnVlXQpSb29tOiAgIFtzZXNz
				aW9uOnJvb21dCgoqKipQbGVhc2UgYXJyaXZlIHRlbiBtaW51dGVzIGJlZm9yZSB0aGUgY291cnNlIHN0YXJ0cyoq
				KgoKVG8gcmUtc2NoZWR1bGUgb3IgY2FuY2VsIHlvdXIgYm9va2luZwpUbyByZS1zY2hlZHVsZSB5b3VyIGJvb2tp
				bmcgeW91IG5lZWQgdG8gY2FuY2VsIHRoaXMgYm9va2luZyBhbmQgdGhlbiByZS1ib29rIGEgbmV3IHNlc3Npb24u
				ICBUbyBjYW5jZWwgeW91ciBib29raW5nLCByZXR1cm4gdG8gdGhlIHNpdGUsIHRoZW4gdG8gdGhlIHBhZ2UgZm9y
				IHRoaXMgY291cnNlLCBhbmQgdGhlbiBzZWxlY3QgJ2NhbmNlbCcgZnJvbSB0aGUgYm9va2luZyBpbmZvcm1hdGlv
				biBzY3JlZW4uCgpbZGV0YWlsc10KCllvdSB3aWxsIHJlY2VpdmUgYSByZW1pbmRlciBbcmVtaW5kZXJwZXJpb2Rd
				IGJ1c2luZXNzIGRheXMgYmVmb3JlIHRoaXMgY291cnNlLg==
			',
			'confirmationinstrmngr' => '
				KioqIEFkdmljZSBvbmx5ICoqKioKClRoaXMgaXMgdG8gYWR2aXNlIHRoYXQgW2ZpcnN0bmFtZV0gW2xhc3RuYW1l
				XSBoYXMgYmVlbiBib29rZWQgZm9yIHRoZSBmb2xsb3dpbmcgY291cnNlIGFuZCBsaXN0ZWQgeW91IGFzIHRoZWly
				IFRlYW0gTGVhZGVyIC8gTWFuYWdlci4KCklmIHlvdSBhcmUgbm90IHRoZWlyIFRlYW0gTGVhZGVyIC8gTWFuYWdl
				ciBhbmQgYmVsaWV2ZSB5b3UgaGF2ZSByZWNlaXZlZCB0aGlzIGVtYWlsIGJ5IG1pc3Rha2UgcGxlYXNlIHJlcGx5
				IHRvIHRoaXMgZW1haWwuICBJZiBoYXZlIGNvbmNlcm5zIGFib3V0IHlvdXIgc3RhZmYgbWVtYmVyIHRha2luZyB0
				aGlzIGNvdXJzZSBwbGVhc2UgZGlzY3VzcyB0aGlzIHdpdGggdGhlbSBkaXJlY3RseS4KCioqKiBbZmlyc3RuYW1l
				XSBbbGFzdG5hbWVdJ3MgYm9va2luZyBjb25maXJtYXRpb24gaXMgY29waWVkIGJlbG93ICoqKio=
			',
			'remindersubject' => '
				Q291cnNlIGJvb2tpbmcgcmVtaW5kZXI6IFtmYWNldG9mYWNlbmFtZV0sIFtzdGFydHRpbWVdLVtmaW5pc2h0aW1l
				XSwgW3Nlc3Npb25kYXRlXQ==
			',
			'remindermessage' => '
				VGhpcyBpcyBhIHJlbWluZGVyIHRoYXQgeW91IGFyZSBib29rZWQgb24gdGhlIGZvbGxvd2luZyBjb3Vyc2U6CgpQ
				YXJ0aWNpcGFudDogICBbZmlyc3RuYW1lXSBbbGFzdG5hbWVdCkNvdXJzZTogICBbZmFjZXRvZmFjZW5hbWVdCkNv
				c3Q6ICAgW2Nvc3RdCgpEdXJhdGlvbjogICBbZHVyYXRpb25dCkRhdGUocyk6ClthbGxkYXRlc10KCkxvY2F0aW9u
				OiAgIFtzZXNzaW9uOmxvY2F0aW9uXQpWZW51ZTogICBbc2Vzc2lvbjp2ZW51ZV0KUm9vbTogICBbc2Vzc2lvbjpy
				b29tXQoKKioqUGxlYXNlIGFycml2ZSB0ZW4gbWludXRlcyBiZWZvcmUgdGhlIGNvdXJzZSBzdGFydHMqKioKClRv
				IHJlLXNjaGVkdWxlIG9yIGNhbmNlbCB5b3VyIGJvb2tpbmcKVG8gcmUtc2NoZWR1bGUgeW91ciBib29raW5nIHlv
				dSBuZWVkIHRvIGNhbmNlbCB0aGlzIGJvb2tpbmcgYW5kIHRoZW4gcmUtYm9vayBhIG5ldyBzZXNzaW9uLiAgVG8g
				Y2FuY2VsIHlvdXIgYm9va2luZywgcmV0dXJuIHRvIHRoZSBzaXRlLCB0aGVuIHRvIHRoZSBwYWdlIGZvciB0aGlz
				IGNvdXJzZSwgYW5kIHRoZW4gc2VsZWN0ICdjYW5jZWwnIGZyb20gdGhlIGJvb2tpbmcgaW5mb3JtYXRpb24gc2Ny
				ZWVuLgoKW2RldGFpbHNd
			',
			'reminderinstrmngr' => '
				KioqIFJlbWluZGVyIG9ubHkgKioqKgoKWW91ciBzdGFmZiBtZW1iZXIgW2ZpcnN0bmFtZV0gW2xhc3RuYW1lXSBp
				cyBib29rZWQgdG8gYXR0ZW5kIGFuZCBhYm92ZSBjb3Vyc2UgYW5kIGhhcyBhbHNvIHJlY2VpdmVkIHRoaXMgcmVt
				aW5kZXIgZW1haWwuCgpJZiB5b3UgYXJlIG5vdCB0aGVpciBUZWFtIExlYWRlciAvIE1hbmFnZXIgYW5kIGJlbGll
				dmUgeW91IGhhdmUgcmVjZWl2ZWQgdGhpcyBlbWFpbCBieSBtaXN0YWtlIHBsZWFzZSByZXBseSB0byB0aGlzIGVt
				YWlsLgoKKioqIFtmaXJzdG5hbWVdIFtsYXN0bmFtZV0ncyByZW1pbmRlciBlbWFpbCBpcyBjb3BpZWQgYmVsb3cg
				KioqKg==
			',
			'waitlistedsubject' => '
				V2FpdGxpc3RpbmcgYWR2aWNlIGZvciBbZmFjZXRvZmFjZW5hbWVd
			',
			'waitlistedmessage' => '
				VGhpcyBpcyB0byBhZHZpc2UgdGhhdCB5b3UgaGF2ZSBiZWVuIGFkZGVkIHRvIHRoZSB3YWl0bGlzdCBmb3I6CgpD
				b3Vyc2U6ICAgW2ZhY2V0b2ZhY2VuYW1lXQpMb2NhdGlvbjogIFtzZXNzaW9uOmxvY2F0aW9uXQpQYXJ0aWNpcGFu
				dDogICBbZmlyc3RuYW1lXSBbbGFzdG5hbWVdCgoqKipQbGVhc2Ugbm90ZSB0aGlzIGlzIG5vdCBhIGNvdXJzZSBi
				b29raW5nIGNvbmZpcm1hdGlvbioqKgoKQnkgd2FpdGxpc3RpbmcgeW91IGhhdmUgcmVnaXN0ZXJlZCB5b3VyIGlu
				dGVyZXN0IGluIHRoaXMgY291cnNlIGFuZCB3aWxsIGJlIGNvbnRhY3RlZCBkaXJlY3RseSB3aGVuIHNlc3Npb25z
				IGJlY29tZSBhdmFpbGFibGUuCgpUbyByZW1vdmUgeW91cnNlbGYgZnJvbSB0aGlzIHdhaXRsaXN0IHBsZWFzZSBy
				ZXR1cm4gdG8gdGhpcyBjb3Vyc2UgYW5kIGNsaWNrIENhbmNlbCBCb29raW5nLiBQbGVhc2Ugbm90ZSB0aGVyZSBp
				cyBubyB3YWl0bGlzdCByZW1vdmFsIGNvbmZpcm1hdGlvbiBlbWFpbC4=
			',
			'cancellationsubject' => '
				Q291cnNlIGJvb2tpbmcgY2FuY2VsbGF0aW9u
			',
			'cancellationmessage' => '
				VGhpcyBpcyB0byBhZHZpc2UgdGhhdCB5b3VyIGJvb2tpbmcgb24gdGhlIGZvbGxvd2luZyBjb3Vyc2UgaGFzIGJlZ
				W4gY2FuY2VsbGVkOgoKKioqQk9PS0lORyBDQU5DRUxMRUQqKioKClBhcnRpY2lwYW50OiAgIFtmaXJzdG5hbWVdIF
				tsYXN0bmFtZV0KQ291cnNlOiAgIFtmYWNldG9mYWNlbmFtZV0KCkR1cmF0aW9uOiAgIFtkdXJhdGlvbl0KRGF0ZSh
				zKToKW2FsbGRhdGVzXQoKTG9jYXRpb246ICAgW3Nlc3Npb246bG9jYXRpb25dClZlbnVlOiAgIFtzZXNzaW9uOnZl
				bnVlXQpSb29tOiAgIFtzZXNzaW9uOnJvb21d
			',
			'cancellationinstrmngr' => '
				KioqIEFkdmljZSBvbmx5ICoqKioKClRoaXMgaXMgdG8gYWR2aXNlIHRoYXQgW2ZpcnN0bmFtZV0gW2xhc3RuYW1lX
				SBpcyBubyBsb25nZXIgc2lnbmVkLXVwIGZvciB0aGUgZm9sbG93aW5nIGNvdXJzZSBhbmQgbGlzdGVkIHlvdSBhcy
				B0aGVpciBUZWFtIExlYWRlciAvIE1hbmFnZXIuCgoqKiogW2ZpcnN0bmFtZV0gW2xhc3RuYW1lXSdzIGJvb2tpbmc
				gY2FuY2VsbGF0aW9uIGlzIGNvcGllZCBiZWxvdyAqKioq
			'
		);
		
		$return_val = base64_decode(str_replace(array("\n", "\r", "\t", " "), '', $defaults[$field]));
		
		return $return_val;
	}
	
	/**
	 * Function creates the facetoface activity
	 * @param	array $node_values Array of values that make up activity settings
	 * @param	string $hash Node hash value
	 * @param	array $parent_stack Reference to $parent_stack
	 * @param	array $parent_stack_id Reference to $parent_stack_id
	 * @param	int $parent_id Reference to $parent_id
	 * @param	array $parent_stack_type Reference to $parent_stack_type
	 * @param	array $completed_node_stack Reference to $completed_node_stack
	 */
	public function create_facetofaceactivity(array $node_values, $hash, &$parent_stack, &$parent_stack_id, 
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

		$module = $DB->get_record('modules', array('name' => 'facetoface'), 'id');

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
		$fromform->thirdparty = (string)(isset($node_values['thirdparty']) ? $node_values['thirdparty'] : '');
		$fromform->display = (string)(isset($node_values['display']) ? $node_values['display'] : '6');
		$fromform->allowcancellationsdefault = (string)(isset($node_values['allowcancellationsdefault']) ? $node_values['allowcancellationsdefault'] : '1');
		$fromform->showoncalendar = (string)(isset($node_values['showoncalendar']) ? $node_values['showoncalendar'] : '1');
		$fromform->usercalentry = (string)(isset($node_values['usercalentry']) ? $node_values['usercalentry'] : '1');
		$fromform->shortname = (string)(isset($node_values['shortname']) ? $node_values['shortname'] : '');
		$fromform->requestsubject = (string)(isset($node_values['requestsubject']) ? base64_decode($node_values['requestsubject']) : $this->facetofaceactivity_defaults('requestsubject'));
		$fromform->requestmessage = (string)(isset($node_values['requestmessage']) ? base64_decode($node_values['requestmessage']) : $this->facetofaceactivity_defaults('requestmessage'));
		$fromform->requestinstrmngr = (string)(isset($node_values['requestinstrmngr']) ? base64_decode($node_values['requestinstrmngr']) : $this->facetofaceactivity_defaults('requestinstrmngr'));
		$fromform->confirmationsubject = (string)(isset($node_values['confirmationsubject']) ? base64_decode($node_values['confirmationsubject']) : $this->facetofaceactivity_defaults('confirmationsubject'));
		$fromform->confirmationmessage = (string)(isset($node_values['confirmationmessage']) ? base64_decode($node_values['confirmationmessage']) : $this->facetofaceactivity_defaults('confirmationmessage'));
		$fromform->confirmationinstrmngr = (string)(isset($node_values['confirmationinstrmngr']) ? base64_decode($node_values['confirmationinstrmngr']) : $this->facetofaceactivity_defaults('confirmationinstrmngr'));
		$fromform->remindersubject = (string)(isset($node_values['remindersubject']) ? base64_decode($node_values['remindersubject']) : $this->facetofaceactivity_defaults('remindersubject'));
		$fromform->remindermessage = (string)(isset($node_values['remindermessage']) ? base64_decode($node_values['remindermessage']) : $this->facetofaceactivity_defaults('remindermessage'));
		$fromform->reminderinstrmngr = (string)(isset($node_values['reminderinstrmngr']) ? base64_decode($node_values['reminderinstrmngr']) : $this->facetofaceactivity_defaults('reminderinstrmngr'));
		$fromform->reminderperiod = (string)(isset($node_values['reminderperiod']) ? $node_values['reminderperiod'] : '2');
		$fromform->waitlistedsubject = (string)(isset($node_values['waitlistedsubject']) ? base64_decode($node_values['waitlistedsubject']) : $this->facetofaceactivity_defaults('waitlistedsubject'));
		$fromform->waitlistedmessage = (string)(isset($node_values['waitlistedmessage']) ? base64_decode($node_values['waitlistedmessage']) : $this->facetofaceactivity_defaults('waitlistedmessage'));
		$fromform->cancellationsubject = (string)(isset($node_values['cancellationsubject']) ? base64_decode($node_values['cancellationsubject']) : $this->facetofaceactivity_defaults('cancellationsubject'));
		$fromform->cancellationmessage = (string)(isset($node_values['cancellationmessage']) ? base64_decode($node_values['cancellationmessage']) : $this->facetofaceactivity_defaults('cancellationmessage'));
		$fromform->cancellationinstrmngr = (string)(isset($node_values['cancellationinstrmngr']) ? base64_decode($node_values['cancellationinstrmngr']) : $this->facetofaceactivity_defaults('cancellationinstrmngr'));
		$fromform->visible = (int)(isset($node_values['visible']) ? $node_values['visible'] : 1);
		$fromform->visibleoncoursepage = (int)(isset($node_values['visibleoncoursepage']) ? $node_values['visibleoncoursepage'] : 1);
		$fromform->cmidnumber = (string)(isset($node_values['cmidnumber']) ? $node_values['cmidnumber'] : '');
		$fromform->availabilityconditionsjson = (string)(isset($node_values['availabilityconditionsjson']) ? (new \tool_structureimport\core_functionality\structure)->availability_conditions($node_values['availabilityconditionsjson'], $activity_course_id) : base64_decode('eyJvcCI6IiYiLCJjIjpbXSwic2hvd2MiOltdfQ=='));
		$fromform->completionunlocked = (int)(isset($node_values['completionunlocked']) ? $node_values['completionunlocked'] : 1);
		$fromform->completion = (string)(isset($node_values['completion']) ? $node_values['completion'] : '1');
		$fromform->completionexpected = (int)(isset($node_values['completionexpected']) ? $node_values['completionexpected'] : 0);
		$fromform->tags = (array)(isset($node_values['tags']) ? $node_values['tags'] : array());
		$fromform->course = (int)$parent_id;
		$fromform->coursemodule = (int)(isset($node_values['coursemodule']) ? $node_values['coursemodule'] : 0);
		$fromform->section = (int)$activity_section;
		$fromform->module = (int)$module->id;
		$fromform->modulename = 'facetoface';
		$fromform->instance = (int)(isset($module_values['instance']) ? $module_values['instance'] : 0);
		$fromform->add = 'facetoface';
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