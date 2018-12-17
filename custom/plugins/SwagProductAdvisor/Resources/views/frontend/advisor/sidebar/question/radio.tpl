{* Contains the default-radio button *}
{block name="frontend_advisor_sidebar_answer_default_radio"}
    {foreach $question['answers'] as $answer}
        {$label = $answer['value']}
        {if $answer['label']}
            {$label = $answer['label']}
        {/if}

        {block name="frontend_advisor_sidebar_default_radio_ct"}
            <div class="question-ct--radio-ct question-ct--filter-ct{if $answer['css']} {$answer['css']|escapeHtmlAttr}{/if}">
                <span class="filter-panel--radio question-ct--filter">
                    {block name="frontend_advisor_sidebar_default_radio_input"}
                        <input type="radio"
                            id="answer{$answer['answerId']|escapeHtml}{$position}"
                            name="q{$question['id']|escapeHtml}_values"
                            value="{$answer['answerId']|escapeHtml}"
                            class="filter--advisor-radio"
                            {if $question['required']} required="required"{/if}
                            {if $answer['selected']} checked="checked"{/if}/>
                    {/block}

                    <span class="radio--state">&nbsp;</span>
                </span>

                {block name="frontend_advisor_sidebar_default_radio_label"}
                    <label class="question-ct--label"
                           for="answer{$answer['answerId']|escapeHtml}{$position}">
                        {$label|escapeHtml}
                    </label>
                {/block}
            </div>
        {/block}
    {/foreach}
{/block}
