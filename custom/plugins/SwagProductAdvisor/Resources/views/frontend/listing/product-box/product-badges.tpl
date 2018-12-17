{extends file="parent:frontend/listing/product-box/product-badges.tpl"}

{block name="frontend_listing_box_article_discount"}
	{block name="frontend_listing_box_article_discount_swag_advisor"}
        {include file="frontend/swag_product_advisor/listing/product-box/product-badges.tpl"}
    {/block}

    {if $productBoxLayout != 'advisor-tophit'}
        {$smarty.block.parent}
    {/if}
{/block}

{block name='frontend_listing_box_article_hint'}
    {if $productBoxLayout != 'advisor-tophit'}
        {$smarty.block.parent}
    {/if}
{/block}
