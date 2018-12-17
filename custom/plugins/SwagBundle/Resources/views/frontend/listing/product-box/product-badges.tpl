{extends file='parent:frontend/listing/product-box/product-badges.tpl'}

{block name='frontend_listing_box_article_new'}
    {$smarty.block.parent}

    {block name='frontend_listing_swag_bundle_box_article_new'}
        {include file='frontend/swag_bundle/listing/bundle_badge.tpl'}
    {/block}
{/block}
