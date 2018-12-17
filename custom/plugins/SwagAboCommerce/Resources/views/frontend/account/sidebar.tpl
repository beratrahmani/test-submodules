{extends file="parent:frontend/account/sidebar.tpl"}

{block name="frontend_account_menu_link_downloads"}
    {$smarty.block.parent}
    {block name="frontend_account_menu_abo_commerce_sidebar"}
        {include file="frontend/account/sidebar/downloads.tpl"}
    {/block}
{/block}
