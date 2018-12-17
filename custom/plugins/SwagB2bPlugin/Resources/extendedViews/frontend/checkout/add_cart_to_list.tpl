<div class="block-group group--checkout-actions">
    <div class="block block--ordernumber">
        <h4>
            {s name="AddProductByOrdernumber" namespace="frontend/plugins/b2b_debtor_plugin"}Add product by ordernumber{/s}
        </h4>

        <form method="post" action="{url action='addArticle' sTargetAction=$sTargetAction}" class="table--add-product add-product--form block-group">

            {block name='frontend_checkout_cart_footer_add_product_field'}
                <input name="sAdd" class="add-product--field block" type="text" placeholder="{s name="CheckoutFooterAddProductPlaceholder" namespace='frontend/checkout/cart_footer_left'}{/s}" />
            {/block}

            {block name='frontend_checkout_cart_footer_add_product_button'}
                <button type="submit" title="" class="add-product--button btn is--primary is--center block">
                    <i class="icon--arrow-right"></i>
                </button>
            {/block}
        </form>

    </div>
    {if {b2b_acl_check controller=b2borderlistremote action=remoteListCart}}
        <div class="block block--orderlist">
            <div class="is--b2b-ajax-panel b2b--ajax-panel b2b--ajax-panel-orderlist"
                 data-id="order-list-remote-box"
                 data-plugins="b2bOrderList"
                 data-url="{url controller=b2borderlistremote action=remoteListCart cartId=$sessionId type=detail}"></div>
        </div>
    {/if}
</div>