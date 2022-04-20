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
 * customcertpwt background image element upgrade code.
 *
 * @package    customcertpwt_element_bgimage
 * @copyright  2021 PowerTrain Inc
 * @license    All Rights Reserved
 */

defined('MOODLE_INTERNAL') || die;

/**
 * customcertpwt background image element upgrade code.
 *
 * @param int $oldversion the version we are upgrading from
 * @return bool always true
 */
function xmldb_customcertpwtelement_bgimage_upgrade($oldversion) {
    global $DB;

    if ($oldversion < 2016120501) {
        // Go through each 'image' element and update the file stored information.
        if ($images = $DB->get_records_select('customcertpwt_elements', $DB->sql_compare_text('element') . ' = \'bgimage\'')) {
            // Create a file storage instance we are going to use to create pathname hashes.
            $fs = get_file_storage();
            // Go through and update the details.
            foreach ($images as $image) {
                // Get the current data we have stored for this element.
                $elementinfo = json_decode($image->data);
                if ($file = $fs->get_file_by_hash($elementinfo->pathnamehash)) {
                    $arrtostore = array(
                        'contextid' => $file->get_contextid(),
                        'filearea' => $file->get_filearea(),
                        'itemid' => $file->get_itemid(),
                        'filepath' => $file->get_filepath(),
                        'filename' => $file->get_filename(),
                        'width' => (int) $elementinfo->width,
                        'height' => (int) $elementinfo->height
                    );
                    $arrtostore = json_encode($arrtostore);
                    $DB->set_field('customcertpwt_elements', 'data', $arrtostore,  array('id' => $image->id));
                }
            }
        }

        // Savepoint reached.
        upgrade_plugin_savepoint(true, 2016120501, 'customcertpwt_element', 'bgimage');
    }

    return true;
}
