{if $productBoxLayout == 'show_matches'}
    {block name="frontend_listing_box_article_includes_additional_hits"}
        {include file="frontend/swag_product_advisor/listing/product-box/box-hits.tpl"}
    {/block}
{elseif $productBoxLayout == 'show_matches_and_misses'}
    {block name="frontend_listing_box_article_includes_additional_misses"}
        {include file="frontend/swag_product_advisor/listing/product-box/box-misses.tpl"}
    {/block}
{/if}
