{namespace name=frontend/plugins/b2b_debtor_plugin}

<li class="is--info {if !$item->authorIdentity->isBackend}is--right{else}is--left{/if}">
    <i class="icon--info"></i>
    <span>
        {$item->eventDate|date_format:'d.m.Y H:i'} &bull; {$item->authorIdentity->firstName} {$item->authorIdentity->lastName}
        {if $item->authorIdentity->isApi} ({s name="ApiLog"}API{/s}){/if}
    </span>

    {s name="OrderStatusChange"}The order status has been changed.{/s}

    <br>
    <table class="table--unstyled">
        <tr>
            <td>{s name="OrderStatusPrevious"}Previous Status{/s}:</td>
            <td>{$item->logValue->oldValue|snippet:{$item->logValue->oldValue}:"frontend/plugins/b2b_debtor_plugin"}</td>
        </tr>
        <tr>
            <td>{s name="OrderStatusNew"}New Status{/s}:</td>
            <td>{$item->logValue->newValue|snippet:{$item->logValue->newValue}:"frontend/plugins/b2b_debtor_plugin"}</td>
        </tr>
    </table>
</li>