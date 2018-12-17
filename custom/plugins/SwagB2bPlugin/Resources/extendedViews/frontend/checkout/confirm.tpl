{extends file="parent:frontend/checkout/confirm.tpl"}

{* B2B Account Header *}
{block name="frontend_index_content_top"}
    {if !$b2bSuite}
        {$smarty.block.parent}
    {else}
        {include file="frontend/_base/topbar.tpl"}
        {$smarty.block.parent}
    {/if}
{/block}

{block name='frontend_checkout_confirm_tos_panel'}
    {if !$b2bSuite}
        {$smarty.block.parent}
    {else}
        {if $orderAllowed}
            {$smarty.block.parent}
        {elseif $b2bCartMode === 'order'}
            {if !{config name='IgnoreAGB'}}
                <input type="hidden" id="sAGB" name="sAGB" value="checked" />
            {/if}
            <div class="tos--panel panel"></div>
        {/if}

        <input type="hidden" {if $orderContext->orderReference}value="{$orderContext->orderReference}"{/if} name="b2bOrderReference" class="b2bOrderReferenceClass">
        <input type="hidden" {if $orderContext->requestedDeliveryDate}value="{$orderContext->requestedDeliveryDate}"{/if} name="b2bRequestedDeliveryDate" class="b2bRequestedDeliveryDateClass">
    {/if}
{/block}

{block name="frontend_checkout_confirm_form"}
    {if $b2bSuite}
        {if $b2bCartMode === 'clearance'}
            <h2>{s name="EditOrderPriorToClearance"  namespace="frontend/plugins/b2b_debtor_plugin"}Edit order prior to clearance{/s}</h2>

            <form action="{url controller=b2borderclearance action=stopAcceptance}" method="post">
                <button
                        class="btn is--primary is--large is--icon-left"
                        title="{s name="StopAcceptance" namespace="frontend/plugins/b2b_debtor_plugin"}Stop acceptance{/s}">
                    {s name="StopAcceptance" namespace="frontend/plugins/b2b_debtor_plugin"}Stop acceptance{/s} <i class="icon--cross"></i>
                </button>
            </form>
        {/if}

        {if $b2bCartMode === 'offerCheckout'}
            <h2>{s name="ReturnToTheOfferOverview"  namespace="frontend/plugins/b2b_debtor_plugin"}Return to the offer overview{/s}</h2>

            <form action="{url controller=b2boffer action=stopOffer}" method="post">
                <button class="btn is--primary is--large is--icon-left">{s name="StopOffer"  namespace="frontend/plugins/b2b_debtor_plugin"}Stop Offer{/s} <i class="icon--cross"></i></button>
            </form>
        {/if}

        <div class="container b2b--ajax-panel" data-plugins="b2bAutoSubmit" data-url="{url controller=b2bbudgetselect amount=$sAmountNet}"></div>
    {/if}
    {$smarty.block.parent}
{/block}

{* adresses equal case removed - can not happen in b2b *}
{block name="frontend_checkout_confirm_information_addresses_equal"}{if !$b2bSuite}{$smarty.block.parent}{/if}{/block}

{* addresses not equal - edit address button *}
{block name="frontend_checkout_confirm_information_addresses_billing_panel_actions_change_address"}
    {if !$b2bSuite}
        {$smarty.block.parent}
    {else}
        <span class="b2b--ajax-panel">
            <a href="{url controller=b2baddressselect action=index type=billing selectedId=$activeBillingAddressId}"
               data-target="b2b-address-select"
               title="{s name="ConfirmAddressSelectButton"}Change address{/s}"
               class="btn ajax-panel-link">
                {s name="ConfirmAddressSelectButton"}Change address{/s}
            </a>
        </span>
    {/if}
{/block}

{* addresses not equal - select billing address button  - MUTED *}
{block name="frontend_checkout_confirm_information_addresses_billing_panel_actions_select_address"}{if !$b2bSuite}{$smarty.block.parent}{/if}{/block}

{* addresses not equal - edit address button *}
{block name="frontend_checkout_confirm_information_addresses_shipping_panel_actions_change_address"}
    {if !$b2bSuite}
        {$smarty.block.parent}
    {else}
        <span class="b2b--ajax-panel">
            <a href="{url controller=b2baddressselect action=index type=shipping selectedId=$activeShippingAddressId}"
                data-target="b2b-address-select"
                data-title="{s name="ConfirmAddressSelectButton"}Change address{/s}"
                class="btn ajax-panel-link"
                title="{s name="ConfirmAddressSelectButton"}Change address{/s}"
            >
                {s name="ConfirmAddressSelectButton"}Change address{/s}
            </a>
        </span>
    {/if}
{/block}

{* addresses not equal - select shipping address button - MUTED *}
{block name="frontend_checkout_confirm_information_addresses_shipping_panel_actions_select_address"}{if !$b2bSuite}{$smarty.block.parent}{/if}{/block}

{block name="frontend_index_content" append}
    {if $b2bSuite}
        <div class="b2b--ajax-panel b2b-modal-panel" data-plugins="b2bGridComponent" data-id="b2b-address-select"></div>
    {/if}
{/block}

{block name="frontend_checkout_cart_footer_add_voucher"}
    {if !$b2bSuite}
        {$smarty.block.parent}
    {else}
        <div class="b2b--row b2b--order-reference">
            <p>
                {s name="CustomOrderReferenceNumberHelpText" namespace="frontend/plugins/b2b_debtor_plugin"}Assign a unique reference number to to match this order with a cost unit{/s}:
            </p>
            <input type="text" name="b2bOrderReferenceHolder" data-b2b-form-input-holder="true" {if $orderContext && $orderContext->orderReference}value="{$orderContext->orderReference}"{/if} data-targetElement="b2bOrderReferenceClass" placeholder="{s name="CustomOrderReferenceNumber" namespace="frontend/plugins/b2b_debtor_plugin"}Order reference number{/s}">
        </div>
        <div class="b2b--row b2b--delivery-date">
            <p>
                {s name="RequestedDeliveryDateHelpText" namespace="frontend/plugins/b2b_debtor_plugin"}If you wish a specific delivery date you can fill in the requested date in the following textfield{/s}:
            </p>
            <input type="text" name="b2bDeliveryDateHolder" data-b2b-form-input-holder="true" {if $orderContext && $orderContext->requestedDeliveryDate}value="{$orderContext->requestedDeliveryDate}"{/if} data-targetElement="b2bRequestedDeliveryDateClass" placeholder="{s name="RequestedDeliveryDate" namespace="frontend/plugins/b2b_debtor_plugin"}Requested delivery date{/s}">
        </div>
    {/if}
{/block}

{block name="frontend_checkout_confirm_error_messages"}
    {if !$b2bSuite}
        {$smarty.block.parent}
    {else}
        {if !$orderAllowed}
            {block name="frontend_checkout_confirm_stockinfo"}
                {b2b_error list=$orderErrorMessages}
            {/block}
        {/if}
        {b2b_contingent_information errors=$orderErrorMessages information=$orderInformationMessages}
        {$smarty.block.parent}
    {/if}
{/block}

{block name="frontend_checkout_confirm_confirm_table_actions"}
    {if !$b2bSuite}
        {$smarty.block.parent}
    {else}
        {if $orderAllowed}
            {$smarty.block.parent}
        {else}
            <div class="table--actions actions--bottom">
                <div class="main--actions">
                    {block name="frontend_checkout_confirm_stockinfo"}{/block}
                    {if $b2bCartMode === 'order'}
                        <button type="submit" class="btn is--primary is--large right is--icon-right" form="confirm--form" data-preloader-button="true">
                            {s name="AskForOrderClearanceActionSubmit" namespace="frontend/plugins/b2b_debtor_plugin"}Request clearance{/s}
                            <i class="icon--arrow-right"></i>
                        </button>
                    {/if}
                    {if !$isB2bOffer && $b2bCartMode !== 'offerCheckout'}
                        <button
                                type="submit"
                                name="B2bOffer"
                                class="btn is--default is--large action--offer-create is--icon-right {b2b_acl controller=b2bcreateofferthroughcart action=createOffer}"
                                form="confirm--form"
                                data-preloader-button="true"
                                data-checkFormIsValid="false"
                                value="true"
                                formnovalidate="formnovalidate"
                        >
                            {s name="RequestAnOffer" namespace="frontend/plugins/b2b_debtor_plugin"}Request an Offer{/s}
                            <i class="icon--arrow-right"></i>
                        </button>
                    {/if}
                </div>
            </div>
        {/if}
    {/if}
{/block}

{block name='frontend_checkout_confirm_submit'}
    {$smarty.block.parent}
    {if !$isB2bOffer && $b2bSuite && $b2bCartMode !== 'offerCheckout'}
        <button
                type="submit"
                name="B2bOffer"
                class="btn is--default is--large action--offer-create is--icon-right {b2b_acl controller=b2bcreateofferthroughcart action=createOffer}"
                form="confirm--form"
                data-preloader-button="true"
                data-checkFormIsValid="false"
                value="true"
                formnovalidate="formnovalidate"
        >
            {s name="RequestAnOffer" namespace="frontend/plugins/b2b_debtor_plugin"}Request an Offer{/s}
            <i class="icon--arrow-right"></i>
        </button>
    {/if}
{/block}

{block name='frontend_checkout_confirm_confirm_footer'}
    {if !$b2bSuite}
        {$smarty.block.parent}
    {else}
        {if {b2b_acl_check controller=b2borderlistremote action=remoteListCart}}
            <div class="table--actions actions--bottom">
                <div class="block-group group--checkout-actions">
                    <div class="block block--orderlist">
                        <div class="is--b2b-ajax-panel b2b--ajax-panel"
                             data-id="order-list-remote-box"
                             data-plugins="b2bOrderList"
                             data-url="{url controller=b2borderlistremote action=remoteListCart cartId=$sessionId type=detail}"></div>
                    </div>
                </div>
            </div>
        {/if}

        {$smarty.block.parent}
    {/if}
{/block}

