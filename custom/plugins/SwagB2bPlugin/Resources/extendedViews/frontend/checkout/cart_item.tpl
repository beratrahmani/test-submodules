{extends file="parent:frontend/checkout/cart_item.tpl"}

{block name="frontend_checkout_cart_item_details_inline"}
    {$smarty.block.parent}
    {if $b2bSuite}
        {include file="frontend/checkout/order_list.tpl"}
    {/if}
{/block}