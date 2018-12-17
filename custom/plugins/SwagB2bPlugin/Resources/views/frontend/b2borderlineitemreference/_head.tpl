{namespace name=frontend/plugins/b2b_debtor_plugin}

<div class="content--inner b2b-order-overview">
    <div class="panel has--border">

        <div class="block-group">
            <span class="state">
                <strong>{s name="State"}State{/s}:</strong><br>
                {$state|snippet:{$state}:"backend/static/order_status"}
            </span>
            <span class="created-at">
                <strong>{s name="Date"}Date{/s}:</strong><br>
                    {$createdAt|date_format:'d.m.Y H:i'}
            </span>
            <span class="order-reference">
                <strong>{s name="Ordernumber"}Order number{/s}:</strong><br>
                    {$orderNumber}
            </span>
        </div>

        <div class="block-group">
            <span class="item-count">
                <strong>{s name="OrderItemQuantityPlural"}Order item quantity{/s}:</strong><br>
                {$itemCount}
            </span>
            <span class="value">
                <strong>{s name="OrderAmount"}Order Amount{/s}:</strong><br>
                {$amountNet|currency}
            </span>
            <span class="value">
                <strong>{s name="withTax"}with Tax{/s}:</strong><br>
                {$amount|currency}
            </span>
        </div>
    </div>
</div>