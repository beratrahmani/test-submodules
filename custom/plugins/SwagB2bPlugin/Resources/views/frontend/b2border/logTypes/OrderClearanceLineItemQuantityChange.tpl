{namespace name=frontend/plugins/b2b_debtor_plugin}

<li class="{if !$item->authorIdentity->isBackend}is--right{else}is--left{/if} is--notice">
    <i class="icon--warning" title="{s name="Info"}Info{/s}"></i>
    <span>
        {$item->eventDate|date_format:'d.m.Y H:i'} &bull; {$item->authorIdentity->firstName} {$item->authorIdentity->lastName}
        {if $item->authorIdentity->isApi} ({s name="ApiLog"}API{/s}){/if}
    </span>

    {s name="OrderItemQuantityChange"}The quantity of an item has been changed.{/s}

    <br>
    <table class="table--unstyled">
        <tr>
            <td>{s name="OrderQuantityOld"}Old quantity{/s}:</td>
            <td>{$item->logValue->oldValue}</td>
        </tr>
        <tr>
            <td>{s name="OrderQuantityNew"}New quantity{/s}:</td>
            <td>{$item->logValue->newValue}</td>
        </tr>
        <tr>
            <td>{s name="Product"}Product{/s}:</td>
            <td>{$item->logValue->productName}</td>
        </tr>
        <tr>
            <td>{s name="Productnumber"}Productnumber{/s}:</td>
            <td>{$item->logValue->orderNumber}</td>
        </tr>
    </table>
</li>