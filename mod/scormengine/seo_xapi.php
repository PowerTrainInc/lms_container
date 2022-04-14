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


require_once('classes/StatementBuilder.php');
require_once('classes/LrsApi.php');
require_once('classes/Requests.php');
require_once('lib.php');

function send_xapi_statements_from_seo($type, $seo, $url, $user, $password) {
    $requests = new xapi\Requests();
    $lrsapi = new xapi\LrsApi($requests, $url, $user, $password);

    $statements = get_xapi_statements_from_seo($type, $seo, $lrsapi);
    if ($statements && count($statements) > 0) {
        $lrsapi->statements()->create_resource($statements)->with_no_params();
    }
}

function get_xapi_statements_from_seo($type, $seo, $lrsapi) {
    $statements = [];
    $activityexists = true;
    $attemptexists = true;
    $statementbuilder = new xapi\StatementBuilder();
    $courseactivitystateid = 'https://w3id.org/xapi/netc/course/activity-state';
    $courseattemptstateid = 'https://w3id.org/xapi/netc/course/attempt-state';

    $coursestateparams = [
        'activityId' => $statementbuilder->build_course_activity_id($seo->course->id),
        'agent' => [
            'account' => $statementbuilder->build_course_actor($seo->learner)['account'],
        ],
        'stateId' => $courseactivitystateid,
    ];

    // Get course activity lrs or make new course activity.
    $courseactivitystate = $lrsapi->activity_states()->get_resource()->with_query_params($coursestateparams);
    if (!$courseactivitystate) {
        $courseactivitystate = [ 'attempts' => [], 'seo' => $seo ];
        $activityexists = false;
    }

    // Get course attempt or make new course attempt.
    $coursestateparams['stateId'] = $courseattemptstateid;
    $coursestateparams['registration'] = $seo->id;
    $courseattemptstate = $lrsapi->activity_states()->get_resource()->with_query_params($coursestateparams);
    if (!$courseattemptstate) {
        $courseattemptstate = [ 'lessons' => [] ];
        $attemptexists = false;
    }

    if ($type === 'INITIALIZE') {
        $statements[] = $statementbuilder->build_initialize_statement($seo);

        // Add this course attempt to course activity.
        $courseactivitystate['attempts'][] = $seo->id;
    }

    if ($type === 'TERMINATE') {
        // Terminate all the lessons in this course attempt (add lesson terminate statements).
        $statements = terminate_all_lessons($courseattemptstate['lessons'], $seo, $lrsapi);

        $statements[] = $statementbuilder->build_terminate_statement($seo);
    }

    if (!$type) {
        // Process lessons.
        if ($seo->activityDetails->attempts !== 0 && isset($seo->activityDetails->children)) {
            $coursestate = process_lessons(
                $seo->activityDetails->children,
                [ 'statements' => $statements, 'lessons' => $courseattemptstate['lessons'] ],
                $lrsapi, $seo);

            $statements = $coursestate['statements'];
            $courseattemptstate['lessons'] = $coursestate['lessons'];
        }
    }

    // Create new course attempt.
    $coursestateparams['stateId'] = $courseattemptstateid;
    $coursestateparams['registration'] = $seo->id;

    if ($attemptexists) {
        $lrsapi->activity_states()
            ->update_resource($courseattemptstate)->with_query_params($coursestateparams);
    } else {
        $lrsapi->activity_states()
            ->create_resource($courseattemptstate)->with_query_params($coursestateparams);
    }

    // Add this course activity to course and update or create.
    $coursestateparams['stateId'] = $courseactivitystateid;
    unset($coursestateparams['registration']);

    if ($activityexists) {
        // Add and update.
        $lrsapi->activity_states()
            ->update_resource($courseactivitystate)->with_query_params($coursestateparams);
    } else {
        // Create new activity state.
        $lrsapi->activity_states()
            ->create_resource($courseactivitystate)->with_query_params($coursestateparams);
    }

    return $statements;
}

function terminate_all_lessons($lessons, $seo, $lrsapi) {
    $statementbuilder = new xapi\StatementBuilder();

    $stateparams = [
        'agent' => [
            'account' => $statementbuilder->build_course_actor($seo->learner)['account'],
        ],
        'stateId' => 'https://w3id.org/xapi/scorm/attempt-state',
    ];

    $statements = [];

    foreach ($lessons as $activityid => $registrationids) {
        foreach ($registrationids as $registrationid) {
            $stateparams['activityId'] = $activityid;
            $stateparams['registration'] = $registrationid;

            // Get the lesson attempt state
            $lessonstate = $lrsapi->activity_states()->get_resource()->with_query_params($stateparams);
            if (!$lessonstate) {
                continue;
            }

            // If already terminated, continue.
            if (array_key_exists('terminated', $lessonstate['lesson_states']) &&
                    $lessonstate['lesson_states']['terminated'] === true
            ) {
                continue;
            }

            // Else update the lesson attempt.
            $lessonstate['lesson_states']['terminated'] = true;
            $lrsapi->activity_states()->update_resource($lessonstate)->with_query_params($stateparams);

            // Add the terminate statement
            $statements[] = $statementbuilder->build_lesson_terminate_statement(
                $lessonstate, $activityid, $registrationid, $seo);
        }
    }

    return $statements;
}

function process_lessons($lessons, $coursestate, $lrsapi, $seo) {
    $currentstate = $coursestate;

    foreach ($lessons as $lesson) {
        if ($lesson->attempts === 0) {
            continue;
        }

        if (isset($lesson->children) && count($lesson->children) > 0) {
            $currentstate = process_lessons($lesson->children, $currentstate, $lrsapi, $seo);
        } else {
            $currentstate = process_lesson($lesson, $currentstate, $lrsapi, $seo);
        }
    }

    return $currentstate;
}

function get_interaction_attempt_states($lesson, $lessonregistrationid, $attemptstate, $seo) {
    if (!isset($lesson->runtime->runtimeInteractions) || count($lesson->runtime->runtimeInteractions) === 0) {
        return [];
    }

    $sb = new xapi\StatementBuilder();

    $assessmentid = uuid();
    $assessment = isset($attemptstate['assessment']) ? $attemptstate['assessment'] : [
        'id' => $sb->build_assessment_activity_id($assessmentid),
        'interactions' => [],
    ];
    $statements = [];

    foreach ($lesson->runtime->runtimeInteractions as $interaction) {
        $interactionid = $sb->build_interaction_activity_id($interaction->id);
        if (!in_array($interactionid, $assessment['interactions'])) {
            $assessment['interactions'][] = $interactionid;
            $statements[] = $sb->build_interaction_responded_statement(
                $interaction, $lesson, $lessonregistrationid, $seo->learner, $seo->course, $assessmentid);
        }
    }

    return [ 'assessment' => $assessment, 'statements' => $statements ];
}

function process_lesson($lesson, $coursestate, $lrsapi, $seo) {
    $currentstate = $coursestate;
    $statementbuilder = new xapi\StatementBuilder();
    $activitystateid = 'https://w3id.org/xapi/scorm/activity-state';
    $attemptstateid = 'https://w3id.org/xapi/scorm/attempt-state';
    $registrationid = uuid();
    $isnewattempt = true;
    $activityexists = true;
    $attemptdiff = 0;
    $lessonactivityid = $statementbuilder->build_lesson_activity_id($lesson->id);

    $stateparams = [
        'activityId' => $lessonactivityid,
        'agent' => [
            'account' => $statementbuilder->build_course_actor($seo->learner)['account'],
        ],
    ];

    // Get the lesson activity state.
    $stateparams['stateId'] = $activitystateid;
    $activitystate = $lrsapi->activity_states()->get_resource()->with_query_params($stateparams);

    // If it does not exists then we need to create a new one - later.
    if (!$activitystate) {
        $activitystate = [ 'attempts' => [], 'seo' => $lesson ];
        $activityexists = false;
    }

    // Set the attempt details for this session.
    $currentattemptstate = [ 'title' => $lesson->title ];
    if (isset($lesson->runtime->location)) {
        $currentattemptstate['location'] = $lesson->runtime->location;
    }
    if (isset($lesson->timeTracked)) {
        $currentattemptstate['total_time'] =
            $statementbuilder->get_iso8601_duration($statementbuilder->get_duration_seconds($lesson->timeTracked));
    }

    // Has this lesson been attempted for this course attempt?
    $hasbeenattempted = array_key_exists($lessonactivityid, $currentstate['lessons']);
    if ($hasbeenattempted) {
        // If the lesson activity has been attempted, then we need to find out the disposition of this attempt.

        // Get lesson attempt diff.
        $lessonattempts = $currentstate['lessons'][$lessonactivityid];
        $attemptdiff = intval($lesson->attempts) - count($lessonattempts);

        if ($attemptdiff === 0) {
            // This is the most recent existing attempt. Just update attempt.
            $registrationid = end($lessonattempts);
            $isnewattempt = false;

            // The activity state and attempt state should already exist.
            // Get the the attempt state to update it with new states.
            $stateparams['stateId'] = $attemptstateid;
            $stateparams['registration'] = $registrationid;
            $attemptstate = $lrsapi->activity_states()->get_resource()->with_query_params($stateparams);
            if (!$attemptstate) {
                throw new ErrorException("No attempt state for lesson registration {$registrationid}");
            }

            // If the new 'total_time' is not equal to the last 'total_time', then it can be assumed something happened in this lesson.
            if ($attemptstate['total_time'] !== $currentattemptstate['total_time']) {
                // Check for assessment interactions in this lesson.
                $interactionstate = get_interaction_attempt_states($lesson, $registrationid, $attemptstate, $seo);
                $currentstate['statements'] = array_merge($currentstate['statements'], $interactionstate['statements']);
                $currentattemptstate['assessment'] = $interactionstate['assessment'];

                // Create the any new statements and attempt states for this lesson attempt.

                $lessonstate = get_lesson_attempt_states(
                    $lesson, $registrationid, $attemptstate['lesson_states'], $seo, $lrsapi);
                $currentstate['statements'] = array_merge($currentstate['statements'], $lessonstate['statements']);

                // Update the attempt state with new states.
                $currentattemptstate['lesson_states'] = $lessonstate['lesson_states'];
                $attemptstate = $lrsapi->activity_states()
                    ->update_resource($currentattemptstate)
                    ->with_query_params($stateparams);
            }
        }
    } else {
        // This lesson activity has never been attempted for this course attempt.

        // Add the lesson id to the course attempts
        $currentstate['lessons'][$lessonactivityid] = [];
        $attemptdiff = intval($lesson->attempts);
    }

    if ($isnewattempt) {
        if ($hasbeenattempted) {
            // If there is an attempt from a previous session it can be terminated.
            // Get the last entry in the course attempt state for this lesson id.
            $lastattemptid = end($currentstate['lessons'][$lessonactivityid]);

            // Get the attempt state
            $stateparams['stateId'] = $attemptstateid;
            $stateparams['registration'] = $lastattemptid;
            $lastattemptstate = $lrsapi->activity_states()->get_resource()->with_query_params($stateparams);

            if (!array_key_exists('terminated', $lastattemptstate['lesson_states'])) {
                $lastattemptstate['lesson_states']['terminated'] = true;
                $lrsapi->activity_states()->update_resource($lastattemptstate)->with_query_params($stateparams);

                $currentstate['statements'][] = $statementbuilder->build_lesson_terminate_statement(
                    $lastattemptstate, $lessonactivityid, $lastattemptid, $seo);
            }
        }

        if ($attemptdiff > 1) {
            // If there was more than 1 attempt made in this session, then we have missing attempts and.
            // Must at least account for their initialization and termination before the current attempt.
            $stateparams['stateId'] = $attemptstateid;

            $missingattempts = create_missing_attempts(
                $lesson, $attemptdiff - 1, $stateparams, $lrsapi, $seo);
            $currentstate['statements'] = array_merge($currentstate['statements'], $missingattempts['statements']);
            $currentstate['lessons'][$lessonactivityid] =
                array_merge($currentstate['lessons'][$lessonactivityid], $missingattempts['attempts']);
        }

        // Add the lesson initialize statement.
        $currentstate['statements'][] = $statementbuilder->build_lesson_initialize_statement($lesson, $registrationid, $seo);

        // Add to the lesson activity state attempts.
        $activitystate['attempts'][] = $registrationid;

        // Add to course lesson id attempts.
        $currentstate['lessons'][$lessonactivityid][] = $registrationid;

        // Check for assessment interactions in this lesson.
        $interactionstate = get_interaction_attempt_states($lesson, $registrationid, [], $seo);
        $currentstate['statements'] = array_merge($currentstate['statements'], $interactionstate['statements']);
        $currentattemptstate['assessment'] = $interactionstate['assessment'];

        // Get any lesson states and statements
        $lessonstate = get_lesson_attempt_states($lesson, $registrationid, [ 'initialized' => true ], $seo, $lrsapi);

        // Assign the new lesson states to the attempt state.
        $currentattemptstate['lesson_states'] = $lessonstate['lesson_states'];

        // Add the new lesson state statements.
        $currentstate['statements'] = array_merge($currentstate['statements'], $lessonstate['statements']);

        // Create the new attempt state.
        $stateparams['stateId'] = $attemptstateid;
        $stateparams['registration'] = $registrationid;
        $attemptstate = $lrsapi->activity_states()->create_resource($currentattemptstate)->with_query_params($stateparams);
    }

    // If there was no lesson activity state for this lesson id create it, otherwise update it.
    $stateparams['stateId'] = $activitystateid;
    unset($stateparams['registration']);

    if ($activityexists) {
        $lrsapi->activity_states()->update_resource($activitystate)->with_query_params($stateparams);
    } else {
        $lrsapi->activity_states()->create_resource($activitystate)->with_query_params($stateparams);
    }

    return $currentstate;
}

function create_missing_attempts($lesson, $missingattemptcount, $stateparams, $lrsapi, $seo) {
    $statements = [];
    $attempts = [];
    $statementbuilder = new xapi\StatementBuilder();

    foreach (range(1, $missingattemptcount) as $missingattempt) {
        $registrationid = uuid();

        // Add registration id to attempts.
        $attempts[] = $registrationid;

        $stateparams['registration'] = $registrationid;
        $missinglessonattemptstate = [
            'total_time' => 'PT0S',
            'lesson_states' => [ 'initialized' => true, 'terminated' => true ]
        ];
        $lrsapi->activity_states()
            ->create_resource($missinglessonattemptstate)
            ->with_query_params($stateparams);

        // Add initialize statement.
        $statements[] = $statementbuilder->build_lesson_initialize_statement($lesson, $registrationid, $seo);

        // Add terminate statement.
        $statements[] = $statementbuilder->build_lesson_terminate_statement(
            $missinglessonattemptstate, $stateparams['activityId'], $registrationid, $seo);
    }

    return [ 'statements' => $statements, 'attempts' => $attempts ];
}

function get_lesson_attempt_states($lesson, $registrationid, $lessonstates, $seo, $lrsapi) {
    $statements = [];
    $newlessonstates = $lessonstates;
    $statementbuilder = new xapi\StatementBuilder();

    // Resumed.
    // Lesson.rumtime.entry = 'resume'.
    if ($lesson->runtime->entry === 'resume' && array_key_exists('suspended', $lessonstates)) {
        $statements[] = $statementbuilder->build_lesson_resumed_statement($lesson, $registrationid, $seo);
        $newlessonstates['resumed'] = true;
    }

    // Scored
    // Lesson.runtime.scoreScaled is set there is something in there and there either.
    // No lesson_states.scored or lesson_states.score !== lesson.runtime.scoreScaled.
    if (isset($lesson->runtime->scoreScaled) && $lesson->runtime->scoreScaled && (
            !array_key_exists('scored', $lessonstates) || (array_key_exists('score', $lessonstates) && (
                $lessonstates['score'] !== $lesson->runtime->scoreScaled)))
    ) {
        $statements[] = $statementbuilder->build_lesson_scored_statement(
            $lesson, $lesson->runtime->scoreScaled, $registrationid, $seo);
        $newlessonstates['scored'] = true;
        $newlessonstates['score'] = $lesson->runtime->scoreScaled;
    }

    // Passed.
    // Lesson->activitySuccess is 'PASSED' and lesson_states.success is not set or.
    // Lesson_states.success is 'FAILED'.
    if ($lesson->activitySuccess === 'PASSED' && (
            !array_key_exists('success', $lessonstates) || $lessonstates['success'] !== 'PASSED')
    ) {
        $statements[] = $statementbuilder->build_lesson_passed_statement($lesson, $registrationid, $seo);
        $newlessonstates['success'] = 'PASSED';
    }

    // Failed
    // Lesson->activitySuccess is 'FAILED' and lesson_states.success is not set or.
    // Lesson_states.success is 'PASSED'.
    if ($lesson->activitySuccess === 'FAILED' && (
            !array_key_exists('success', $lessonstates) || $lessonstates['success'] !== 'FAILED')
    ) {
        $statements[] = $statementbuilder->build_lesson_failed_statement($lesson, $registrationid, $seo);
        $newlessonstates['success'] = 'FAILED';
    }

    // Completed.
    // Lesson.activityCompletion is 'COMPLETE' and lesson_states.completed is not set.
    if ($lesson->activityCompletion === 'COMPLETED' && !array_key_exists('completed', $lessonstates)) {
        $statements[] = $statementbuilder->build_lesson_completed_statement($lesson, $registrationid, $seo);
        $newlessonstates['completed'] = true;
    }

    // Suspended.
    // Lesson.runtime.exit = 'suspend'.
    if ($lesson->runtime->exit === 'suspend') {
        $statements[] = $statementbuilder->build_lesson_suspended_statement($lesson, $registrationid, $seo);
        $newlessonstates['suspended'] = true;
    }

    return [ 'statements' => $statements, 'lesson_states' => $newlessonstates ];
}

