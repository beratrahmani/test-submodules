{extends file="parent:frontend/checkout/cart.tpl"}
{namespace name="frontend/plugins/swag_advanced_cart/checkout"}

{* Allow wishlist loading if cart is NOT empty *}
{block name='frontend_checkout_cart_table_actions'}
    {block name='frontend_checkout_advanced_cart_cart_table_actions'}
        {include file="frontend/swag_advanced_cart/checkout/manage.tpl"}
    {/block}
    {$smarty.block.parent}
{/block}

{* Allow wishlist loading if cart is empty *}
{block name="frontend_basket_basket_is_empty"}
    {block name="frontend_checkout_actions_wishlist_list"}
        {if count($wishlists)}
            {include file="frontend/swag_advanced_cart/checkout/manage.tpl" isEmpty=true}
        {/if}
    {/block}
    {$smarty.block.parent}
{/block}
