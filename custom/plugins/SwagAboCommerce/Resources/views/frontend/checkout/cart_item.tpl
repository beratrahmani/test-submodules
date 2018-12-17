{extends file="parent:frontend/checkout/cart_item.tpl"}

{block name="frontend_checkout_cart_item_additional_type"}
    {$smarty.block.parent}
    {if $sBasketItem.modus == 10 && $sBasketItem.abo_attributes.isAboDiscount}
        {block name="frontend_checkout_abo_commerce_cart_item_additional_type"}
            {include file="frontend/checkout/items/abo_rebate.tpl"}
        {/block}
    {/if}
{/block}
