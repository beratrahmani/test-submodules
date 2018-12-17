{extends file="parent:frontend/detail/actions.tpl"}
{namespace name="frontend/plugins/swag_advanced_cart/article_detail"}

{*Hide Note if replacing is enabled*}
{block name="frontend_detail_actions_notepad"}
    {block name="frontend_detail_advanced_cart_actions_notepad"}
        {if empty({config name=replaceNote})}
            {$smarty.block.parent}
        {/if}
        {include file="frontend/swag_advanced_cart/detail/notepad.tpl"}
    {/block}
{/block}
