{block name="frontend_account_abonnement_payment"}
    <div class="div--payment-group">
        <h5>{s name="PaymentMethod" namespace="frontend/abo_commerce/orders"}{/s}</h5>
        {block name="frontend_account_abonnement_payment_values"}
            <div class="div--payment-content">

                {block name="frontend_checkout_confirm_information_addresses_payment_panel_actions_select_payment_method"}
                    <a class="btn is--primary right" href="{url controller=AboCommerce action="orders"}"
                       data-abo-payment-selection="true"
                       data-abo-payment-selection-url="{url controller=AboCommerce action="ajaxSelectionPayment"}"
                       data-abo-id="{$order.id}"
                       data-abo-payment-id="{$order.paymentId}"
                       title="{s name="SelectOther" namespace="frontend/abo_commerce/index"}{/s}">
                        {s name="SelectOther" namespace="frontend/abo_commerce/index"}{/s}
                    </a>
                {/block}
                {$paymentMeans[$order.paymentId]}
            </div>
        {/block}
    </div>
{/block}