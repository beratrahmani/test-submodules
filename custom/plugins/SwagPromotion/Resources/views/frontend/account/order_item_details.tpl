{extends file="parent:frontend/account/order_item_details.tpl"}

{block name="frontend_account_order_item_pseudo_price_before"}
    {block name="frontend_account_order_item_promotion_pseudo_price_before"}
        {s name="promotionPriceDisocuntLabel" namespace="frontend/swagPromotion/main"}{/s}
    {/block}
{/block}

{block name="frontend_account_order_item_pseudo_price_after"}
    {block name="frontend_account_order_item_promotion_pseudo_price_after"}
        {s name="promotionPriceDisocuntInfo" namespace="frontend/swagPromotion/main"}{/s}
    {/block}
{/block}
