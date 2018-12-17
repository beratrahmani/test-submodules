{extends file='parent:frontend/checkout/confirm.tpl'}

{block name='frontend_checkout_confirm_error_messages'}
    {$smarty.block.parent}
    {block name="frontend_checkout_confirm_error_messages_used_promotions"}
        {if $promotionsUsedTooOften}
            {include file="frontend/swag_promotion/checkout/used_too_often_offcanvas.tpl"}
        {/if}
    {/block}
{/block}
