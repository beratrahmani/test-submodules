{namespace name=frontend/plugins/b2b_debtor_plugin}

{extends file="parent:frontend/_base/modal-content.tpl"}

{block name="b2b_modal_base_settings"}
    {$modalSettings.navigation = true}

    {$modalSettings.actions = false}
    {$modalSettings.content.padding = false}
    {$modalSettings.bottom = true}
{/block}

{* Title Placeholder *}
{block name="b2b_modal_base_content_inner_topbar_headline"}
    {s name="MasterData"}Master data{/s}
{/block}

{* Modal Content: Grid Component: Content *}
{block name="b2b_modal_base_content_inner_scrollable_inner_content_inner"}
    <div class="content--inner b2b-order-overview">

        {include file="frontend/b2borderlineitemreference/_head.tpl"}

        <div class="block-group">
            <div class="b2b-shipping-address">
                <div class="panel has--border">
                    <div class="panel--title">
                        {s name="ShippingAddress"}Shipping address{/s}
                    </div>
                    <div class="panel--body">
                        {include file="frontend/b2borderlineitemreference/_address-detail.tpl" address=$shippingAddress}
                    </div>
                </div>
            </div>
            <div class="b2b-billing-address">
                <div class="panel has--border">
                    <div class="panel--title">
                        {s name="BillingAddress"}Billing address{/s}
                    </div>
                    <div class="panel--body">
                        {include file="frontend/b2borderlineitemreference/_address-detail.tpl" address=$billingAddress}
                    </div>
                </div>
            </div>
        </div>
        <div class="block-group">
            <div class="b2b-shipping">
                <div class="panel has--border">
                    <div class="panel--body">
                        <table class="is--full">
                            <tr>
                                <td class="is--bold">{s name="Shipping"}Shipping{/s}:</td>
                                <td>
                                    {$shippingName|default:"{s name="NoShippingMethod"}No shipping method{/s}"}
                                </td>
                            </tr>
                            <tr>
                                <td class="is--bold">{s name="ShippingAmount"}Shipping Amount{/s}:</td>
                                <td>
                                    {$orderContext->shippingAmountNet|currency}
                                    <br>
                                    <small>{s name="withTax"}with Tax{/s}: {$orderContext->shippingAmount|currency}</small>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

            </div>
            <div class="b2b-billing">
                {if $paymentName}
                    <div class="panel has--border">
                        <div class="panel--body">

                            <table class="is--full">
                                <tr>
                                    <td class="is--bold">{s name="Billing"}Billing{/s}:</td>
                                    <td>{$paymentName}</td>
                                </tr>
                            </table>

                        </div>
                    </div>
                {/if}
            </div>
        </div>

        {if $orderInfo}
            <div class="block-group">
                <div class="panel has--border">
                    <div class="panel--body">
                        <div class="panel--body">
                            <span class="is--bold">{s name="OrderList"}Order List{/s}:</span> {$orderInfo}
                        </div>
                    </div>
                </div>
            </div>
        {/if}

        <div class="block-group">
            <div class="panel has--border">
                <div class="panel--body">
                    <table class="is--full changeable-values">
                        <tr class="b2b-order-reference {if !$orderEditable}is--disabled{/if}">
                            <td class="is--bold is--label">{s name="OrderReferenceNumber"}Order reference number{/s}</td>
                            <td class="order-reference--col">
                                <div class="order-reference--edit">
                                    <input type="text" {if !$orderEditable}disabled="disabled"{/if} class="is--full" name="orderReferenceHolder" value="{$orderContext->orderReference}" data-b2b-form-input-holder="true" data-targetElement="orderReferenceHidden" placeholder="{s name="NoOrderReference"}No Order reference number defined{/s}">
                                </div>
                            </td>
                        </tr>
                        <tr class="b2b-requested-delivery-date {if !$orderEditable}is--disabled{/if}">
                            <td class="is--bold">{s name="RequestedDeliveryDate"}requested delivery date{/s}</td>
                            <td class="requested-delivery-date--col">
                                <div class="requested-delivery-date--edit">
                                    <input type="text" {if !$orderEditable}disabled="disabled"{/if} class="is--full" name="requestedDeliveryDateHolder" value="{$orderContext->requestedDeliveryDate}" placeholder="{s name="NoRequestedDeliveryDate"}No requested delivery date defined{/s}" data-b2b-form-input-holder="true" data-targetElement="requestedDeliveryDateHidden">
                                </div>
                            </td>
                        </tr>
                        <tr class="b2b-comment">
                            <td class="is--bold is--label">
                                {s name="Comment"}Comment{/s}:
                            </td>
                            <td>
                                <div class="b2b-comment--col">
                                    <div class="b2b-comment--edit">
                                        <textarea class="is--full" placeholder="{s name="CommentInputPlaceholder"}Enter a comment for this order.{/s}" data-b2b-form-input-holder="true" data-targetElement="commentHidden">{strip}
                                            {$comment}
                                        {/strip}</textarea>
                                    </div>
                                </div>
                            </td>
                        </tr>

                    </table>
                </div>
            </div>
        </div>

    </div>
{/block}

{* Modal Content: Grid Component: Bottom *}
{block name="b2b_modal_base_content_inner_scrollable_inner_bottom_inner"}

    {$orderEditable = $orderContext->isEditable()}

    <div class="bottom--actions">
        <form action="{url action=updateMasterData}" method="post" data-ajax-panel-trigger-reload="order-grid,order-clearance-grid">
            {if $orderEditable}
                <input type="hidden" name="orderReference" class="orderReferenceHidden" value="{$orderContext->orderReference}">
                <input type="hidden" name="requestedDeliveryDate" class="requestedDeliveryDateHidden" value="{$orderContext->requestedDeliveryDate}">
            {/if}
            <input type="hidden" name="orderContextId" value="{$orderContext->id}">
            <input type="hidden" name="comment" class="commentHidden" value="{$comment}">
            <button type="submit" class="btn is--primary">{s name="Save"}Save{/s}</button>
        </form>
    </div>
{/block}