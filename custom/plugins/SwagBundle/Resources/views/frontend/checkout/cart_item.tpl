{extends file='parent:frontend/checkout/cart_item.tpl'}

{block name='frontend_checkout_cart_item_additional_type'}
    {$smarty.block.parent}
    {block name='frontend_checkout_swag_bundle_cart_item_additional_type'}
        {include file='frontend/swag_bundle/cart_item/additional_type.tpl'}
    {/block}
{/block}
