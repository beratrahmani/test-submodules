{block name="frontend_advisor_content_wizard_question_options"}
    {$template = $question['template']}
    <div class="wizard-content--ct content--wizard-{$template}">
        {if $template == 'radio' && $question['type'] != 'price'}
            {block name="frontend_advisor_content_wizard_question_option_radio"}
                {include file="frontend/advisor/wizard/options/radio.tpl"}
            {/block}
        {elseif $template == 'radio' && $question['type'] == 'price'}
            {block name="frontend_advisor_content_wizard_question_option_price_radio"}
                {include file="frontend/advisor/wizard/options/price/radio.tpl"}
            {/block}
        {elseif $template == 'checkbox'}
            {block name="frontend_advisor_content_wizard_question_option_checkbox"}
                {include file="frontend/advisor/wizard/options/checkbox.tpl"}
            {/block}
        {elseif $template == 'range_slider'}
            {block name="frontend_advisor_content_wizard_question_option_range"}
                {include file="frontend/advisor/range_slider.tpl"}
            {/block}
        {/if}
    </div>
{/block}