{extends file='parent:frontend/checkout/error_messages.tpl'}

{namespace name='frontend/checkout/bundle'}

{block name='frontend_checkout_error_messages_basket_error'}
    {block name='frontend_checkout_swag_bundle_error_messages_basket_error'}
        {include file='frontend/swag_bundle/error_messages/basket_error.tpl'}
    {/block}
    {$smarty.block.parent}
{/block}

{block name='frontend_checkout_error_messages_voucher_error'}
    {if $sVoucherError && $sVoucherValidation}
        {block name='frontend_checkout_swag_bundle_error_messages_basket_error'}
            {include file='frontend/swag_bundle/checkout/voucher_error.tpl'}
        {/block}
    {elseif !$sVoucherValidation && $sVoucherError}
        {$smarty.block.parent}
    {/if}
{/block}
