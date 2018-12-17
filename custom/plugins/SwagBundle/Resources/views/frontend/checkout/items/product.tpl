{extends file='parent:frontend/checkout/items/product.tpl'}

{block name='frontend_checkout_cart_item_image'}
    {include file='frontend/swag_bundle/product/image.tpl'}
    {$smarty.block.parent}
{/block}

{* product amount *}
{block name='frontend_checkout_cart_item_quantity_selection'}
    {if $sBasketItem.modus == 0}
        {if $sBasketItem.bundleId > 0}
            {include file='frontend/swag_bundle/product/quantity_selection.tpl'}
        {else}
            {$smarty.block.parent}
        {/if}
    {else}
        &nbsp;
    {/if}
{/block}
