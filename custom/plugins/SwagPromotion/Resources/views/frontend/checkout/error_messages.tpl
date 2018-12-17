{extends file="parent:frontend/checkout/error_messages.tpl"}

{block name="frontend_checkout_error_messages_voucher_error"}
    {$smarty.block.parent}
    {block name="frontend_checkout_error_messages_voucher_error_promotion"}
        {include file="frontend/swag_promotion/checkout/error_messages.tpl"}
    {/block}
{/block}
