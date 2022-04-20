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
 * Handles uploading files
 *
 * @package    mod_customcertpwt
 * @copyright  2021 PowerTrain Inc
 * @license    All Rights Reserved
 */

require('../../config.php');

require_login();

$context = context_system::instance();
require_capability('moodle/site:config', $context);

$struploadimage = get_string('uploadimage', 'customcertpwt');

// Set the page variables.
$pageurl = new moodle_url('/mod/customcertpwt/upload_image.php');
\mod_customcertpwt\page_helper::page_setup($pageurl, $context, $SITE->fullname);

// Additional page setup.
$PAGE->navbar->add($struploadimage);

$uploadform = new \mod_customcertpwt\upload_image_form();

if ($uploadform->is_cancelled()) {
    redirect(new moodle_url('/admin/settings.php?section=modsettingcustomcertpwt'));
} else if ($data = $uploadform->get_data()) {
    // Handle file uploads.
    \mod_customcertpwt\certificate::upload_files($data->customcertpwtimage, $context->id);

    redirect(new moodle_url('/mod/customcertpwt/upload_image.php'), get_string('changessaved'));
}

echo $OUTPUT->header();
echo $OUTPUT->heading($SITE->fullname);
$uploadform->display();
echo $OUTPUT->footer();
