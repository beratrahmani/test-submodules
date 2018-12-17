{extends file="parent:frontend/checkout/items/rebate.tpl"}

{block name='frontend_checkout_cart_item_rebate_tax_price_wrapper'}
    {if !$sBasketItem.isShippingFreePromotion}
        {$smarty.block.parent}
    {/if}
{/block}

{block name='frontend_checkout_cart_item_rebate_total_sum'}
    {if !$sBasketItem.isShippingFreePromotion}
        {$smarty.block.parent}
    {/if}

    {block name="frontend_checkout_cart_item_rebate_total_sum_promotion"}
        {include file="frontend/swag_promotion/checkout/items/total_sum.tpl"}
    {/block}
{/block}
