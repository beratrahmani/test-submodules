{extends file='parent:frontend/checkout/error_messages.tpl'}

{namespace name="frontend/checkout/live_shopping"}

{block name='frontend_checkout_error_messages_basket_error'}
    {block name='frontend_checkout_liveshopping_error_messages_basket_error'}
        {include file='frontend/swag_live_shopping/error_messages/liveshopping-basket-error-messages.tpl'}
    {/block}

    {$smarty.block.parent}
{/block}
