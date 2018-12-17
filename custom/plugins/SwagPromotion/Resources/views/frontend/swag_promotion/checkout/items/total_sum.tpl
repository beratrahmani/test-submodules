{* Promotion voucher items can be removed *}
{block name='frontend_checkout_cart_item_promotion_voucher_delete'}
    {if $promotionVoucherIds[$sBasketItem.id]}
        <div class="panel--td column--actions block">
            {block name='frontend_checkout_cart_item_promotion_voucher_delete_link'}
            <a href="{url action='deletePromotionVoucher' controller='SwagPromotion' voucherId=$promotionVoucherIds[$sBasketItem.id] sTargetAction=$sTargetAction}"
               class="btn is--small column--actions-link" title="{"{s name='CartItemLinkDelete' namespace='frontend/checkout/cart_item'}{/s}"|escape}">
                <i class="icon--cross"></i>
            </a>
            {/block}
        </div>
    {/if}
{/block}
