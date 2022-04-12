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
 * S3 Data Importer.
 *
 * @package     auth_cacpwt
 * @copyright   2022 PowerTrain Inc {@link https://powertrain.com/}
 * @license     All Rights Reserved
 */

namespace auth_cacpwt\task;

use coding_exception;
use core\task\scheduled_task;
use dml_exception;
use S3;
use S3Request;
use stdClass;

/*
 * Command for testing the CRON task.
 *
 * D:\xampp-netc\php\php.exe scheduled_task.php --execute="\auth_cacpwt\task\data_import"
 * Run from the admin/cli folder
 */

/**
 * Data import class.
 */
class data_import extends scheduled_task {
    /**
     * Returns the name of this task.
     * @throws coding_exception
     */
    public function get_name() {
        // Shown in admin screens.
        return get_string('data_import_task', 'auth_cacpwt');
    }

    /**
     * Execute task.
     * @throws dml_exception|coding_exception
     */
    public function execute() {
        global $CFG, $DB;

        // Run if task is enabled.
        if (get_config('auth_cacpwt', 'enable_s3_imports') == '1') {
            // Get feature settings.
            $containername = get_config('auth_cacpwt', 'container_name');
            $importfilename = get_config('auth_cacpwt', 'import_filename');
            $headersexist = get_config('auth_cacpwt', 'first_row_headers');
            $accesskey = get_config('auth_cacpwt', 'access_key');
            $secretkey = get_config('auth_cacpwt', 'secret_key');
            $testmode = get_config('auth_cacpwt', 'enable_imports_test_mode');

            // Load S3 class.
            require_once($CFG->dirroot . '/repository/s3/S3.php');

            // Set up default error message.
            $errormsg = null;

            $s3 = new S3($accesskey, $secretkey, true);

            // Rigging in method to check if we've successfully connected to AWS.
            $rest = new S3Request('GET', '', '', 's3.amazonaws.com');
            $rest = $rest->getResponse();

            $connectionfailed = $rest->error;

            unset($rest);
            // End of rigging method.

            if ($connectionfailed === false) {
                // Rig in a method for checking if the container exists.
                $rest = new S3Request('GET', $containername, '', 's3.amazonaws.com');
                $rest = $rest->getResponse();

                $containerfailed = $rest->error;

                unset($rest);
                // End of rigging method.

                if (!$containerfailed) {
                    // Rig in method for checking if the import file exists.
                    $rest = new S3Request('GET', $containername, $importfilename, 's3.amazonaws.com');
                    $rest = $rest->getResponse();

                    $importfilemissing = $rest->error;

                    unset($rest);
                    // End of rigging method.

                    if (!$importfilemissing) {
                        // Get header settings.
                        $headers = get_config('auth_cacpwt', 'first_row_headers');

                        if ($headers == 1) {
                            $headers = true;
                        } else {
                            $headers = false;
                        }

                        // Get batch from config.  Set to 'even' if first time.
                        if (!$batch = get_config('auth_cacpwt', 'batch')) {
                            $batch = 'even';
                        }

                        // Set new batch value.
                        if ($batch == 'even') {
                            $newtable = 'auth_cacpwt_import_odd';
                            $newbatch = 'odd';
                        } else {
                            $newtable = 'auth_cacpwt_import_even';
                            $newbatch = 'even';
                        }

                        // Get data from Amazon Web Services (AWS) S3 bucket.
                        $importdata = $s3->getObject($containername, $importfilename);
                        $lines = explode(PHP_EOL, $importdata->body);

                        // Prep new table to receive data.
                        $DB->execute('TRUNCATE {' . $newtable . '}');

                        $recordcount = 0;
                        $batchcount = 0;

                        foreach ($lines as $line) {
                            $entries = str_getcsv($line);

                            // If headers are being passed -- skip first row.
                            if ($headers) {
                                $headers = false;
                            } else {
                                if (count($entries) == 3) {
                                    $organizations = explode(',', $entries[2]);
                                    $organizations = array_flip($organizations);

                                    $authrequired = 1;

                                    if (!isset($organizations['Authorization Required'])) {
                                        $authrequired = 0;
                                    }

                                    // Write entry to import table.
                                    $insert = new stdClass;
                                    $insert->dodid = $entries[0];
                                    $insert->auth_required = $authrequired;
                                    $insert->bypass_dmdc = $entries[1];

                                    $DB->insert_record($newtable, $insert);

                                    unset($insert);

                                    $recordcount++;
                                    $batchcount++;

                                    if ($testmode == 1 && $recordcount == 1000) {
                                        break;
                                    }

                                    // Show the world we are alive and working.
                                    if ($batchcount == 100000) {
                                        echo 'Records Processed: ' . $recordcount . PHP_EOL;

                                        $batchcount = 0;
                                    }

                                    unset($organizations, $authrequired);
                                }
                            }

                            unset($entries);
                        }

                        echo 'Total Records Processed: ' . $recordcount . PHP_EOL;

                        // Update config to use new table.
                        set_config('batch', $newbatch, 'auth_cacpwt');

                        unset($batchcount, $recordcount, $importdata, $lines, $line, $batch, $newbatch, $newtable, $headers);
                    } else {
                        $errormsg = get_string('cron:error_import_file', 'auth_cacpwt');
                    }
                } else {
                    $errormsg = get_string('cron:error_container', 'auth_cacpwt');
                }
            } else {
                $errormsg = get_string('cron:error_connecting', 'auth_cacpwt');
            }
        }
    }
}
