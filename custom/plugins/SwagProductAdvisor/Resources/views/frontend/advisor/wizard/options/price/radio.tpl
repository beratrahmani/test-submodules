{foreach $question['steps'] as $step}
    <div class="question-ct--radio-ct question-ct--filter-ct{if $step['css']} {$step['css']|escapeHtmlAttr}{/if}">
        {block name="frontend_advisor_wizard_question_price_radio_input"}
            <input type="radio"
                id="answer{$step['answerId']|escapeHtml}"
                name="q{$question['id']|escapeHtml}_values_max"
                value="{$step['value']|escapeHtml}"
                class="filter--advisor-price-radio advisor--hide-input"
                {if $step['selected']} checked="checked"{/if}/>
        {/block}

        {block name="frontend_advisor_wizard_question_price_radio_label_ct"}
            <label class="question-ct--label"
                for="answer{$step['answerId']|escapeHtml}">
                {block name="frontend_advisor_wizard_cell_price_radio_label_text"}
                    {$value = {"{s namespace="frontend/advisor/main" name="QuestionStepText"}up to{/s} {$step['value']|escapeHtml|currency}"}}
                    {if $step['label']}
                        {$value = $step['label']|escapeHtml}
                    {/if}
                    <span class="label--answer-name">{$value}</span>
                {/block}
            </label>
        {/block}
    </div>
{/foreach}