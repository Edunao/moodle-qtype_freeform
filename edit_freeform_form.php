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


/**
 * Freeform question editing form definition.
 */
class qtype_freeform_edit_form extends question_edit_form {

    protected function definition_inner($mform) {
        $strcaseno              = 'no'; // get_string('caseno', 'qtype_freeform');
        $strcaseyes             = 'yes'; // get_string('caseyes', 'qtype_freeform');
        $strcasesensitive       = 'case sensitive'; // get_string('casesensitive', 'qtype_freeform');
        $strcorrectanswers      = 'Correct Answers'; // get_string('correctanswers', 'qtype_freeform');
        $strfilloutoneanswer    = 'Fill out answers'; // get_string('filloutoneanswer', 'qtype_freeform');

        // create a 'preview' widget and add it to the page just below the question entry box
        $previewhtml = '<div id="freeform-preview"><span style="color:red">Loading Preview ...</span></div>';
        $previewelem = $mform->createElement('static', 'preview', 'PREVIEW', $previewhtml);
        $mform->insertElementBefore($previewelem, 'defaultmark');

        // add a hidden element to hold quest io slot information and suchlike (to be filler in by javascript)
        $mform->addElement('hidden', 'data');
        $mform->setType('data', PARAM_RAW);

//        // add options
//        $mform->addElement('select', 'usecase', $strcasesensitive, [ $strcaseyes, $strcaseno ]);
//
//        // close the 'general' block and add instructions as a separate non-foldable block
//        $mform->addElement('static', 'answersinstruct', $strcorrectanswers, $strfilloutoneanswer);
//        $mform->closeHeaderBefore('answersinstruct');

        // add some standrard settings at the end
        $this->add_interactive_settings();

        // Load js module(s) and execute entry-point script(s)
        global $PAGE;
#        $PAGE->requires->js_call_amd('frankenstyle_path/your_js_filename', 'init', [...]);
        $PAGE->requires->js_call_amd('qtype_freeform/freeform_edit', 'init');
//        $PAGE->requires->js_call_amd('local_freeform/freeform_execution_lib', 'init');
    }

    protected function data_preprocessing($question) {
        // do stuff with fileareas and suchlike
        $question = parent::data_preprocessing($question);
        $question = $this->data_preprocessing_answers($question);
        $question = $this->data_preprocessing_hints($question);

        return $question;
    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
//        $answers = $data['answer'];
//        $answercount = 0;
//        $maxgrade = false;
//        foreach ($answers as $key => $answer) {
//            $trimmedanswer = trim($answer);
//            if ($trimmedanswer !== '') {
//                $answercount++;
//                if ($data['fraction'][$key] == 1) {
//                    $maxgrade = true;
//                }
//            } else if ($data['fraction'][$key] != 0 ||
//                    !html_is_blank($data['feedback'][$key]['text'])) {
//                $errors["answeroptions[{$key}]"] = get_string('answermustbegiven', 'qtype_freeform');
//                $answercount++;
//            }
//        }
//        if ($answercount==0) {
//            $errors['answeroptions[0]'] = get_string('notenoughanswers', 'qtype_freeform', 1);
//        }
//        if ($maxgrade == false) {
//            $errors['answeroptions[0]'] = get_string('fractionsnomax', 'question');
//        }
        return $errors;
    }

    public function qtype() {
        return 'freeform';
    }
}
