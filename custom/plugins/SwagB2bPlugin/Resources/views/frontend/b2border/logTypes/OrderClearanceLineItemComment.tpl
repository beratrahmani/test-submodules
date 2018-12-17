{namespace name=frontend/plugins/b2b_debtor_plugin}

<li class="is--info {if !$item->authorIdentity->isBackend}is--right{else}is--left{/if}">
    <i class="icon--info" title="{s name="Info"}Info{/s}"></i>
    <span>
        {$item->eventDate|date_format:'d.m.Y H:i'} &bull; {$item->authorIdentity->firstName} {$item->authorIdentity->lastName}
        {if $item->authorIdentity->isApi} ({s name="ApiLog"}API{/s}){/if}
    </span>

    {s name="OrderItemCommentChange"}The comment of an order item has been changed to{/s}:

    <br>

    <strong>{$item->logValue->newValue}</strong>

    <table class="table--unstyled">
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