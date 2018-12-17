{namespace name=frontend/plugins/b2b_debtor_plugin}

<li class="is--success {if !$item->authorIdentity->isBackend}is--right{else}is--left{/if}">
    <i class="icon--check"></i>
    <span>
        {$item->eventDate|date_format:'d.m.Y H:i'} &bull; {$item->authorIdentity->firstName} {$item->authorIdentity->lastName}
        {if $item->authorIdentity->isApi} ({s name="ApiLog"}API{/s}){/if}
    </span>

    {s name="OrderItemAdd"}An item has been added to the order.{/s}

    <br>

    <table class="table--unstyled">
        <tr>
            <td>{s name="Product"}Product{/s}:</td>
            <td>{$item->logValue->productName}</td>
        </tr>
        <tr>
            <td>{s name="Productnumber"}Productnumber{/s}:</td>
            <td>{$item->logValue->orderNumber}</td>
        </tr>
        <tr>
            <td>{s name="Quantity"}Quantity{/s}:</td>
            <td>{$item->logValue->newValue}</td>
        </tr>
        {if $item->logValue->oldValue}
            <tr>
                <td>{s name="Comment"}Comment{/s}:</td>
                <td>{$item->logValue->oldValue}</td>
            </tr>
        {/if}
    </table>
</li>