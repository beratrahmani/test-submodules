{extends file='parent:frontend/checkout/confirm_item.tpl'}

{block name='frontend_checkout_cart_item_additional_type'}
    {$smarty.block.parent}
    {block name='frontend_checkout_swag_bundle_cart_item_additional_type'}
        {include file='frontend/swag_bundle/confirm_item/additional_type.tpl'}
    {/block}
{/block}
