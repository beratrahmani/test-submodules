{block name="frontend_account_abonnement_billing"}
    <div class="div--billing-group">
        <h5>{s name="BillingAddress" namespace="frontend/abo_commerce/orders"}{/s}</h5>
        {block name="frontend_account_abonnement_billing_values"}
            <div class="div--billing-content">
                {if !empty($order.billing.company)}
                    {$order.billing.company}<br/><br/>
                {/if}

                {block name="frontend_checkout_confirm_information_addresses_billing_panel_actions_select_address"}
                    <a class="btn is--primary right" href="{url controller=AboCommerce action="orders"}"
                       data-abo-address-selection="true"
                       data-abo-address-selection-url="{url controller=AboCommerce action="ajaxSelection"}"
                       data-abo-id="{$order.id}"
                       data-abo-selected-address="{$order.billing.id}"
                       data-abo-address-type="billing"
                       title="{s name="SelectOther" namespace="frontend/abo_commerce/index"}{/s}">
                    {s name="SelectOther" namespace="frontend/abo_commerce/index"}{/s}
                    </a>
                {/block}

                {$order.billing.firstName} {$order.billing.lastName}<br/>
                {$order.billing.street}<br/>
                {if !empty($order.billing.additionalAddressLine1)}
                    {$order.billing.additionalAddressLine1}<br/>
                {/if}
                {if !empty($order.billing.additionalAddressLine2)}
                    {$order.billing.additionalAddressLine2}<br/>
                {/if}
                {$order.billing.zip} {$order.billing.city}<br/><br/>
                {$order.billing.country}
            </div>
        {/block}
    </div>
{/block}