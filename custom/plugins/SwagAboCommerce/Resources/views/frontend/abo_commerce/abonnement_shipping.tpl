{block name="frontend_account_abonnement_shipping"}
    <div class="div--shipping-group">
        <h5>{s name="ShippingAddress" namespace="frontend/abo_commerce/orders"}{/s}</h5>
        {block name="frontend_account_abonnement_shipping_values"}
            <div class="div--shipping-content">
                {if !empty($order.shipping.company)}
                    {$order.shipping.company}<br/><br/>
                {/if}

                {block name="frontend_checkout_confirm_information_addresses_shipping_panel_actions_select_address"}
                    <a class="btn is--primary right" href="{url controller=AboCommerce action="orders"}"
                       data-abo-address-selection="true"
                       data-abo-address-selection-url="{url controller=AboCommerce action="ajaxSelection"}"
                       data-abo-id="{$order.id}"
                       data-abo-selected-address="{$order.shipping.id}"
                       data-abo-address-type="shipping"
                       title="{s name="SelectOther" namespace="frontend/abo_commerce/index"}{/s}">
                       {s name="SelectOther" namespace="frontend/abo_commerce/index"}{/s}
                    </a>
                {/block}

                {$order.shipping.firstName} {$order.shipping.lastName}<br/>
                {$order.shipping.street}<br/>
                {if !empty($order.shipping.additionalAddressLine1)}
                    {$order.shipping.additionalAddressLine1}<br/>
                {/if}
                {if !empty($order.shipping.additionalAddressLine2)}
                    {$order.shipping.additionalAddressLine2}<br/>
                {/if}
                {$order.shipping.zip} {$order.shipping.city}<br/><br/>
                {$order.shipping.country}
            </div>
        {/block}
    </div>
{/block}
