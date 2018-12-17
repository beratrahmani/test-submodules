{block name="frontend_advisor_sidebar_question_name"}
    <div class="question-ct--question-name">
        {block name="frontend_advisor_sidebar_question_name_text"}
            <div class="question-name--name">
                {$question['question']}
                {if $question['required']}
                    **
                {/if}
            </div>
        {/block}

        {$openQuestion = $question['expandQuestion']}

        {* Criteria is only given on the result, so we are on the result-page *}
        {if $criteria}
            {$openQuestion = $question['answered']}
        {/if}

        {block name="frontend_advisor_sidebar_question_name_arrow"}
            <div class="question-name--arrow">
                <i class="advisor--icon-arrow icon--arrow-{if $openQuestion}up{else}down{/if}"></i>
            </div>
        {/block}
    </div>
{/block}

{* Contains all answers for a question *}
{block name="frontend_advisor_sidebar_question_answers"}
    <div class="question-ct--answer-ct{if !$openQuestion} question-ct--hidden{/if}">
        {block name="frontend_advisor_sidebar_question_answers_ct"}
            <div class="question-ct--answers">
                {$templateBase = "frontend/advisor/sidebar/question/"}

                {if $question['type'] == 'price'}
                    {$templateBase = {$templateBase|cat:'price/'}}
                {/if}

                {$template = $templateBase|cat:$question['template']|cat:".tpl"|strtolower}
                {block name="frontend_advisor_sidebar_answer_ct"}
                    {if $template|template_exists}
                        {include file={$template}}
                    {/if}
                {/block}
            </div>
        {/block}

        {if $question['infoText']}
            {block name="frontend_advisor_sidebar_question_info"}
                {include file="frontend/advisor/sidebar/question/info.tpl"}
            {/block}
        {/if}

        {block name="frontend_advisor_sidebar_question_reset"}
            {if $question['template'] != "range_slider"}
                {include file="frontend/advisor/sidebar/question/reset.tpl"}
            {/if}
        {/block}
    </div>
{/block}