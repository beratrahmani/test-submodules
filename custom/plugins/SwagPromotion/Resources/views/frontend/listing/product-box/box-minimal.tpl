{extends file="parent:frontend/listing/product-box/box-minimal.tpl"}

{block name="frontend_listing_box_article_price_discount"}
    {block name="frontend_listing_box_article_promotion_price_discount"}
        {if $sArticle.hasNewPromotionProductPrice && $promotionPriceDisplaying === 'price'}
        {else}
            {$smarty.block.parent}
        {/if}
    {/block}
{/block}

{block name="frontend_listing_box_article_price_discount_before"}
    {block name="frontend_listing_box_article_promotion_price_discount_before"}
        {s name="promotionPriceDisocuntLabel" namespace="frontend/swagPromotion/main"}{/s}
    {/block}
{/block}

{block name="frontend_listing_box_article_price_discount_after"}
    {block name="frontend_listing_box_article_promotion_price_discount_after"}
        {s name="promotionPriceDisocuntInfo" namespace="frontend/swagPromotion/main"}{/s}
    {/block}
{/block}
