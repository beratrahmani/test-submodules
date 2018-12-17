{namespace name="frontend/swag_promotion/main"}

{if $voucherExpired}
    {$content = "{s namespace='frontend/swag_promotion/main' name='voucherExpired'}{/s}"}
    {block name="frontend_checkout_error_messages_voucher_error_promotion_expired"}
        {include file="frontend/_includes/messages.tpl" type="error"}
    {/block}
{/if}
{if $voucherNotCombined}
    {$content = "{s namespace='frontend/swag_promotion/main' name='voucherNotCombined'}{/s}"}
    {block name="frontend_checkout_error_messages_voucher_error_voucher_not_combined"}
        {include file="frontend/_includes/messages.tpl" type="error"}
    {/block}
{/if}
{if $voucherPromotionId}
    {if in_array($voucherPromotionId, $availablePromotions)}
        {$content = "{s namespace='frontend/swag_promotion/main' name='promotionActivated'}{/s}"}
        {block name="frontend_checkout_error_messages_voucher_error_promotion_added"}
            {include file="frontend/_includes/messages.tpl" type="info"}
        {/block}
    {else}
        {$content = "{s namespace='frontend/swag_promotion/main' name='voucherAddedButNotActive'}{/s}"}
        {block name="frontend_checkout_error_messages_voucher_error_promotion_addedNotActive"}
            {include file="frontend/_includes/messages.tpl" type="warning"}
        {/block}
    {/if}
{/if}
