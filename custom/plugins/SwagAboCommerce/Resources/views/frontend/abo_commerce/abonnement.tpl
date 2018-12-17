{namespace name="frontend/abo_commerce/orders"}

{* Order date *}
{block name="frontend_account_abonnement_item_date"}
    <div class="order--date panel--td column--date">

        {block name="frontend_account_abonnement_item_date_label"}
            <div class="column--label">
                {s name="OrderColumnDate" namespace="frontend/account/orders"}{/s}:
            </div>
        {/block}

        {block name="frontend_account_abonnement_item_date_value"}
            <div class="column--value">
                {$order.order.orderTime|date:DATETIME_SHORT}
            </div>
        {/block}
    </div>
{/block}

{* Order number *}
{block name="frontend_account_abonnement_item_number"}
    <div class="order--number panel--td column--id is--bold">

        {block name="frontend_account_abonnement_item_number_label"}
            <div class="column--label">
                {s name="OrderColumnId" namespace="frontend/account/orders"}{/s}:
            </div>
        {/block}

        {block name="frontend_account_abonnement_item_number_value"}
            <div class="column--value">
                {$order.lastOrder.number}
            </div>
        {/block}
    </div>
{/block}

{* Delivery interval *}
{block name="frontend_account_abonnement_item_delivery_interval"}
    <div class="order--number panel--td column--id is--bold">

        {block name="frontend_account_abonnement_delivery_interval_label"}
            <div class="column--label">
                {s name="AboCommerceOrdersDeliveryInterval"}{/s}
            </div>
        {/block}

        {block name="frontend_account_abonnement_delivery_interval_value"}
            <div class="column--value">
                {s name="AboCommerceOrdersEach"}{/s} {$order.deliveryInterval} {if $order.deliveryIntervalUnit eq 'months'}{s name="AboCommerceOrdersMonths"}{/s}{else}{s name="AboCommerceOrdersWeeks"}{/s}{/if}
            </div>
        {/block}
    </div>
{/block}

{* Expiry date *}
{block name="frontend_account_abonnement_expiry"}
    <div class="order--number panel--td column--id is--bold">

        {block name="frontend_account_abonnement_expiry_label"}
            <div class="column--label">
                {s name="AboCommerceOrdersExpiry"}{/s}
            </div>
        {/block}

        {block name="frontend_account_abonnement_expiry_value"}
            <div class="column--value">
                <strong>{$order.lastRun|date:DATE_MEDIUM}</strong>
            </div>
        {/block}
    </div>
{/block}

{* Abonnement actions *}
{block name="frontend_account_abonnement_item_actions"}
    <div class="order--actions panel--td column--actions">

        <a href="#order{$order.lastOrder.number}"
           class="btn abo--order-button is--secondary is--small"
           title="{"{s name="AboCommerceOrdersShowDetailsOnly"}{/s}"|escape}"
           data-collapse-panel="true"
           data-collapseTarget="#order{$order.lastOrder.number}{$order.id}">
            {s name="AboCommerceOrdersShowDetailsOnly"}{/s}
        </a>
    </div>
{/block}
