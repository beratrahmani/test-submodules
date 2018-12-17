{extends file="parent:widgets/swag_emotion_advanced/index.tpl"}

{block name="emotion_advanced_quick_view_actions_notepad"}
    {block name="emotion_advanced_quick_view_advanced_cart_actions_notepad"}
        {if empty({config name=replaceNote})}
            {$smarty.block.parent}
        {else}
            {include file="widgets/swag_advanced_cart/quick_view_notepad.tpl"}
        {/if}
    {/block}
{/block}

{block name="emotion_advanced_quick_view_content"}
    {block name="emotion_advanced_quick_view_content_advanced_cart_alert"}
        {include file="widgets/swag_advanced_cart/quick_view_alert.tpl"}
    {/block}
    {$smarty.block.parent}
{/block}
