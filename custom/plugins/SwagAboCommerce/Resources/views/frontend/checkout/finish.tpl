{extends file="parent:frontend/checkout/finish.tpl"}

{block name="frontend_checkout_finish_item"}
    {if $sBasketItem.modus == 10 && $sBasketItem.abo_attributes.isAboDiscount}
        {block name="frontend_checkout_abo_commerce_finish_item"}
            {include file="frontend/checkout/items/abo_rebate.tpl" isLast=$sBasketItem@last}
        {/block}
    {else}
        {$smarty.block.parent}
    {/if}
{/block}
