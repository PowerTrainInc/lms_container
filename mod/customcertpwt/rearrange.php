<?php
// This file is part of the customcertpwt module for Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Handles position elements on the PDF via drag and drop.
 *
 * @package    mod_customcertpwt
 * @copyright  2021 PowerTrain Inc
 * @license    All Rights Reserved
 */

require_once('../../config.php');

// The page of the customcertpwt we are editing.
$pid = required_param('pid', PARAM_INT);

$page = $DB->get_record('customcertpwt_pages', array('id' => $pid), '*', MUST_EXIST);
$template = $DB->get_record('customcertpwt_templates', array('id' => $page->templateid), '*', MUST_EXIST);
$elements = $DB->get_records('customcertpwt_elements', array('pageid' => $pid), 'sequence');

// Set the template.
$template = new \mod_customcertpwt\template($template);
// Perform checks.
if ($cm = $template->get_cm()) {
    require_login($cm->course, false, $cm);
} else {
    require_login();
}
// Make sure the user has the required capabilities.
$template->require_manage();

if ($template->get_context()->contextlevel == CONTEXT_MODULE) {
    $customcertpwt = $DB->get_record('customcertpwt', ['id' => $cm->instance], '*', MUST_EXIST);
    $title = $customcertpwt->name;
    $heading = format_string($title);
} else {
    $title = $SITE->fullname;
    $heading = $title;
}

// Set the $PAGE settings.
$pageurl = new moodle_url('/mod/customcertpwt/rearrange.php', array('pid' => $pid));
\mod_customcertpwt\page_helper::page_setup($pageurl, $template->get_context(), $title);

// Add more links to the navigation.
if (!$cm = $template->get_cm()) {
    $str = get_string('managetemplates', 'customcertpwt');
    $link = new moodle_url('/mod/customcertpwt/manage_templates.php');
    $PAGE->navbar->add($str, new \action_link($link, $str));
}

$str = get_string('editcustomcertpwt', 'customcertpwt');
$link = new moodle_url('/mod/customcertpwt/edit.php', array('tid' => $template->get_id()));
$PAGE->navbar->add($str, new \action_link($link, $str));

$PAGE->navbar->add(get_string('rearrangeelements', 'customcertpwt'));

// Include the JS we need.
$PAGE->requires->yui_module('moodle-mod_customcertpwt-rearrange', 'Y.M.mod_customcertpwt.rearrange.init',
    array($template->get_id(),
          $page,
          $elements));

// Create the buttons to save the position of the elements.
$html = html_writer::start_tag('div', array('class' => 'buttons'));
$html .= $OUTPUT->single_button(new moodle_url('/mod/customcertpwt/edit.php', array('tid' => $template->get_id())),
        get_string('saveandclose', 'customcertpwt'), 'get', array('class' => 'savepositionsbtn'));
$html .= $OUTPUT->single_button(new moodle_url('/mod/customcertpwt/rearrange.php', array('pid' => $pid)),
        get_string('saveandcontinue', 'customcertpwt'), 'get', array('class' => 'applypositionsbtn'));
$html .= $OUTPUT->single_button(new moodle_url('/mod/customcertpwt/edit.php', array('tid' => $template->get_id())),
        get_string('cancel'), 'get', array('class' => 'cancelbtn'));
$html .= html_writer::end_tag('div');

// Create the div that represents the PDF.
$style = 'height: ' . $page->height . 'mm; line-height: normal; width: ' . $page->width . 'mm;';
$marginstyle = 'height: ' . $page->height . 'mm; width:1px; float:left; position:relative;';
$html .= html_writer::start_tag('div', array(
    'data-templateid' => $template->get_id(),
    'data-contextid' => $template->get_contextid(),
    'id' => 'pdf',
    'style' => $style)
);
if ($page->leftmargin) {
    $position = 'left:' . $page->leftmargin . 'mm;';
    $html .= "<div id='leftmargin' style='$position $marginstyle'></div>";
}
if ($elements) {
    foreach ($elements as $element) {
        // Get an instance of the element class.
        if ($e = \mod_customcertpwt\element_factory::get_element_instance($element)) {
            switch ($element->refpoint) {
                case \mod_customcertpwt\element_helper::customcertpwt_REF_POINT_TOPRIGHT:
                    $class = 'element refpoint-right';
                    break;
                case \mod_customcertpwt\element_helper::customcertpwt_REF_POINT_TOPCENTER:
                    $class = 'element refpoint-center';
                    break;
                case \mod_customcertpwt\element_helper::customcertpwt_REF_POINT_TOPLEFT:
                default:
                    $class = 'element refpoint-left';
            }
            $html .= html_writer::tag('div', $e->render_html(), array('class' => $class,
                'data-refpoint' => $element->refpoint, 'id' => 'element-' . $element->id));
        }
    }
}
if ($page->rightmargin) {
    $position = 'left:' . ($page->width - $page->rightmargin) . 'mm;';
    $html .= "<div id='rightmargin' style='$position $marginstyle'></div>";
}
$html .= html_writer::end_tag('div');

echo $OUTPUT->header();
echo $OUTPUT->heading($heading);
echo $OUTPUT->heading(get_string('rearrangeelementsheading', 'customcertpwt'), 3);
echo $OUTPUT->notification(get_string('exampledatawarning', 'customcertpwt'), \core\output\notification::NOTIFY_WARNING);
echo $html;
$PAGE->requires->js_call_amd('mod_customcertpwt/rearrange-area', 'init', array('#pdf'));
echo $OUTPUT->footer();
