{namespace name=frontend/plugins/b2b_debtor_plugin}

<li class="{if !$item->authorIdentity->isBackend}is--right{else}is--left{/if} is--info">
    <i class="icon--info" title="{s name="Info"}Info{/s}"></i>
    <span>
        {$item->eventDate|date_format:'d.m.Y H:i'} &bull; {$item->authorIdentity->firstName} {$item->authorIdentity->lastName}
        {if $item->authorIdentity->isApi} ({s name="ApiLog"}API{/s}){/if}
    </span>

    {s name="OrderStatusChange"}The order status has been changed.{/s}
    <br>
    {if $item->logValue->comment}
        {s name="Comment"}Comment{/s}:
        <strong>{$item->logValue->comment}</strong>
    {/if}

    <br>
    Type: StatusChange

    <br>
    <table class="table--unstyled">
        <tr>
            <td>{s name="OrderStatusPrevious"}Previous status{/s}:</td>
            <td>{$item->logValue->oldValue}</td>
        </tr>
        <tr>
            <td>{s name="OrderStatusNew"}New status{/s}:</td>
            <td>{$item->logValue->newValue}</td>
        </tr>
    </table>
</li>