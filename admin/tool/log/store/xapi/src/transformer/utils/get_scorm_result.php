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

namespace src\transformer\utils;
defined('MOODLE_INTERNAL') || die();

function get_scorm_result($repo, $currentscostatus) {
    $result = null;
    // error_log("in get scorm result....");
    // error_log(print_r($currentscostatus, true));

    $scormscoestracks = $repo->read_records('scorm_scoes_track', [
        'userid' => $currentscostatus->userid,
        'scormid' => $currentscostatus->scormid,
        'attempt' => $currentscostatus->attempt,
        'scoid' => $currentscostatus->scoid
    ]);

    foreach ($scormscoestracks as $st) {
        // error_log("$st->element: $st->value");
        if ($st->element == 'cmi.core.lesson_status' ||
            $st->element == 'cmi.completion_status' ||
            $st->element == 'cmi.success_status') {
            // i'm ignoring stuff like 'unknown' or 'not attempted'
            if ($st->value == 'passed') {
                $result['success'] = TRUE;
            }
            if ($st->value == 'failed') {
                $result['success'] = FALSE;
            }
            if ($st->value == 'completed') {
                $result['completion'] = TRUE;
            }
            if ($st->value == 'incomplete') {
                $result['completion'] = FALSE;
            }
        }
        elseif ($st->element == 'cmi.score.scaled') {
            $result['score']['scaled'] = $st->value;
        } 
        elseif ($st->element == 'cmi.score.raw') {
            $result['score']['raw'] = $st->value;
        }
        elseif ($st->element == 'cmi.score.min') {
            $result['score']['min'] = $st->value;
        }
        elseif ($st->element == 'cmi.score.max') {
            $result['score']['max'] = $st->value;
        }
        elseif ($st->element == 'cmi.session_time') {
            $result['duration'] = $st->value;
        }
    }

    return $result;
}

