{namespace name=frontend/plugins/b2b_debtor_plugin}

<div class="content--inner b2b-order-overview">
    <div class="panel has--border">
        <div class="block-group">
            <span class="item-count">
                <strong>{s name="OrderItemQuantityPlural"}Order item quantity{/s}:</strong><br>
                {$itemCount}
            </span>
            <span class="value">
                <strong>{s name="OrderAmount"}Order amount{/s}:</strong><br>
                {$amountNet|currency}
            </span>
            <span class="value">
                <strong>{s name="withTax"}with Tax{/s}:</strong><br>
                {$amount|currency}
            </span>
        </div>
    </div>
</div>