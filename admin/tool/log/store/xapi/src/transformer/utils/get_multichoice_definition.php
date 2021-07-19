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

use src\transformer\utils as utils;

defined('MOODLE_INTERNAL') || die();

function get_multichoice_definition(array $config, \stdClass $questionattempt, \stdClass $question, $lang, $interactiontype = 'choice') {

    // if ($config['send_response_choices']) {
    $repo = $config['repo'];
    $formattedRightAnswer = $interactiontype === 'choice' ?
        explode('; ', utils\get_string_html_removed($questionattempt->rightanswer)) :
        explode('} {', rtrim(ltrim($questionattempt->rightanswer, '{'), '}'));

    $answers = $repo->read_records('question_answers', [
        'question' => $questionattempt->questionid
    ]);
    
    $choicesids = array_map(function ($item) {
        return array(
            'id' => 'choice'.$item->id,
            'description' => utils\get_string_html_removed($item->answer)
        );
    }, $answers);

    $choices = array_map(function ($answer) use ($lang) {
        $formattedAnswer = utils\get_string_html_removed($answer->answer);

        return [
            "id" => "choice".$answer->id,
            "description" => [
                $lang => $formattedAnswer
            ]
        ];
    }, $answers);

    $correctResponesPattern = array_reduce(
        $formattedRightAnswer,
        function ($reduction, $selection) use ($choicesids) {
            foreach ($choicesids as $choice) {
                $v = utils\get_value($choice, 'description');
                if (strtoupper($v) === strtoupper($selection)) {
                    $selectionkey = utils\get_value($choice, 'id');
                }
            }
            $reduction = $reduction . $selectionkey . '[,]';
            return $reduction;
        },
        ''
    );
    $correctResponesPattern = [utils\str_replace_last('[,]', '', $correctResponesPattern)];

    return [
        'type' => 'http://adlnet.gov/expapi/activities/cmi.interaction',
        'name' => [
            $lang => utils\get_string_html_removed($question->name)
        ],
        'description' => [
            $lang => utils\get_string_html_removed($question->questiontext)
        ],
        'interactionType' => $interactiontype,
        'correctResponsesPattern' => $correctResponesPattern,
        // Need to pull out id's that are appended during array_map so json parses it correctly as an array.
        'choices' => array_values($choices)
    ];
}
