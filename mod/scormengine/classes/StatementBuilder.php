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

namespace xapi;
class StatementBuilder {
    const ACTOR_HOMEPAGE = 'https://edipi.navy.mil';
    const SCORM_CONTEXT_CATEGORY = [
        'id' => 'https://w3id.org/xapi/scorm',
        'definition' => [
            'type' => 'http://adlnet.gov/expapi/activities/profile'
        ]
    ];
    const NETC_CONTEXT_CATEGORY = [
        'id' => 'https://w3id.org/xapi/netc/v1.0',
        'definition' => [
            'type' => 'http://adlnet.gov/expapi/activities/profile'
        ]
    ];
    const ELEARNING_CONTEXT_CATEGORY = [
        'id' => 'https://w3id.org/xapi/netc-e-learning/v1.0',
        'definition' => [
            'type' => 'http://adlnet.gov/expapi/activities/profile'
        ]
    ];
    const ASSESSMENT_CONTEXT_CATEGORY = [
        'id' => 'https://w3id.org/xapi/netc-assessment/v1.0',
        'definition' => [
            'type' => 'http://adlnet.gov/expapi/activities/profile'
        ]
    ];
    const SCORM_PLATFORM = 'SCORM Engine 20.1';
    const ACTIVITY_ID_PREFIX = 'https://navy.mil/netc/xapi/activities';


    public function get_iso8601_duration($secondsstring) {
        return "PT{$secondsstring}S";
    }

    public function get_duration_seconds($timetracked) {
        sscanf($timetracked, "%d:%d:%d.%d", $hours, $minutes, $seconds, $milliseconds);
        return $hours * 3600 + $minutes * 60 + $seconds + $milliseconds / 100;
    }

    public function build_course_actor($learner) {
        return [
            'name' => "{$learner->firstName} {$learner->lastName}",
            'account' => [
                'homePage' => self::ACTOR_HOMEPAGE,
                'name' => "{$learner->id}",
            ],
            'objectType' => 'Agent'
        ];
    }

    public function build_course_verb($verbid, $verbdisplay) {
        return [
            'id' => $verbid,
            'display' => [
                'en' => $verbdisplay
            ]
        ];
    }

    public function build_course_activity_id($courseid) {
        return self::ACTIVITY_ID_PREFIX."/courses/{$courseid}";
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

    public function build_course_context($registrationid) {
        return [
            'contextActivities' => [
                'category' => [
                    self::NETC_CONTEXT_CATEGORY,
                    self::ELEARNING_CONTEXT_CATEGORY,
                ]
            ],
            'registration' => $registrationid,
            'platform' => self::SCORM_PLATFORM,
            'extensions' => [
                'https://w3id.org/xapi/netc/extensions/launch-location' => 'Ashore',
                'https://w3id.org/xapi/netc/extensions/school-center' => "Center for Naval Aviation Technical Training (CNATT)",
                'https://w3id.org/xapi/netc/extensions/user-agent' => $_SERVER['HTTP_USER_AGENT'],
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
            $result['score']['scaled'] = intval($seo->score->scaled) / 100;
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

    public function build_lesson_activity_id($lessonid) {
        return self::ACTIVITY_ID_PREFIX."/lessons/{$lessonid}";
    }

    public function build_lesson_initialize_statement($lesson, $registrationid, $seo) {
        return [
            'actor' => $this->build_course_actor($seo->learner),
            'verb' => $this->build_course_verb('http://adlnet.gov/expapi/verbs/initialized', 'initialized'),
            'object' => $this->build_lesson_object(
                $this->build_lesson_activity_id($lesson->id), $lesson->title),
            'context' => $this->build_lesson_context($registrationid, $seo->course),

        ];
    }

    public function build_lesson_terminate_statement($lessonstate, $lessonactivityid, $registrationid, $seo) {
        return [
            'actor' => $this->build_course_actor($seo->learner),
            'verb' => $this->build_course_verb('http://adlnet.gov/expapi/verbs/terminated', 'terminated'),
            'object' => $this->build_lesson_object($lessonactivityid, $lessonstate['title']),
            'context' => $this->build_lesson_context($registrationid, $seo->course),
            'result' => $this->build_lesson_result($lessonstate),
        ];
    }

    public function build_lesson_result($lessonstate) {
        $result = [
            'duration' => $lessonstate['total_time'],
        ];

        if (array_key_exists('success', $lessonstate['lesson_states'])) {
            $result['success'] = $lessonstate['lesson_states']['success'] === 'FAILED' ? false : true;
        }

        if (array_key_exists('completed', $lessonstate['lesson_states'])) {
            $result['completion'] = $lessonstate['lesson_states']['completed'];
        }

        if (array_key_exists('score', $lessonstate['lesson_states'])) {
            $result['score'] = [ 'scaled' => $lessonstate['lesson_states']['score'] ];
        }

        return $result;
    }

    public function build_lesson_object($lessonactivityid, $lessontitle) {
        return [
            'id' => $lessonactivityid,
            'definition' => [
                'name' => [
                    'en' => $lessontitle
                ],
                'type' => 'http://adlnet.gov/expapi/activities/lesson'
            ]
        ];
    }

    public function build_lesson_context($registrationid, $course) {
        return [
            'contextActivities' => [
                'grouping' => [ $this->build_course_object($course) ],
                'category' => [
                    self::SCORM_CONTEXT_CATEGORY,
                    self::NETC_CONTEXT_CATEGORY,
                    self::ELEARNING_CONTEXT_CATEGORY,
                ]
            ],
            'registration' => $registrationid,
            'platform' => self::SCORM_PLATFORM,
            'extensions' => [
                'https://w3id.org/xapi/netc/extensions/launch-location' => 'Ashore',
                'https://w3id.org/xapi/netc/extensions/school-center' => "Center for Naval Aviation Technical Training (CNATT)",
                'https://w3id.org/xapi/netc/extensions/user-agent' => $_SERVER['HTTP_USER_AGENT'],
            ]
        ];
    }

    public function build_lesson_resumed_statement($lesson, $registrationid, $seo) {
        return [
            'actor' => $this->build_course_actor($seo->learner),
            'verb' => $this->build_course_verb('http://adlnet.gov/expapi/verbs/resumed', 'resumed'),
            'object' => $this->build_lesson_object($this->build_lesson_activity_id($lesson->id), $lesson->title),
            'context' => $this->build_lesson_context($registrationid, $seo->course),

        ];
    }

    public function build_lesson_suspended_statement($lesson, $registrationid, $seo) {
        return [
            'actor' => $this->build_course_actor($seo->learner),
            'verb' => $this->build_course_verb('http://adlnet.gov/expapi/verbs/suspended', 'suspended'),
            'object' => $this->build_lesson_object($this->build_lesson_activity_id($lesson->id), $lesson->title),
            'context' => $this->build_lesson_context($registrationid, $seo->course),
        ];
    }

    public function build_lesson_scored_statement($lesson, $scorescaled, $registrationid, $seo) {
        return [
            'actor' => $this->build_course_actor($seo->learner),
            'verb' => $this->build_course_verb('http://adlnet.gov/expapi/verbs/scored', 'scored'),
            'object' => $this->build_lesson_object($this->build_lesson_activity_id($lesson->id), $lesson->title),
            'context' => $this->build_lesson_context($registrationid, $seo->course),
            'result' => [
                'score' => [
                    'scaled' => $scorescaled,
                ],
            ],

        ];
    }

    public function build_lesson_passed_statement($lesson, $registrationid, $seo) {
        return [
            'actor' => $this->build_course_actor($seo->learner),
            'verb' => $this->build_course_verb('http://adlnet.gov/expapi/verbs/passed', 'passed'),
            'object' => $this->build_lesson_object($this->build_lesson_activity_id($lesson->id), $lesson->title),
            'context' => $this->build_lesson_context($registrationid, $seo->course),
        ];
    }

    public function build_lesson_failed_statement($lesson, $registrationid, $seo) {
        return [
            'actor' => $this->build_course_actor($seo->learner),
            'verb' => $this->build_course_verb('http://adlnet.gov/expapi/verbs/failed', 'failed'),
            'object' => $this->build_lesson_object($this->build_lesson_activity_id($lesson->id), $lesson->title),
            'context' => $this->build_lesson_context($registrationid, $seo->course),
        ];
    }

    public function build_lesson_completed_statement($lesson, $registrationid, $seo) {
        return [
            'actor' => $this->build_course_actor($seo->learner),
            'verb' => $this->build_course_verb('http://adlnet.gov/expapi/verbs/completed', 'completed'),
            'object' => $this->build_lesson_object($this->build_lesson_activity_id($lesson->id), $lesson->title),
            'context' => $this->build_lesson_context($registrationid, $seo->course),
        ];
    }

    public function build_interaction_activity_id($interactionid) {
        return self::ACTIVITY_ID_PREFIX."/cmi.interactions/{$interactionid}";
    }

    public function build_assessment_activity_id($assessmentid) {
        return self::ACTIVITY_ID_PREFIX."/assessments/{$assessmentid}";
    }

    public function get_interaction_type($interactiontype) {
        if (preg_match('/choice/i', $interactiontype)) {
            return 'choice';
        } else if (preg_match('/true|false/i', $interactiontype)) {
            return 'true-false';
        } else if (preg_match('/long|fill|in/i', $interactiontype)) {
            return 'long-fill-in';
        } else if (preg_match('/fill|in/i', $interactiontype)) {
            return 'fill-in';
        } else if (preg_match('/matching/i', $interactiontype)) {
            return 'matching';
        } else if (preg_match('/performance/i', $interactiontype)) {
            return 'performance';
        } else if (preg_match('/sequencing/i', $interactiontype)) {
            return 'sequencing';
        } else if (preg_match('/like|rt/i', $interactiontype)) {
            return 'likert';
        } else if (preg_match('/numeric/i', $interactiontype)) {
            return 'numeric';
        } else {
            return 'other';
        }
    }

    public function build_interaction_object($interactionactivityid, $interactiondescription, $interactiontype) {
        return [
            'id' => $interactionactivityid,
            'definition' => [
                'description' => [
                    'en' => $interactiondescription,
                ],
                'type' => 'http://adlnet.gov/expapi/activities/cmi.interaction',
                'interactionType' => $this->get_interaction_type($interactiontype),
            ],
        ];
    }

    public function build_assessment_object($assessmentactivityid) {
        return [
            'id' => $assessmentactivityid,
            'definition' => [
                'type' => 'http://adlnet.gov/expapi/activities/assessment'
            ],
        ];
    }

    public function build_interaction_context($assessmentid, $lessonregistrationid, $lesson, $course) {
        return [
            'contextActivities' => [
                'parent' => [
                    $this->build_assessment_object($this->build_assessment_activity_id($assessmentid)),
                ],
                'grouping' => [
                    $this->build_course_object($course),
                    $this->build_lesson_object($this->build_lesson_activity_id($lesson->id), $lesson->title)
                ],
                'category' => [
                    self::SCORM_CONTEXT_CATEGORY,
                    self::NETC_CONTEXT_CATEGORY,
                    self::ELEARNING_CONTEXT_CATEGORY,
                    self::ASSESSMENT_CONTEXT_CATEGORY,
                ]
            ],
            'registration' => $lessonregistrationid,
            'platform' => self::SCORM_PLATFORM,
            'extensions' => [
                'https://w3id.org/xapi/netc/extensions/launch-location' => 'Ashore',
                'https://w3id.org/xapi/netc/extensions/school-center' => "Center for Naval Aviation Technical Training (CNATT)",
                'https://w3id.org/xapi/netc/extensions/user-agent' => $_SERVER['HTTP_USER_AGENT'],
            ]
        ];
    }

    public function build_interaction_responded_statement($interaction, $lesson, $lessonregistrationid, $learner, $course, $assessmentid) {
        $statement = [
            'actor' => $this->build_course_actor($learner),
            'verb' => $this->build_course_verb('http://adlnet.gov/expapi/verbs/responded', 'responded'),
            'object' => $this->build_interaction_object(
                $this->build_interaction_activity_id($interaction->id), $interaction->description, $interaction->type),
            'context' => $this->build_interaction_context(
                $assessmentid, $lessonregistrationid, $lesson, $course),
        ];

        if ($interaction->learnerResponse !== '') {
            $statement['result']['response'] = $interaction->learnerResponse;
            if ($interaction->result) {
                $statement['result']['success'] = $interaction->result === 'correct' ? true : false;
            }
        }

        return $statement;
    }
}

