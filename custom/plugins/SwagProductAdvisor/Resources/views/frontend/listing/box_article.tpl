{extends file="parent:frontend/listing/box_article.tpl"}

{block name="frontend_listing_box_article_includes_additional"}
    {block name="frontend_listing_box_article_includes_additional_advisor"}
    	{include file="frontend/swag_product_advisor/listing/box_article.tpl"}
    {/block}
    {if $productBoxLayout != 'show_matches' && $productBoxLayout != 'show_matches_and_misses'}
        {$smarty.block.parent}
    {/if}
{/block}