{namespace name="frontend/advisor/main"}

<div class="advisor--wizard" data-advisor-wizard="true">
    {block name="frontend_advisor_content_wizard_title"}
        <div class="advisor--wizard-title">
            {$advisor['name']}
        </div>
    {/block}

    {* Builds a warning message which shows all required fields missing *}
    {block name="frontend_advisor_content_wizard_warning_required"}
        {if $missingQuestions}
            {block name="frontend_advisor_content_wizard_warning_message"}
                {$message = "{s name='RequiredFieldsMissingMessage'}The following questions have yet to be answered:{/s} <br /><ul class='advisor-wizard--warning-ul'>"}
                {foreach $missingQuestions as $missingQuestion}
                    {$message = "`$message` <li class='advisor-wizard--warning-li'><a href='`$missingQuestion['questionUrl']`'>`$missingQuestion['question']`</a>"}
                {/foreach}
                {$message = "`$message` </ul>"}
            {/block}

            {block name="frontend_advisor_content_wizard_warning"}
                <div class="wizard--warning-ct">
                    {include file="frontend/_includes/messages.tpl" type="warning" content=$message}
                </div>
            {/block}
        {/if}
    {/block}

    {* Contains the progress-bar and the currently active step *}
    {block name="frontend_advisor_content_wizard_progress"}
        <div class="advisor--wizard-progress">
            {block name="frontend_advisor_content_wizard_progress_width"}
                {$width = ($advisor['currentQuestionIndex'] / $advisor['questionCount']) * 100}
            {/block}

            {block name="frontend_advisor_content_wizard_progress_step"}
                <div class="wizard-progress--current-step">
                    {$advisor['currentQuestionIndex']} {s name="WizardQuestionCountDelimiter"}/{/s} {$advisor['questionCount']}
                </div>
            {/block}

            {block name="frontend_advisor_content_wizard_progress_bar"}
                <div class="wizard-progress--progress-bar">
                    <div class="progress-bar--status" style="width: {$width}%"></div>
                </div>
            {/block}
        </div>
    {/block}

    {block name="frontend_advisor_content_wizard_question_form"}
        <form method="post" data-save-url="{url controller=advisor action=quickSave hash=$advisorHash}" action="{$advisorNextQuestion}">
            {* Contains the main question-data (Question itself, question text and the grid, if necessary) *}
            {block name="frontend_advisor_content_wizard_question"}
                {$question = $advisor['currentQuestion']}
                <div class="advisor--wizard-question"{if $question['required']} data-advisor-required="required"{/if}>
                    <div class="wizard-question--info-ct">
                        {block name="frontend_advisor_content_wizard_question_title"}
                            <div class="wizard-question--title">
                                {$question['question']} {if $question['required']}**{/if}
                            </div>
                        {/block}

                        {block name="frontend_advisor_content_wizard_question_info"}
                            {if $question['infoText']}
                                <div class="wizard-question--info">
                                    {$question['infoText']}
                                </div>
                            {/if}
                        {/block}
                    </div>

                    {block name="frontend_advisor_content_wizard_question_content"}
                        <div class="wizard-question--content">
                            {block name="frontend_advisor_content_wizard_question_key"}
                                {* Do not change the name of this input! *}
                                <input type="hidden"
                                       name="questionKey"
                                       value="{$question['id']|escapeHtml}"/>
                            {/block}

                            {if $question['template'] === 'radio_image' || $question['template'] === 'checkbox_image'}
                                {include file="frontend/advisor/wizard/grid.tpl"}
                            {else}
                                {include file="frontend/advisor/wizard/options.tpl"}
                            {/if}
                        </div>
                    {/block}
                </div>
            {/block}

            {* Contains the wizard-actions, e.g. the next- and back-button *}
            {block name="frontend_advisor_content_wizard_actions"}
                <div class="advisor--wizard-actions">
                    {* The next-question button *}
                    {block name="frontend_advisor_content_wizard_actions_next"}
                        {$disableButton = false}
                        {if $question['template'] !== 'range_slider' && $question['required'] && !$question['answered']}
                            {$disableButton = true}
                        {/if}
                            <div class="wizard-actions--next-button right">
                                {if $advisor['questionCount'] === $advisor['currentQuestionIndex']}
                                    {block name="frontend_advisor_content_wizard_start_button"}
                                        <button type="submit" class="btn is--primary is--center is--icon-right next-button--btn">
                                            {$advisor['buttonText']}
                                            <i class="icon--arrow-right"></i>
                                        </button>
                                    {/block}
                                {else}
                                    {block name="frontend_advisor_content_wizard_next_button"}
                                        <button type="submit" class="btn is--primary is--center is--icon-right next-button--btn">
                                            {s name="WizardNextButtonText"}Next question{/s}
                                            <i class="icon--arrow-right"></i>
                                        </button>
                                    {/block}
                                {/if}
                            </div>
                    {/block}

                    {block name="frontend_advisor_content_wizard_actions_back"}
                        {if $advisor['currentQuestionIndex'] != 1}
                            <div class="wizard-actions--back-button left">
                                {block name="frontend_advisor_content_wizard_back_button"}
                                    <a class="advisor--wizard-back btn is--center is--icon-left" href="{$advisorPreviousQuestion}" title="{s name="WizardBackButtonText"}Back{/s}">
                                        <i class="icon--arrow-left"></i>
                                        {s name="WizardBackButtonText"}Back{/s}
                                    </a>
                                {/block}
                            </div>
                        {/if}
                    {/block}

                    {block name="frontend_advisor_content_wizard_skip_wrapper"}
                        <div class="wizard-actions--skip-wrapper">
                            {block name="frontend_advisor_content_wizard_actions_skip"}

                                <select class="wizard-actions--question-select" data-class="wizard-actions--question-js-select">
                                    {block name="frontend_advisor_content_wizard_actions_skip_empty"}
                                        <option class="advisor--empty-text" selected="selected" value="">
                                            {s name="JumpToQuestionText"}Skip to question{/s}
                                        </option>
                                    {/block}

                                    {* Do not change the name of $curQuestion to $question ! *}
                                    {foreach $advisor['questions'] as $curQuestion}
                                        {block name="frontend_advisor_content_wizard_actions_skip_condition"}
                                            {block name="frontend_advisor_content_wizard_actions_skip_option"}
                                                <option data-question-url="{$curQuestion['questionUrl']}">
                                                    {$curQuestion['question']}
                                                    {if $curQuestion['required']}**{/if}
                                                </option>
                                            {/block}
                                        {/block}
                                    {/foreach}
                                </select>
                            {/block}

                            {* The reset-advisor button *}
                            {block name="frontend_advisor_content_wizard_actions_reset"}
                                <div class="wizard-actions--reset-button reset-button--wizard-question">
                                    <a class="advisor--reset-advisor-btn btn is--center is--icon-left" title="{s name="ResetAdvisorBtnText"}Reset advisor{/s}" href="{$advisorResetUrl}">
                                        {s name="ResetAdvisorBtnText" namespace="frontend/advisor/main"}Reset advisor{/s}
                                        <i class="icon--arrow-left"></i>
                                    </a>
                                </div>
                            {/block}
                        </div>
                    {/block}
                </div>

                {if $advisor['hasRequired'] == true}
                    <div class="wizard--required">** {s name="RequiredInfo"}Required questions{/s}</div>
                {/if}
            {/block}
        </form>
    {/block}
</div>