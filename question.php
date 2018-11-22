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
 * Freeform Question Type
 *
 * @copyright  2018 Edunao SAS (contact@edunao.com)
 * @author     Sadge (daniel@edunao.com)
 * @package    qtype_freeform
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/question/type/questionbase.php');
require_once($CFG->dirroot . '/local/freeform/classes/expression_normaliser.php');

/**
 * Represents a freeform question.
 */
class qtype_freeform_question extends question_graded_automatically {

    public function get_expected_data() {
        return array('answer' => PARAM_RAW);
    }

    public function summarise_response(array $response) {

        if (isset($response['answer'])) {
            return $response['answer'];
        } else {
            return null;
        }
    }

    public function is_complete_response(array $response) {

        // get the user answer
        $responseparts = (array) json_decode($response['answer']);
        $responseparts = array_values($responseparts);

        // get the right answer
        $decodeddata = json_decode($this->data);
        $answers     = $decodeddata->answers;

        if (count($responseparts) != count($answers)) {
            return false;
        }

        foreach ($responseparts as $responsepart) {
            if (!$responsepart && $responsepart !== "0") {
                return false;
            }
        }

        return true;
    }

    public function is_gradable_response(array $response) {
        return true;
    }

    public function get_validation_error(array $response) {
        if ($this->is_gradable_response($response)) {
            return '';
        }
        return get_string('pleaseenterananswer', 'qtype_freeform');
    }

    public function is_same_response(array $prevresponse, array $newresponse) {
        // TODO Verify the logic here, in principle it looks correct to me but would need testing
        return question_utils::arrays_same_at_key_missing_is_blank($prevresponse, $newresponse, 'answer');
    }

    public function get_correct_response() {
        //        if ($response) {
        //            $response['answer'] = $this->clean_response($response['answer']);
        //        }
        $response['answer'] = 'toto'; // TODO: Put real code here
        return $response;
        // NOTE return null; would be valid
    }

    /**
     * Grade a response to the question, returning a fraction between
     * get_min_fraction() and get_max_fraction(), and the corresponding {@link question_state}
     * right, partial or wrong.
     *
     * @param array $response responses, as returned by
     *      {@link question_attempt_step::get_qt_data()}.
     * @return array (float, integer) the fraction, and the state.
     * @throws Exception
     */
    public function grade_response(array $response) {

        // Get the user answer
        $responseparts = (array) json_decode($response['answer']);
        $responseparts = array_values($responseparts);

        // Get the right answers
        $decodeddata = json_decode($this->data);
        $answers     = $decodeddata->answers;

        if (count($responseparts) != count($answers)) {
            throw new Exception("Number of response parts doesn't match number of answers");
        }

        $normaliser = new \local_freeform\expression_normaliser();

        $points = 0;
        for ($i = 0; $i < count($answers); $i++) {

            $answersig   = $normaliser->normalise_expression($answers[$i]->text);
            $responsesig = $normaliser->normalise_expression($responseparts[$i]);
            $points      += ($answersig === $responsesig) ? 1 : 0;
        }
        $score = $points / count($answers);

        return array($score, question_state::graded_state_for_fraction($score));
    }

}
