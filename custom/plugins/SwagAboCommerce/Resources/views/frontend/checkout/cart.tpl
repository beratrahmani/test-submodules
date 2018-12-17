{extends file="parent:frontend/checkout/cart.tpl"}

{block name='frontend_checkout_cart_item'}
    {if $sBasketItem.modus == 10 && $sBasketItem.abo_attributes.isAboDiscount}
        {block name='frontend_checkout_abo_commerce_cart_item'}
            {include file="frontend/checkout/items/abo_rebate.tpl"}
        {/block}
    {else}
        {$smarty.block.parent}
    {/if}
{/block}
