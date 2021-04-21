<?php

function send_xapi_statements_from_seo($type, $seo, $url, $user, $password) {
    $statements = get_xapi_statements_from_seo($type, $seo);

    if ($statements && count($statements) > 0) {
        send_xapi_statement($statements, $url, $user, $password);
    }
}


function send_xapi_statement($statements, $url, $user, $password) {
    $ch = curl_init($url);
    $headers = array(
        'Content-Type: application/json',
        'X-Experience-API-Version: 1.0.1',
        'Authorization: Basic '. base64_encode("{$user}:{$password}"),
        'engineTenantName: default'
    );

    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_URL, $url);    
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($statements));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);

    $result = curl_exec($ch);
    curl_close($ch);
}

function get_iso8601_duration($seconds_string) {
    return "PT{$seconds_string}S";
}

function get_xapi_statements_from_seo($type, $seo) {
    $statements = [];

    if ($type === 'INITIALIZE') {
        $statements[] = buildInitializeStatement($seo);
    }

    if ($type === 'TERMINATE') {
            $statements[] = buildTerminateStatement($seo);
    }

    return $statements;
}

function buildCourseActor($learner) {
    return [
        'name' => "{$learner->firstName} {$learner->lastName}",
        'account' => [
            'homePage' => 'https://edipi.navy.mil',
            'name' => $learner->id
        ],
        'objectType' => 'Agent'
    ];
}

function buildCourseVerb($verb_id, $verb_display) {
    return [
        'id' => $verb_id,
        'display' => [
            'en' => $verb_display
        ]
    ];
}

function buildCourseObject($course) {
    return [
        'id' => "https://navy.mil/activity/{$course->id}",
        'definition' => [
            'name' => [
                'en' => $course->title
            ],
            'type' => 'http://adlnet.gov/expapi/activities/course'
        ]
    ];
}

function buildCourseContext($registration_id) {
    return [
        'contextActivities' => [
            'category' => [
                [
                    'id' => 'https://w3id.org/xapi/netc/v1.0',
                    'definition' => [
                        'type' => 'http://adlnet.gov/expapi/activities/profile'
                    ]
                ],
                [
                    'id' => 'https://w3id.org/xapi/netc-e-learning/v1.0',
                    'definition' => [
                        'type' => 'http://adlnet.gov/expapi/activities/profile'
                    ]
                ]
            ]
        ],
        'registration' => $registration_id,
        'platform' => 'SCORM Engine 20.1',
        'extensions' => [
            'https://w3id.org/xapi/netc/extensions/launch-location' => 'Ashore'
        ]
    ];
}

function buildCourseResult($seo) {
    $result = [
        'duration' => get_iso8601_duration($seo->totalSecondsTracked)
    ];

    if ($seo->registrationSuccess !== 'UNKNOWN') {
        $result['success'] = $seo->registrationSuccess === 'FAILED' ? false : true;
    }

    if ($seo->registrationCompletion !== 'UNKNOWN') {
        $result['completion'] = $seo->registrationCompletion === 'INCOMPLETE' ? false : true;
    }

    if ($seo->score && $seo->score->scaled) {
        $result['score']['scaled'] = intval($seo->score->scaled)/100;
    }

    return $result;
}

function buildInitializeStatement($seo) {
    return [
        'actor' => buildCourseActor($seo->learner),
        'verb' => buildCourseVerb('http://adlnet.gov/expapi/verbs/initialized', 'initialized'),
        'object' => buildCourseObject($seo->course),
        'context' => buildCourseContext($seo->id),
        'timestamp' => date('c', strtotime($seo->lastAccessDate))
    ];
}

function buildTerminateStatement($seo) {
    return [
        'actor' => buildCourseActor($seo->learner),
        'verb' => buildCourseVerb('http://adlnet.gov/expapi/verbs/terminated', 'terminated'),
        'object' => buildCourseObject($seo->course),
        'context' => buildCourseContext($seo->id),
        'result' => buildCourseResult($seo),
        'timestamp' => date('c', strtotime("+{$seo->totalSecondsTracked} second", strtotime($seo->lastAccessDate)))
    ];
}

?>
