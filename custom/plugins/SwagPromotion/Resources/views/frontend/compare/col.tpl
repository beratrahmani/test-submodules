{extends file="parent:frontend/compare/col.tpl"}

{block name="frontend_compare_price_pseudoprice"}
    {block name="frontend_compare_price_promotion_pseudoprice"}
        {if $sArticle.hasNewPromotionProductPrice && $promotionPriceDisplaying === 'price'}
        {else}
            {$smarty.block.parent}
        {/if}
    {/block}
{/block}

{block name="frontend_compare_price_pseudoprice_before"}
    {block name="frontend_compare_price_promotion_pseudoprice_before"}
        {s name="promotionPriceDisocuntLabel" namespace="frontend/swagPromotion/main"}{/s}
    {/block}
{/block}

{block name="frontend_compare_price_pseudoprice_after"}
    {block name="frontend_compare_price_promotion_pseudoprice_after"}
        {s name="promotionPriceDisocuntInfo" namespace="frontend/swagPromotion/main"}{/s}
    {/block}
{/block}
