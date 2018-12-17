{extends file='parent:frontend/checkout/items/product.tpl'}

{block name='frontend_checkout_cart_item_image_container_outer'}
    {block name='frontend_checkout_liveshopping_cart_item_image_container_outer'}
        {include file='frontend/swag_live_shopping/checkout/liveshopping-checkout-badge.tpl'}
    {/block}

    {$smarty.block.parent}
{/block}

{block name='frontend_checkout_cart_item_quantity_selection'}
    {if $sBasketItem.swagLiveShoppingId > 0}
        <div class="liveshopping--checkout-item-quantity">
            {block name="frontend_checkout_cart_liveshopping_item_quantity_value" }
                {$sBasketItem.quantity}
            {/block}
        </div>
    {else}
        {$smarty.block.parent}
    {/if}
{/block}
