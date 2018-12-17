{namespace name=frontend/plugins/b2b_debtor_plugin}

<li class="is--notice {if !$item->authorIdentity->isBackend}is--right{else}is--left{/if}">
    <i class="icon--warning"></i>
    <span>
        {$item->eventDate|date_format:'d.m.Y H:i'} &bull; {$item->authorIdentity->firstName} {$item->authorIdentity->lastName}
        {if $item->authorIdentity->isApi} ({s name="ApiLog"}API{/s}){/if}
    </span>

    {s name="PriceOfArticleChanged"}The Price of the Article was changed.{/s}
    <br>
    <table class="table--unstyled">
        <tr>
            <td>{s name="PreviousPrice"}Previous Price{/s}:</td>
            <td>{$item->logValue->oldValue|currency}</td>
        </tr>
        <tr>
            <td>{s name="NewPrice"}New Price{/s}:</td>
            <td>{$item->logValue->newValue|currency}</td>
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