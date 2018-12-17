{extends file="parent:frontend/checkout/cart.tpl"}

{block name='frontend_checkout_cart_premium'}
    {block name='frontend_checkout_cart_promotion'}
        {if !empty($freeGoods)}
            {include file="frontend/swag_promotion/checkout/free_goods_selection.tpl"}
        {/if}
    {/block}
    {$smarty.block.parent}
{/block}
