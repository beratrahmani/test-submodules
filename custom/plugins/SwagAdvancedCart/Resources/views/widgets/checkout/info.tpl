{extends file="parent:widgets/checkout/info.tpl"}

{block name="frontend_index_checkout_actions_notepad"}
    {block name="frontend_index_advanced_cart_checkout_actions_notepad"}
        {if empty({config name=replaceNote})}
            {$smarty.block.parent}
        {else}
            {include file="widgets/swag_advanced_cart/notepad.tpl"}
        {/if}
    {/block}
{/block}
