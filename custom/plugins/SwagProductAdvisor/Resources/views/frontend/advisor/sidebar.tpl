{block name="frontend_index_left_categories"}
    {* Container for the sidebar *}
    {block name="frontend_advisor_content_sidebar"}
        <div class="advisor--content-sidebar advisor--state-{$advisorState} content-sidebar--position-{$position}" data-advisor-sidebar="true" data-minQuestions="{$advisor['minMatchingAttributes']}">
            <div class="advisor--sidebar panel has--border">
                {block name="frontend_advisor_content_sidebar_form"}
                    <form class="sidebar--questions-form" method="post" action="{url action=save controller=advisor advisorId={$advisor['id']} hash={$advisorHash}}">
                        <input type="hidden" name="advisorId" value="{$advisor['id']}" />
                        {block name="frontend_advisor_content_sidebar_header"}
                            <div class="advisor--sidebar-header">
                                <div class="sidebar-header--advisor-title">
                                    {$advisor['name']}
                                </div>
                            </div>
                        {/block}

                        {* Contains all questions *}
                        {block name="frontend_advisor_content_sidebar_questions"}
                            <div class="advisor--questions">
                                {foreach $advisor['questions'] as $question}
                                    {* Contains a single question *}
                                    {block name="frontend_advisor_content_sidebar_question_ct"}
                                        <div class="advisor--question-ct{if $question['template'] == 'range_slider'} advisor--price-ct{/if}">
                                            {include file="frontend/advisor/sidebar/question.tpl" isFirst={$question@first}}
                                        </div>
                                    {/block}
                                {/foreach}
                            </div>
                        {/block}

                        {block name="frontend_advisor_content_sidebar_buttons"}
                            <div class="advisor--sidebar-buttons">

                                {$minimumAnswers = $advisor['minMatchingAttributes']}
                                {if $minimumAnswers >= 0}
                                    <div class="sidebar-buttons--warning"{if $minimumAnswers == 0 || $advisor['answeredQuestions']|count >= $minimumAnswers} style="display: none;"{/if}>
                                        {$messagePart1 = {"{s namespace='frontend/advisor/main' name='MinimumAnswersWarning1'}Please give at least{/s}"}}
                                        {$messagePart2 = {"{s namespace='frontend/advisor/main' name='MinimumAnswersWarning2'}more answer(s){/s}"}}

                                        {$message = "`$messagePart1` <span class='sidebar-buttons--minimum-answers'>`$minimumAnswers`</span> `$messagePart2`"}
                                        {include file="frontend/_includes/messages.tpl" type="warning" content=$message}
                                    </div>
                                {/if}
                                {block name="frontend_advisor_content_sidebar_button_submit"}
                                    <button type="submit" class="advisor--submit-btn block btn is--primary is--center is--icon-right" disabled="disabled">
                                        {$advisor['buttonText']|truncate:20}
                                        <i class="icon--arrow-right"></i>
                                    </button>
                                {/block}

                                {block name="frontend_advisor_content_sidebar_button_reset"}
                                    <a class="advisor--reset-advisor-btn block btn is--center is--icon-left is--disabled" title="{s name="ResetAdvisorBtnText" namespace="frontend/advisor/main"}Reset advisor{/s}" href="{$advisorResetUrl}">
                                        {s name="ResetAdvisorBtnText" namespace="frontend/advisor/main"}Reset advisor{/s}
                                        <i class="icon--arrow-left"></i>
                                    </a>
                                {/block}
                            </div>
                        {/block}

                        {* Displays the "required fields" info *}
                        {block name="frontend_advisor_content_sidebar_required_info"}
                            {if $advisor['hasRequired'] == true}
                                <div class="advisor--required-info">
                                    ** {s name="RequiredInfo" namespace="frontend/advisor/main"}Required fields{/s}
                                </div>
                            {/if}
                        {/block}
                    </form>
                {/block}
            </div>
        </div>
    {/block}
{/block}