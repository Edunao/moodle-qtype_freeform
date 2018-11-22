define(['jquery', 'local_freeform/freeform_execution_lib'], function($, freeformExecutionLib) {

    return {
        firstTime : true,

        init: function(expressions, uniqueId, answersData, correction, htmlTags) {
            console.log('Freeform_execute init',expressions, uniqueId, answersData, correction, htmlTags);

            // construct the name of the tag that holds the official aggregated answer set
            let answerTagName = 'ffq_' + uniqueId + '_answer';
            let answerTag = $('#'+answerTagName);
            let readOnly = (answerTag.length === 0);

            // update the DOM, filling in pre-existent answers
            freeformExecutionLib.reset();
            freeformExecutionLib.setExpressions(expressions, uniqueId);
            $('#ffq_'+uniqueId+'_answer').val(answersData);

            // get the subwanswers
            let subanswers = JSON.parse(answersData);
            subanswers = Object.values(subanswers);

            let subanswersCount = subanswers ? subanswers.length : 0;


            // loop over the sub-answers backwards in order to be sure to setup all parts of multi-part expressions before rendering them
            for (var i = subanswersCount; --i >= 0; ) {
                if (!subanswers[i]) {
                    continue;
                }

                let instanceName = 'ffq_' + uniqueId + '_' + i;
                $('input[name='+instanceName+']').val(subanswers[i]);

                let feedback = '';

                // get the answer feedback
                if (typeof correction[i] !== 'undefined') {
                    feedback = correction[i] == 1 ? htmlTags['right'] : htmlTags['wrong'];
                }

                freeformExecutionLib.updateLabel(instanceName, feedback);
            }

            freeformExecutionLib.setAnswers(uniqueId,subanswers);

            // do stuff that only needs to be done once and not once for each question on the page
            if (this.firstTime === true) {
                this.firstTime = false;

                // if we're not in read-only mode hook global events
                if (readOnly === false) {
                    // init the execution lib, regestering callback to be triggered on input change
                    freeformExecutionLib.init(function (data) {
                        // derive the name of the tag that holds the official aggregated answer set
                        let answerTagName = 'ffq_' + data.uniqueId + '_answer';
                        // store away the aggregated answers
                        let answerString = JSON.stringify(data.answers);
                        $('#' + answerTagName).val(answerString);
                    });

                    $('#responseform').on('submit', function () {
                        freeformExecutionLib.update_current_label(null);
                    });
                }

                // if we are in read-only mode then update the DOM to make it look nice in grading views and suchlike
                if (readOnly === true) {
                    // set input-set objects in DOM to hidden
                    $('.input-set').addClass('freeform-hidden');
                }
            }

            // if we're not in read-only mode hook question-related events
            if (readOnly !== true){
                freeformExecutionLib.hook_events(uniqueId);
            }
        },

        init_correct_response : function(expressions, uniqueId, answersData) {

            let subanswers = JSON.parse(answersData);
            let subanswersCount = subanswers ? subanswers.length : 0;
            $('#correct_ffq_'+uniqueId+'_answer').val(answersData);

            console.log('init_correct_response', expressions, uniqueId, answersData,subanswers);

            freeformExecutionLib.reset();
            freeformExecutionLib.setExpressions(expressions, uniqueId);
            freeformExecutionLib.setQuestionContextName('correct', uniqueId);

            // loop over the sub-answers backwards in order to be sure to setup all parts of multi-part expressions before rendering them
            for (var i = subanswersCount; --i >= 0; ) {
                if (!subanswers[i]) {
                    continue;
                }
                let instanceName = 'correct_ffq_' + uniqueId + '_' + i;
                $('input[name='+instanceName+']').val(subanswers[i]);

                // replace the label text by the right answer
                freeformExecutionLib.updateLabel(instanceName);
            }
        }
    };
});
