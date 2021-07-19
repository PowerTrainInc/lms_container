<?php
// Standard GPL and phpdocs
require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir.'/adminlib.php');
 
//admin_externalpage_setup('tooldemo');
 
// Set up the page.
//$title = get_string('pluginname', 'tool_demo');
//$pagetitle = $title;
//$url = new moodle_url("/admin/tool/demo/index.php");
//$PAGE->set_url($url);
$PAGE->set_title("Manage Content");
$PAGE->set_heading("Manage Content");
 

echo $OUTPUT->header();
echo $OUTPUT->heading("Manage Content");

echo "<script>window.site_home = '".$CFG->wwwroot."'</script>";
echo '<div id="app"></div>';
echo '<script src="../public/integrate.js"></script>';
//echo '<div id="app"></div><script src="../public/dist/js/app.bundle.js"></script>';

echo $OUTPUT->footer();