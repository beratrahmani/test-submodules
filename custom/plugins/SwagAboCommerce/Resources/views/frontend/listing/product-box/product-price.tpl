{extends file="parent:frontend/listing/product-box/product-price.tpl"}
{namespace name="frontend/listing/box_article"}

{block name='frontend_listing_box_article_price_default'}
    {if $sArticle.attributes.swag_abo_commerce_prices && $sArticle.attributes.swag_abo_commerce_prices->get('cheapest_abo_price') !== NULL}
        <span class="price--default is--nowrap{if $sArticle.has_pseudoprice} is--discount{/if}">
            {s name='ListingBoxArticleStartsAt'}{/s}
            {$sArticle.attributes.swag_abo_commerce_prices->get('cheapest_abo_price')|currency}
            {s name="Star"}{/s}
        </span>
    {else}
        {$smarty.block.parent}
    {/if}
{/block}
