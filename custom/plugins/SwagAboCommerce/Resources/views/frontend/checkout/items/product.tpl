{extends file="parent:frontend/checkout/items/product.tpl"}
{namespace name="frontend/checkout/abo_commerce_cart_item"}

{block name='frontend_checkout_cart_item_quantity'}
    {if $sBasketItem.abo_attributes.isAboArticle && $sBasketItem.aboCommerce.isLimited}
        {block name='frontend_checkout_abo_commerce_cart_item_quantity'}
            {include file="frontend/checkout/items/product/quantity.tpl"}
        {/block}
    {else}
        {$smarty.block.parent}
    {/if}
{/block}

{block name='frontend_checkout_cart_item_image_container_outer'}
    {block name='frontend_checkout_abo_commerce_cart_item_image_container_outer'}
        {include file="frontend/checkout/items/product/container.tpl"}
    {/block}
    {$smarty.block.parent}
{/block}

{block name="frontend_checkout_cart_item_details"}
    {$smarty.block.parent}
    {block name="frontend_checkout_abo_commerce_cart_item_details"}
        {include file="frontend/checkout/items/product/details.tpl"}
    {/block}
{/block}