{extends file="frontend/advisor/wizard/grid/image.tpl"}

{block name="frontend_advisor_content_wizard_grid_image_input"}
    {block name="frontend_advisor_content_wizard_grid_price_image_input"}
        <input type="{$inputType}"
               id="answer{$answer['answerId']|escapeHtml}"
               name="q{$question['id']|escapeHtml}_values_max"
               value="{$answer['value']}"
               class="filter--advisor-checkbox advisor--hide-input"
                {if $answer['selected']} checked="checked"{/if}/>
    {/block}
{/block}

{block name="frontend_advisor_content_wizard_grid_label_config"}
    {block name="frontend_advisor_content_wizard_grid_price_label_config"}
        {$label = {"{s namespace='frontend/advisor/main' name='QuestionStepText'}up to{/s} {$answer['value']|currency}"}}
    {/block}
    {$smarty.block.parent}
{/block}
