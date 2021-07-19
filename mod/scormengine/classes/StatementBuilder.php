<?php namespace xapi;

class StatementBuilder {
    const actor_homepage = 'https://edipi.navy.mil';
    const scorm_context_category = [
        'id' => 'https://w3id.org/xapi/scorm',
        'definition' => [
            'type' => 'http://adlnet.gov/expapi/activities/profile'
        ]
    ];
    const netc_context_category = [
        'id' => 'https://w3id.org/xapi/netc/v1.0',
        'definition' => [
            'type' => 'http://adlnet.gov/expapi/activities/profile'
        ]
    ];
    const elearning_context_category = [
        'id' => 'https://w3id.org/xapi/netc-e-learning/v1.0',
        'definition' => [
            'type' => 'http://adlnet.gov/expapi/activities/profile'
        ]
    ];
    const scorm_platform = 'SCORM Engine 20.1';
    const activity_id_prefix = 'https://navy.mil/netc/xapi/activities';

 
    public function get_iso8601_duration($seconds_string) {
        return "PT{$seconds_string}S";
    }

    public function get_duration_seconds($time_tracked) {
        sscanf($time_tracked, "%d:%d:%d.%d", $hours, $minutes, $seconds, $milliseconds);
        return $hours * 3600 + $minutes * 60 + $seconds + $milliseconds / 100;
    }

    public function build_course_actor($learner) {
        return [
            'name' => "{$learner->firstName} {$learner->lastName}",
            'account' => [
                'homePage' => self::actor_homepage,
                'name' => "{$learner->id}",
            ],
            'objectType' => 'Agent'
        ];
    }

    public function build_course_verb($verb_id, $verb_display) {
        return [
            'id' => $verb_id,
            'display' => [
                'en' => $verb_display
            ]
        ];
    }

    public function build_course_activity_id($course_id) {
        return self::activity_id_prefix."/courses/{$course_id}";
    }
    
    public function build_course_object($course) {
        return [
            'id' => $this->build_course_activity_id($course->id),
            'definition' => [
                'name' => [
                    'en' => $course->title
                ],
                'type' => 'http://adlnet.gov/expapi/activities/course'
            ]
        ];
    }
    
    public function build_course_context($registration_id) {
        return [
            'contextActivities' => [
                'category' => [
                    self::netc_context_category,
                    self::elearning_context_category,
                ]
            ],
            'registration' => $registration_id,
            'platform' => self::scorm_platform,
            'extensions' => [
                'https://w3id.org/xapi/netc/extensions/launch-location' => 'Ashore',
            ]
        ];
    }
    
    public function build_course_result($seo) {
        $result = [
            'duration' => $this->get_iso8601_duration($seo->totalSecondsTracked)
        ];
    
        if ($seo->registrationSuccess !== 'UNKNOWN') {
            $result['success'] = $seo->registrationSuccess === 'FAILED' ? false : true;
        }
    
        if ($seo->registrationCompletion !== 'UNKNOWN') {
            $result['completion'] = $seo->registrationCompletion === 'INCOMPLETE' ? false : true;
        }
    
        if (isset($seo->score->scaled)) {
            $result['score']['scaled'] = intval($seo->score->scaled)/100;
        }
    
        return $result;
    }
    
    public function build_initialize_statement($seo) {
        return [
            'actor' => $this->build_course_actor($seo->learner),
            'verb' => $this->build_course_verb('http://adlnet.gov/expapi/verbs/initialized', 'initialized'),
            'object' => $this->build_course_object($seo->course),
            'context' => $this->build_course_context($seo->id),
            'timestamp' => date('c', strtotime($seo->lastAccessDate))
        ];
    }
    
    public function build_terminate_statement($seo) {
        return [
            'actor' => $this->build_course_actor($seo->learner),
            'verb' => $this->build_course_verb('http://adlnet.gov/expapi/verbs/terminated', 'terminated'),
            'object' => $this->build_course_object($seo->course),
            'context' => $this->build_course_context($seo->id),
            'result' => $this->build_course_result($seo),
            'timestamp' => date('c', strtotime("+{$seo->totalSecondsTracked} second", strtotime($seo->lastAccessDate)))
        ];
    }

    public function build_lesson_activity_id($lesson_id) {
        return self::activity_id_prefix."/lessons/{$lesson_id}";
    }

    public function build_lesson_initialize_statement($lesson, $registration_id, $seo) {
        return [
            'actor' => $this->build_course_actor($seo->learner),
            'verb' => $this->build_course_verb('http://adlnet.gov/expapi/verbs/initialized', 'initialized'),
            'object' => $this->build_lesson_object(
                $this->build_lesson_activity_id($lesson->id), $lesson->title),
            'context' => $this->build_lesson_context($registration_id, $seo->course),

        ];
    }

    public function build_lesson_terminate_statement($lesson_state, $lesson_activity_id, $registration_id, $seo) {
        return [
            'actor' => $this->build_course_actor($seo->learner),
            'verb' => $this->build_course_verb('http://adlnet.gov/expapi/verbs/terminated', 'terminated'),
            'object' => $this->build_lesson_object($lesson_activity_id, $lesson_state['title']),
            'context' => $this->build_lesson_context($registration_id, $seo->course),
            'result' => $this->build_lesson_result($lesson_state),
        ];
    }

    public function build_lesson_result($lesson_state) {
        $result = [
            'duration' => $lesson_state['total_time'],
        ];
        
        if (array_key_exists('success', $lesson_state['lesson_states'])) {
            $result['success'] = $lesson_state['lesson_states']['success'] === 'FAILED' ? false : true;
        }

        if (array_key_exists('completed', $lesson_state['lesson_states'])) {
            $result['completion'] = $lesson_state['lesson_states']['completed'];
        }

        if (array_key_exists('score', $lesson_state['lesson_states'])) {
            $result['score'] = [ 'scaled' => $lesson_state['lesson_states']['score'] ];
        }
    
        return $result;
    }

    public function build_lesson_object($lesson_activity_id, $lesson_title) {
        return [
            'id' => $lesson_activity_id,
            'definition' => [
                'name' => [
                    'en' => $lesson_title
                ],
                'type' => 'http://adlnet.gov/expapi/activities/lesson'
            ]
        ];
    }

    public function build_lesson_context($registration_id, $course) {
        return [
            'contextActivities' => [
                'grouping' => [ $this->build_course_object($course) ],
                'category' => [
                    self::scorm_context_category,
                    self::netc_context_category,
                    self::elearning_context_category,
                ]
            ],
            'registration' => $registration_id,
            'platform' => self::scorm_platform,
            'extensions' => [
                'https://w3id.org/xapi/netc/extensions/launch-location' => 'Ashore',
                'https://w3id.org/xapi/netc/extensions/school-center' => "Center for Naval Aviation Technical Training (CNATT)",
                'https://w3id.org/xapi/netc/extensions/user-agent' => $_SERVER['HTTP_USER_AGENT'],
            ]
        ];
    }

    public function build_lesson_resumed_statement($lesson, $registration_id, $seo) {
        return [
            'actor' => $this->build_course_actor($seo->learner),
            'verb' => $this->build_course_verb('http://adlnet.gov/expapi/verbs/resumed', 'resumed'),
            'object' => $this->build_lesson_object($this->build_lesson_activity_id($lesson->id), $lesson->title),
            'context' => $this->build_lesson_context($registration_id, $seo->course),

        ];
    }

    public function build_lesson_suspended_statement($lesson, $registration_id, $seo) {
        return [
            'actor' => $this->build_course_actor($seo->learner),
            'verb' => $this->build_course_verb('http://adlnet.gov/expapi/verbs/suspended', 'suspended'),
            'object' => $this->build_lesson_object($this->build_lesson_activity_id($lesson->id), $lesson->title),
            'context' => $this->build_lesson_context($registration_id, $seo->course),
        ];
    }

    public function build_lesson_scored_statement($lesson, $score_scaled, $registration_id, $seo) {
        return [
            'actor' => $this->build_course_actor($seo->learner),
            'verb' => $this->build_course_verb('http://adlnet.gov/expapi/verbs/scored', 'scored'),
            'object' => $this->build_lesson_object($this->build_lesson_activity_id($lesson->id), $lesson->title),
            'context' => $this->build_lesson_context($registration_id, $seo->course),
            'result' => [
                'score' => [
                    'scaled' => $score_scaled,
                ],
            ],

        ];
    }

    public function build_lesson_passed_statement($lesson, $registration_id, $seo) {
        return [
            'actor' => $this->build_course_actor($seo->learner),
            'verb' => $this->build_course_verb('http://adlnet.gov/expapi/verbs/passed', 'passed'),
            'object' => $this->build_lesson_object($this->build_lesson_activity_id($lesson->id), $lesson->title),
            'context' => $this->build_lesson_context($registration_id, $seo->course),
        ];
    }

    public function build_lesson_failed_statement($lesson, $registration_id, $seo) {
        return [
            'actor' => $this->build_course_actor($seo->learner),
            'verb' => $this->build_course_verb('http://adlnet.gov/expapi/verbs/failed', 'failed'),
            'object' => $this->build_lesson_object($this->build_lesson_activity_id($lesson->id), $lesson->title),
            'context' => $this->build_lesson_context($registration_id, $seo->course),
        ];
    }

    public function build_lesson_completed_statement($lesson, $registration_id, $seo) {
        return [
            'actor' => $this->build_course_actor($seo->learner),
            'verb' => $this->build_course_verb('http://adlnet.gov/expapi/verbs/completed', 'completed'),
            'object' => $this->build_lesson_object($this->build_lesson_activity_id($lesson->id), $lesson->title),
            'context' => $this->build_lesson_context($registration_id, $seo->course),
        ];
    }
}

?>
