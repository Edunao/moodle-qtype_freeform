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

require_once($CFG->libdir . '/questionlib.php');
require_once($CFG->dirroot . '/question/engine/lib.php');
require_once($CFG->dirroot . '/question/type/freeform/question.php');


/**
 * The freeform question type.
 */
class qtype_freeform extends question_type {
    public function extra_question_fields() {
        return [
            'qtype_freeform_options',
            'data'
            ];
    }

    public function can_analyse_responses() {
        return false();
    }

    public function move_files($questionid, $oldcontextid, $newcontextid) {
        parent::move_files($questionid, $oldcontextid, $newcontextid);
        $this->move_files_in_answers($questionid, $oldcontextid, $newcontextid);
        $this->move_files_in_hints($questionid, $oldcontextid, $newcontextid);
    }

    protected function delete_files($questionid, $contextid) {
        parent::delete_files($questionid, $contextid);
        $this->delete_files_in_answers($questionid, $contextid);
        $this->delete_files_in_hints($questionid, $contextid);
    }

    public function save_question_options($question) {
        global $DB;
        $result = new stdClass();

//        // Perform sanity checks on fractional grades.
//        $maxfraction = -1;
//        foreach ($question->answer as $key => $answerdata) {
//            if ($question->fraction[$key] > $maxfraction) {
//                $maxfraction = $question->fraction[$key];
//            }
//        }
//
//        if ($maxfraction != 1) {
//            $result->error = get_string('fractionsnomax', 'question', $maxfraction * 100);
//            return $result;
//        }

        parent::save_question_options($question);

//        $this->save_question_answers($question);

        $this->save_hints($question);
    }

    protected function fill_answer_fields($answer, $questiondata, $key, $context) {
        $answer = parent::fill_answer_fields($answer, $questiondata, $key, $context);
        $answer->answer = trim($answer->answer);
        return $answer;
    }

    protected function initialise_question_instance(question_definition $question, $questiondata) {
        parent::initialise_question_instance($question, $questiondata);
        $this->initialise_question_answers($question, $questiondata);
    }
}
