<?php
// This file is part of Moodle - http://moodle.org/
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
 * Restricted Access Page.
 *
 * @package    auth_cacpwt
 * @copyright  2022 PowerTrain Inc {@link https://powertrain.com/}
 * @license    All Rights Reserved
 */

require('../../config.php');

require_course_login($SITE);

$PAGE->set_context(context_system::instance());
$PAGE->set_url('/auth/cacpwt/restricted.php');

$title = get_string('restrictedaccess_title', 'auth_cacpwt');

$PAGE->set_title($title);

echo $OUTPUT->header();
echo $OUTPUT->heading($title);

$body = get_config('auth_cacpwt', 'restrictedaccess');
$width = get_config('auth_cacpwt', 'restrictedaccess_width');
$padding = get_config('auth_cacpwt', 'restrictedaccess_padding');
$background = get_config('auth_cacpwt', 'restrictedaccess_background');

echo '
    <br>
    <div style="display: block; width: ' . $width . '; padding: ' . $padding . '; margin: auto; background: ' . $background . ';">
';

echo $body;

echo '
    </div>
    <br>
';

echo $OUTPUT->footer();
