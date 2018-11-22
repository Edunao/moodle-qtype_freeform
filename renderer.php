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
require_once($CFG->dirroot . '/local/freeform/classes/expression_normaliser.php');

/**
 * Generates the output for freeform questions.
 */
class qtype_freeform_renderer extends qtype_renderer {
    public function formulation_and_controls(question_attempt $qa, question_display_options $options) {
        global $PAGE;
        // fetch previous answer data from the question attempt and identify the name of the answer field for this question attempt
        $currentanswer = $qa->get_last_qt_var('answer');

        if (empty($currentanswer)) {
            $currentanswer = '{}';
        }
        $inputname = $qa->get_qt_field_name('answer');

        // tear apart the data field
        $question = $qa->get_question();
        $data     = json_decode($question->data);
        $result   = $data->question;

        // TODO Add a tick or cross feedback image
        //        $feedbackimg = '';
        //        if ($options->correctness) {
        //            $answer = $question->get_matching_answer(array('answer' => $currentanswer));
        //            if ($answer) {
        //                $fraction = $answer->fraction;
        //            } else {
        //                $fraction = 0;
        //            }
        //            $inputattributes['class'] .= ' ' . $this->feedback_class($fraction);
        //            $feedbackimg = $this->feedback_image($fraction);
        //        }

        // TODO reactivate the code below
        //        // if the answer provided by the student isn't valid then pop up an error message to prompt for valid input
        //        if ($qa->get_state() == question_state::$invalid) {
        //            $error  = $question->get_validation_error(array('answer' => $currentanswer));
        //            $result .= html_writer::nonempty_tag('div', $error, array('class' => 'validationerror'));
        //        }

        // if this is not a read-only case then append a hidden input element to the form to hold the aggregated answers
        if (!$options->readonly) {
            $result .= '<input type="hidden" name="' . $inputname . '" id="ffq__answer"/>';
        }

        // determine the unique id of the question in order to handle several pages on the same page correctly and update the result string to incorporate it
        $questioninstance = preg_replace('/.*:(\d+)_.*/', '\1', $inputname);
        $result           = str_replace('ffq__', 'ffq_' . $questioninstance . '_', $result);

        // correction
        $correction = array();
        $htmltags   = array();
        if ($options->correctness) {
            $correction = $this->get_correction($currentanswer, $qa->get_question());

            $htmltags = [
                    'right' => $this->feedback_image(1),
                    'wrong' => $this->feedback_image(0),
            ];
        }

        // trigger inclusion and initialisation of our javascript code
        $PAGE->requires->js_call_amd('qtype_freeform/freeform_execute', 'init',
                [$data->expressions, $questioninstance, $currentanswer, $correction, $htmltags]);

        // return the result to render
        return $result;
    }

    public function get_correction($currentanswer, $question) {

        // get user answer
        $responseparts = (array) json_decode($currentanswer);
        $responseparts = array_values($responseparts);

        // get the right answer
        $decodeddata = json_decode($question->data);
        $answers     = $decodeddata->answers;

        if (count($responseparts) != count($answers)) {
            throw new Exception("Number of response parts doesn't match number of answers");
        }

        $normaliser = new \local_freeform\expression_normaliser();

        $correction = array();
        for ($i = 0; $i < count($answers); $i++) {
            $answersig      = $normaliser->normalise_expression($answers[$i]->text);
            $responsesig    = $normaliser->normalise_expression($responseparts[$i]);
            $correction[$i] = ($answersig === $responsesig) ? 1 : 0;
        }
        return $correction;
    }

    public function specific_feedback(question_attempt $qa) {
        //        $question = $qa->get_question();
        //
        //        $answer = $question->get_matching_answer(array('answer' => $qa->get_last_qt_var('answer')));
        //        if (!$answer || !$answer->feedback) {
        //            return '';
        //        }
        //
        //        return $question->format_text($answer->feedback, $answer->feedbackformat,
        //                $qa, 'question', 'answerfeedback', $answer->id);
        return '';
    }

    public function correct_response(question_attempt $qa) {
        global $PAGE;

        // if the fraction is equal to 1, we don't have to display the correct response
        if ($qa->get_fraction() == 1) {
            return get_string('answeriscorrect', 'qtype_freeform');
        }

        $question         = $qa->get_question();
        $data             = json_decode($question->data);
        $result           = $data->question;
        $inputname        = $qa->get_qt_field_name('answer');
        $questioninstance = preg_replace('/.*:(\d+)_.*/', '\1', $inputname);

        // add _correct_ to prevent name conflicts
        $result = str_replace('ffq__', 'correct_ffq_' . $questioninstance . '_', $result);

        // get an array of answers text
        $answers = [];
        foreach ($data->answers as $answer) {
            $answers[] = $answer->text;
        }
        $answers = json_encode($answers);

        // trigger inclusion and initialisation of our javascript code
        $PAGE->requires->js_call_amd('qtype_freeform/freeform_execute', 'init_correct_response', [
                $data->expressions,
                $questioninstance, $answers
        ]);

        $result = get_string('correctansweris', 'qtype_freeform', $result);

        // return the correct answer to render
        return $result;
    }

}
