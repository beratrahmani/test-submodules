{extends file="parent:frontend/checkout/items/rebate.tpl"}

{block name="frontend_checkout_cart_item_rebate_details_title"}
    {block name="frontend_checkout_abo_commerce_cart_item_rebate_details_title"}
        {include file="frontend/checkout/items/abo_rebate/title.tpl"}
    {/block}
{/block}

{block name="frontend_checkout_cart_item_rebate_details_inline"}
    {block name="frontend_checkout_abo_commerce_cart_item_rebate_details_inline"}
        {include file="frontend/checkout/items/abo_rebate/details.tpl"}
    {/block}
{/block}
