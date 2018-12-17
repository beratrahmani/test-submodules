{extends file="parent:frontend/listing/box_article.tpl"}

{block name="frontend_listing_box_article_includes_additional"}
    {block name='frontend_listing_liveshopping_box_article_includes_additional'}
        {include file="frontend/listing/product-box/box-basic.tpl" productBoxLayout="basic" isTopseller=$isTopseller}
    {/block}
{/block}
