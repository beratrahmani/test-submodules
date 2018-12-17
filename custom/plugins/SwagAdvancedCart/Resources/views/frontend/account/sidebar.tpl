{extends file="parent:frontend/account/sidebar.tpl"}
{namespace name="frontend/plugins/swag_advanced_cart/content_right"}

{block name="frontend_account_menu_link_notes"}
    {block name="frontend_advanced_cart_account_menu_link_notes"}
        {if empty({config name=replaceNote})}
            {$smarty.block.parent}
        {/if}
        {include file="frontend/swag_advanced_cart/account/sidebar.tpl"}
    {/block}
{/block}
