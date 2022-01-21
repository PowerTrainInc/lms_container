<?php
/**
 * Strings for component 'tool_structureimport', language 'en'
 *
 * @package    tool
 * @subpackage structureimport
 * @copyright  2021 PowerTrain Inc {@link https://powertrain.com/}
 * @license    All Rights Reserved
 */
 
$string['criterroractivitycreate'] = 'Import Error: Failed to create course activity.';
$string['criterrorcategoryfail'] = 'Failed to create category!';
$string['criterrorcoursefail'] = 'Failed to create course!';
$string['criterrorcoursefailcat'] = 'Critical Error!  Course must be contained inside a category.';
$string['criterrorinvalidactivityname'] = 'Critical Error!  Invalid activity name in availabilityconditionsjson field.';
$string['criterrorinvalidactivitynum'] = 'Critical Error! Invalid activity number in availabilityconditionsjson field.  Must be -1.';
$string['criterrorinvalidactivitytype'] = 'Cannot import activity type "{$a->activity}".';
$string['criterrorinvalidtemplate'] = 'Certificate template {$a->template} does not exist.'; 
$string['criterrormissingcourse'] = 'Import Error: Activity missing parent course.';
$string['criterrormissingfile'] = 'Critical Error!  Missing structure file.';
$string['criterrormissingmodule'] = 'Import Error: Module not installed.';
$string['criterrorstoragefail'] = 'Moodle data path {$a->path} is not writable or does not exist.';
$string['criterrortransactionsreq'] = 'Database transaction support required.';
$string['importdone'] = 'Import done!';
$string['importdonebutton'] = 'Done';
$string['log:certificatetemplateupdated'] = 'Certificate template updated/assigned';
$string['log:certificatetemplateupdateddescription'] = 'The user with id \'{$a->userid}\' updated/assigned the certificate template via the plugin.';
$string['modulename'] = 'Structure & Course Importer';
$string['nodeinvalidparent'] = 'Node {$a->node} must be a child of {$a->parent}.';
$string['nodemissingelement'] = 'XML Error: {$a->node} requires element {$a->element}.';
$string['none'] = 'None';
$string['pluginname'] = 'Structure & Course Importer';
$string['selectacategoryerror'] = 'Select a category';
$string['selectafileerror'] = 'Select a file';
$string['structurefileselect'] = 'Select Structure & Course File';
$string['structureinsertpoint'] = 'Select Structure Insert Point';
$string['successactivity'] = 'Activity <b>{$a->name}</b> for topic/module #{$a->id} created';
$string['successcategory'] = 'Category #{$a->id} <b>{$a->name}</b> created';
$string['successcategory:update'] = 'Category #{$a->id} <b>{$a->name}</b> updated';
$string['successcourse'] = 'Course #{$a->id} <b>{$a->name}</b> created';
$string['successtopic'] = 'Topic #{$a->id} <b>{$a->name}</b> created';
$string['uploadfile'] = 'Upload File';