<?php

require_once('classes/StatementBuilder.php');
require_once('classes/LrsApi.php');
require_once('classes/Requests.php');
require_once('lib.php');

function send_xapi_statements_from_seo($type, $seo, $url, $user, $password) {
    $requests = new xapi\Requests();
    $lrs_api = new xapi\LrsApi($requests, $url, $user, $password);

    $statements = get_xapi_statements_from_seo($type, $seo, $lrs_api);
    error_log(json_encode($statements));
    if ($statements && count($statements) > 0) {
        $lrs_api->statements()->create_resource($statements)->with_no_params();
    }
}

function get_xapi_statements_from_seo($type, $seo, $lrs_api) {
    $statements = [];
    $activity_exists = true;
    $attempt_exists = true; 
    $statement_builder = new xapi\StatementBuilder();
    $course_activity_state_id = 'https://w3id.org/xapi/netc/course/activity-state';
    $course_attempt_state_id = 'https://w3id.org/xapi/netc/course/attempt-state';

    $course_state_params = [
        'activityId' => $statement_builder->build_course_activity_id($seo->course->id),
        'agent' => [
            'account' => $statement_builder->build_course_actor($seo->learner)['account'],
        ],
        'stateId' => $course_activity_state_id,
    ];

    // get course activity lrs or make new course activity.
    $course_activity_state = $lrs_api->activity_states()->get_resource()->with_query_params($course_state_params);
    if (!$course_activity_state) {
        $course_activity_state = [ 'attempts' => [], 'seo' => $seo ];
        $activity_exists = false;
    }


    // get course attempt or make new course attempt.
    $course_state_params['stateId'] = $course_attempt_state_id;
    $course_state_params['registration'] = $seo->id;
    $course_attempt_state = $lrs_api->activity_states()->get_resource()->with_query_params($course_state_params);
    if (!$course_attempt_state) {
        $course_attempt_state = [ 'lessons' => [] ];
        $attempt_exists = false;
    }

    if ($type === 'INITIALIZE') {
        $statements[] = $statement_builder->build_initialize_statement($seo);

        // add this course attempt to course activity.
        $course_activity_state['attempts'][] = $seo->id;
    }

    if ($type === 'TERMINATE') {
        // terminate all the lessons in this course attempt (add lesson terminate statements).
        $statements = terminate_all_lessons($course_attempt_state['lessons'], $seo, $lrs_api);

        $statements[] = $statement_builder->build_terminate_statement($seo);
    }

    if (!$type) {
        // process lessons.
        if ($seo->activityDetails->attempts !== 0 && isset($seo->activityDetails->children)) {
            $course_state = process_lessons(
                $seo->activityDetails->children,
                [ 'statements' => $statements, 'lessons' => $course_attempt_state['lessons'] ],
                $lrs_api, $seo);
            // array_merge($statements, $course_state['statements']);
            $statements = $course_state['statements'];
            $course_attempt_state['lessons'] = $course_state['lessons'];
        }
    }

    // create new course attempt
    $course_state_params['stateId'] = $course_attempt_state_id;
    $course_state_params['registration'] = $seo->id;

    if ($attempt_exists) {
        $lrs_api->activity_states()
            ->update_resource($course_attempt_state)->with_query_params($course_state_params);
    } else {
        $lrs_api->activity_states()
            ->create_resource($course_attempt_state)->with_query_params($course_state_params);
    }

    // add this course activity to course and update or create.
    $course_state_params['stateId'] = $course_activity_state_id;
    unset($course_state_params['registration']);

    if ($activity_exists) {
        // add and update
        $lrs_api->activity_states()
            ->update_resource($course_activity_state)->with_query_params($course_state_params);
    } else {
        // create new activity state
        $lrs_api->activity_states()
            ->create_resource($course_activity_state)->with_query_params($course_state_params);
    }

    return $statements;
}

function terminate_all_lessons($lessons, $seo, $lrs_api) {
    $statement_builder = new xapi\StatementBuilder();
    $state_params = [
        'agent' => [
            'account' => $statement_builder->build_course_actor($seo->learner)['account'],
        ],
        'stateId' => 'https://w3id.org/xapi/scorm/attempt-state',
    ];
    $statements = [];

    foreach($lessons as $activity_id => $registration_ids) {
        foreach($registration_ids as $registration_id) {
            $state_params['activityId'] = $activity_id;
            $state_params['registration'] = $registration_id;

            // get the lesson attempt state
            $lesson_state = $lrs_api->activity_states()->get_resource()->with_query_params($state_params);
            if (!$lesson_state) continue;

            //if already terminated, continue
            if (array_key_exists('terminated', $lesson_state['lesson_states']) &&
                    $lesson_state['lesson_states']['terminated'] === true
            ) {
                continue;
            }

            // else update the lesson attempt
            $lesson_state['lesson_states']['terminated'] = true;
            $lrs_api->activity_states()->update_resource($lesson_state)->with_query_params($state_params);

            // add the terminate statement
            $statements[] = $statement_builder->build_lesson_terminate_statement(
                $lesson_state, $activity_id, $registration_id, $seo);
        }
    }

    return $statements;
}

function process_lessons($lessons, $course_state, $lrs_api, $seo) {
    $current_state = $course_state;

    foreach ($lessons as $lesson) {
        if ($lesson->attempts === 0) continue;

        if (isset($lesson->children) && count($lesson->children) > 0) {
            $current_state = process_lessons($lesson->children, $current_state, $lrs_api, $seo);
        } else {
            $current_state = process_lesson($lesson, $current_state, $lrs_api, $seo);
        }
    }

    // return [ 'statements' => [<array>], 'attempts' => [<string>] ]
    return $current_state;
}

function process_lesson($lesson, $course_state, $lrs_api, $seo) {
    $current_state = $course_state;
    $statement_builder = new xapi\StatementBuilder();
    $activity_state_id = 'https://w3id.org/xapi/scorm/activity-state';
    $attempt_state_id = 'https://w3id.org/xapi/scorm/attempt-state';
    $registration_id = uuid();
    $is_new_attempt = true;
    $activity_exists = true;
    $attempt_diff = 0;
    $lesson_activity_id = $statement_builder->build_lesson_activity_id($lesson->id);

    $state_params = [
        'activityId' => $lesson_activity_id,
        'agent' => [
            'account' => $statement_builder->build_course_actor($seo->learner)['account'],
        ],
    ];

    // get the lesson activity state.
    $state_params['stateId'] = $activity_state_id;
    $activity_state = $lrs_api->activity_states()->get_resource()->with_query_params($state_params);

    //   if it does not exists then we need to create a new one - later.
    if (!$activity_state) {
        $activity_state = [ 'attempts' => [], 'seo' => $lesson ];
        $activity_exists = false;
    }


    // Set the attempt details for this session
    $current_attempt_state = [ 'title' => $lesson->title ];
    if (isset($lesson->location)) $current_attempt_state['location'] = $lesson->location;
    if (isset($lesson->timeTracked)) {
        $current_attempt_state['total_time'] = 
            $statement_builder->get_iso8601_duration($statement_builder->get_duration_seconds($lesson->timeTracked));
    }


    // Has this lesson been attempted for this course attempt?
    $has_been_attempted = array_key_exists($lesson_activity_id, $current_state['lessons']);
    if ($has_been_attempted) {
        // If the lesson activity has been attempted, then we need to find out the disposition of this attempt.

        // get lesson attempt diff
        $lesson_attempts = $current_state['lessons'][$lesson_activity_id];
        $attempt_diff = intval($lesson->attempts) - count($lesson_attempts);

        if ($attempt_diff === 0) {
            // This is the most recent existing attempt. Just update attempt.
            $registration_id = end($lesson_attempts);
            $is_new_attempt = false;

            // The activity state and attempt state should already exist
            //  Get the the attempt state to update it with new states.
            $state_params['stateId'] = $attempt_state_id;
            $state_params['registration'] = $registration_id;
            $attempt_state = $lrs_api->activity_states()->get_resource()->with_query_params($state_params);
            if (!$attempt_state) throw new ErrorException("No attempt state for lesson registration {$registration_id}");

            // If the new 'total_time' is not equal to the last 'total_time', then it can be assumed something happened in this lesson.
            if ($attempt_state['total_time'] !== $current_attempt_state['total_time']) {
                // create the any new statements and attempt states for this lesson attempt.
                // [ 'statements' => [<statements>], 'lesson_states' => [ 'intialized' => true, ... ] ];
                $lesson_state = get_lesson_attempt_states(
                    $lesson, $registration_id, $attempt_state['lesson_states'], $seo, $lrs_api);
                $current_state['statements'] = array_merge($current_state['statements'], $lesson_state['statements']);
                
                // update the attempt state with new states.
                $current_attempt_state['lesson_states'] = $lesson_state['lesson_states'];
                $attempt_state = $lrs_api->activity_states()
                    ->update_resource($current_attempt_state)
                    ->with_query_params($state_params);
            }
        }
    } else {
        // This lesson activity has never been attempted for this course attempt.

        // Add the lesson id to the course attempts
        $current_state['lessons'][$lesson_activity_id] = [];
        $attempt_diff = intval($lesson->attempts);
    }

    if ($is_new_attempt) {
        if ($has_been_attempted) {
            // If there is an attempt from a previous session it can be terminated.
            // get the last entry in the course attempt state for this lesson id.
            $last_attempt_id = end($current_state['lessons'][$lesson_activity_id]);

            // get the attempt state
            $state_params['stateId'] = $attempt_state_id;
            $state_params['registration'] = $last_attempt_id;
            $last_attempt_state = $lrs_api->activity_states()->get_resource()->with_query_params($state_params);

            if (!array_key_exists('terminated', $last_attempt_state['lesson_states'])) {
                $last_attempt_state['lesson_states']['terminated'] = true;
                $lrs_api->activity_states()->update_resource($last_attempt_state)->with_query_params($state_params);

                $current_state['statements'][] = $statement_builder->build_lesson_terminate_statement(
                    $last_attempt_state, $lesson_activity_id, $last_attempt_id, $seo);
            }
        }

        if ($attempt_diff > 1) {
            // If there was more than 1 attempt made in this session, then we have missing attempts and
            //  must at least account for their initialization and termination before the current attempt.
            $state_params['stateId'] = $attempt_state_id;

            $missing_attempts = create_missing_attempts(
                $lesson, $attempt_diff - 1, $state_params, $lrs_api, $seo);
            $current_state['statements'] = array_merge($current_state['statements'], $missing_attempts['statements']);
            $current_state['lessons'][$lesson_activity_id] =
                array_merge($current_state['lessons'][$lesson_activity_id], $missing_attempts['attempts']);
            $activity_state['attempts'] = array_merge($activity_state['attempts'], $missing_attempts['attempts']);
        }

        // add the lesson initialize statement
        $current_state['statements'][] = $statement_builder->build_lesson_initialize_statement($lesson, $registration_id, $seo);

        // add to the lesson activity state attempts
        $activity_state['attempts'][] = $registration_id;

        // add to course lesson id attempts
        $current_state['lessons'][$lesson_activity_id][] = $registration_id;

        // get any lesson states and statements
        $lesson_state = get_lesson_attempt_states($lesson, $registration_id, [ 'initialized' => true ], $seo, $lrs_api);

        // assign the new lesson states to the attempt state
        $current_attempt_state['lesson_states'] = $lesson_state['lesson_states'];

        // add the new lesson state statements
        $current_state['statements'] = array_merge($current_state['statements'], $lesson_state['statements']);

        // create the new attempt state.
        $state_params['stateId'] = $attempt_state_id;
        $state_params['registration'] = $registration_id;
        $attempt_state = $lrs_api->activity_states()->create_resource($current_attempt_state)->with_query_params($state_params);
    }

    // if there was no lesson activity state for this lesson id create it, otherwise update it.
    $state_params['stateId'] = $activity_state_id;
    unset($state_params['registration']);

    if ($activity_exists) {
        $lrs_api->activity_states()->update_resource($activity_state)->with_query_params($state_params);
    } else {
        $lrs_api->activity_states()->create_resource($activity_state)->with_query_params($state_params);
    }

    return $current_state;
}

function create_missing_attempts($lesson, $missing_attempt_count, $state_params, $lrs_api, $seo) {
    $statements = [];
    $attempts = [];
    $statement_builder = new xapi\StatementBuilder();

    foreach(range(1, $missing_attempt_count) as $missing_attempt) {
        $registration_id = uuid();

        // add registration id to attempts.
        $attempts[] = $registration_id;
        
        $state_params['registration'] = $registration_id;
        $missing_lesson_attempt_state = [
            'total_time' => 'PT0S',
            'lesson_states' => [ 'initialized' => true, 'terminated' => true ]
        ];
        $lrs_api->activity_states()
            ->create_resource($missign_lesson_attempt_state)
            ->with_query_params($state_params);

        // add initialize statement.
        $statements[] = $statement_builder->build_lesson_initialize_statement($lesson, $registration_id, $seo);

        // add terminate statement.
        $statements[] = $statement_builder->build_lesson_terminate_statement(
            $missing_lesson_attempt_state, $state_params['activityId'], $registration_id, $seo);
    }

    return [ 'statements' => $statements, 'attempts' => $attempts ];
}

function get_lesson_attempt_states($lesson, $registration_id, $lesson_states, $seo, $lrs_api) {
    $statements = [];
    $new_lesson_states = $lesson_states;
    $statement_builder = new xapi\StatementBuilder();

    // resumed.
    // lesson.rumtime.entry = 'resume'.
    if ($lesson->runtime->entry === 'resume' && array_key_exists('suspended', $lesson_states)) {
        $statements[] = $statement_builder->build_lesson_resumed_statement($lesson, $registration_id, $seo);
        $new_lesson_states['resumed'] = true;
    }

    // scored
    // lesson.runtime.scoreScaled is set there is something in there and there either
    //      no lesson_states.scored or lesson_states.score !== lesson.runtime.scoreScaled
    if (isset($lesson->runtime->scoreScaled) && $lesson->runtime->scoreScaled && (
            !array_key_exists('scored', $lesson_states) || (array_key_exists('score', $lesson_states) && (
                $lesson_states['score'] !== $lesson->runtime->scoreScaled)))
    ) {
        $statements[] = $statement_builder->build_lesson_scored_statement(
            $lesson, $lesson->runtime->scoreScaled, $registration_id, $seo);
        $new_lesson_states['scored'] = true;
        $new_lesson_states['score'] = $lesson->runtime->scoreScaled;
    }


    // passed.
    // lesson->activitySuccess is 'PASSED' and lesson_states.success is not set or
    //  lesson_states.success is 'FAILED'
    if ($lesson->activitySuccess === 'PASSED' && (
            !array_key_exists('success', $lesson_states) || $lesson_states['success'] !== 'PASSED')
    ) {
        $statements[] = $statement_builder->build_lesson_passed_statement($lesson, $registration_id, $seo);
        $new_lesson_states['success'] = 'PASSED';
    }


    // failed
    // lesson->activitySuccess is 'FAILED' and lesson_states.success is not set or
    //  lesson_states.success is 'PASSED'
    if ($lesson->activitySuccess === 'FAILED' && (
            !array_key_exists('success', $lesson_states) || $lesson_states['success'] !== 'FAILED')
    ) {
        $statements[] = $statement_builder->build_lesson_failed_statement($lesson, $registration_id, $seo);
        $new_lesson_states['success'] = 'FAILED';
    }


    // completed.
    // lesson.activityCompletion is 'COMPLETE' and lesson_states.completed is not set.
    if ($lesson->activityCompletion === 'COMPLETED' && !array_key_exists('completed', $lesson_states)) {
        $statements[] = $statement_builder->build_lesson_completed_statement($lesson, $registration_id, $seo);
        $new_lesson_states['completed'] = true;
    }

    // suspended.
    // lesson.runtime.exit = 'suspend' 
    if ($lesson->runtime->exit === 'suspend') {
        $statements[] = $statement_builder->build_lesson_suspended_statement($lesson, $registration_id, $seo);
        $new_lesson_states['suspended'] = true;
    }
    
    return [ 'statements' => $statements, 'lesson_states' => $new_lesson_states ];
}

?>
