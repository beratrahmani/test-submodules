{extends file="parent:frontend/checkout/ajax_cart.tpl"}

{block name='frontend_checkout_ajax_cart_articlename_name'}
    {if $sBasketItem.modus == 10 && $sBasketItem.abo_attributes.isAboDiscount}
        {block name='frontend_checkout_ajax_cart_abo_commerce_articlename_name'}
            {include file="frontend/checkout/ajax_cart/articlename.tpl"}
        {/block}
    {else}
        {$smarty.block.parent}
    {/if}
{/block}
