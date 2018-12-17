{extends file="parent:frontend/detail/data.tpl"}

{block name="frontend_detail_data_pseudo_price_discount_content"}
    {block name="frontend_detail_data_promotion_pseudo_price_discount_content"}
        {if $sArticle.hasNewPromotionProductPrice && $promotionPriceDisplaying === 'price'}
        {else}
            {$smarty.block.parent}
        {/if}
    {/block}
{/block}

{block name="frontend_detail_data_pseudo_price_discount_before"}
    {block name="frontend_detail_data_promotion_pseudo_price_discount_before"}
        {s name="promotionPriceDisocuntLabel" namespace="frontend/swagPromotion/main"}{/s}
    {/block}
{/block}

{block name="frontend_detail_data_pseudo_price_discount_after"}
    {block name="frontend_detail_data_promotion_pseudo_price_discount_after"}
        {s name="promotionPriceDisocuntInfo" namespace="frontend/swagPromotion/main"}{/s}
    {/block}
{/block}
