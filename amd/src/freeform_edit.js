define(['jquery', 'local_freeform/freeform_editor_lib', 'local_freeform/freeform_execution_lib'], function($, freeformEditorLib, freeformExecutionLib) {

    let self = {
        signature : '',

        init: function() {

            console.log("Preparing freeform question editing");
            freeformExecutionLib.init(function(data){
                // callback called on change of text for an answer input field
                freeformEditorLib.generateSignature(data.answerText, function(signature){
                    console.log("Generated Signature:", data.tag.attr('name'), data.answerText, signature);
                    let match = (signature === self.signature);
                    data.tag.parent().attr('style','background-color:'+(match?'#efe':'#fffcfc'));
                });
            });

            // the input node that we monitor for text changes. Note that depending on the user's choice of editor
            // the input node maybe within an iframe, in which case the process for identifying the node itself
            // is rather more troublesome.
            let srcNode = $("#id_questiontext");
            let firstRun=1;
            let isSrcNodeDiv = 0;

            // the output node for preview to be written to
            let destNode = $("#freeform-preview");

            // local flags used to determine whether text has changed and re-parsing is required
            let lastValue = "";
            let changed = false;

            setInterval(
                function(){

                    if (typeof USING_TINYMCE !== 'undefined' && firstRun===1){
                        let frameNode = $("#id_questiontext_ifr");

                        // we're in a frame and we haven't yet identified the frame's child node so try now
                        if (frameNode.length===1){
                            srcNode = frameNode.contents().find("#tinymce");
                            isSrcNodeDiv = 1;

                            // if the child node isn't found then drop out and try again at the next update
                            if (srcNode.length!==1){
                                return;
                            }
                        }
                        firstRun = 0;
                    }

                    // check whether the text that we're monitoring has changed
                    let nextVal = (isSrcNodeDiv === 0)? srcNode.val(): srcNode.html();
                    if (lastValue !== nextVal){
                        // the text has changed so make a note of the latest version, set our changed flag to true and drop out
                        // to avoid constantly parsing text while the user is still typing
                        lastValue = nextVal;
                        changed = true;
                        return;
                    }

                    // if the text has changed recently but is now stable then parse it and update data structures as required
                    if (changed === true){

                        // release references to DOM elements before regeneration
                        freeformExecutionLib.reset();

                        // Use the freeformEditorLib to beautify the text
                        let textParts = freeformEditorLib.processQuestionText(lastValue, freeformExecutionLib.getAnswers());
                        let html = textParts.html;
                        let data  = {
                            answers: [],
                            expressions: freeformEditorLib.getExpressions(),
                            question: html
                        };
                        for (let i=0; i < textParts.questions.length; ++i){
                            data.answers[i] = {
                                text: (textParts.questions[i].type === '?[]' ? ':txt:' : '') + textParts.questions[i].text,
                                directives: textParts.questions[i].directives.answerProveccingOptions
                            };
                        }
                        jsonData = JSON.stringify(data);
                        $("input[name='data']").attr("value", jsonData);

                        // store away parent expressions table for rendering sun-expressino answers
                        freeformExecutionLib.setExpressions(freeformEditorLib.getExpressions());

                        // display the beautified text
                        destNode.html(html);

                        // hook up event handlers
                        freeformExecutionLib.hook_events("", function(questionName){
                            // callback on focus change - updates the local expressin signature for input coloration tricks
                            self.signature = '';
                            freeformEditorLib.generateQuestionSignature(questionName, function(questionText, signature){
                                console.log("Generated Signature:", questionName, questionText, signature);
                                self.signature = signature;
                            });
                        });

                        // clear the 'changed' flag as we've now dealt with the changes in question
                        changed = false;
                    }
                },
                250
            );

        }
    };
    return self;
});
