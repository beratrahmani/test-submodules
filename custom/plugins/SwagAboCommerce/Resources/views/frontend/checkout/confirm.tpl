{extends file="parent:frontend/checkout/confirm.tpl"}

{block name="frontend_checkout_confirm_item"}
    {if $sBasketItem.modus == 10 && $sBasketItem.abo_attributes.isAboDiscount}
        {include file="frontend/checkout/items/abo_rebate.tpl" isLast=$sBasketItem@last}
    {else}
        {$smarty.block.parent}
    {/if}
{/block}

{block name='frontend_checkout_confirm_add_product_field'}
    {$smarty.block.parent}
    <input type="hidden" name="swAboCommerceAddArticleFromCart" value="1">
{/block}