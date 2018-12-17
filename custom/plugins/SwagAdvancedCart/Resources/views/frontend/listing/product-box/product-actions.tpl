{extends file="parent:frontend/listing/product-box/product-actions.tpl"}
{namespace name="frontend/plugins/swag_advanced_cart/article_detail"}

{block name="frontend_listing_box_article_actions_save"}
    {block name="frontend_listing_advanced_cart_box_article_actions_save"}
        {if empty({config name=replaceNote})}
            {$smarty.block.parent}
        {else}
            {include file="frontend/swag_advanced_cart/listing/product-actions.tpl"}
        {/if}
    {/block}
{/block}
