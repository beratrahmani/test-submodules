{extends file="parent:frontend/listing/product-box/product-badges.tpl"}

{block name="frontend_listing_box_article_esd"}
    {$smarty.block.parent}
    {block name="frontend_listing_box_article_esd_promotion"}
        {include file="frontend/swag_promotion/listing/product-box/promotion-badge.tpl"}
    {/block}
{/block}
