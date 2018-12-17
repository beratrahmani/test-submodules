{namespace name=frontend/plugins/b2b_debtor_plugin}

<li class="{if !$item->authorIdentity->isBackend}is--right{else}is--left{/if} is--success">
    <i class="icon--check" title="{s name="Changed"}Changed{/s}"></i>
    <span>
        {$item->eventDate|date_format:'d.m.Y H:i'} &bull; {$item->authorIdentity->firstName} {$item->authorIdentity->lastName}
        {if $item->authorIdentity->isApi} ({s name="ApiLog"}API{/s}){/if}
    </span>

    {s name="OrderReferenceChange"}The order reference has been changed to{/s}:

    <br>

    <strong>{$item->logValue->newValue}</strong>

    <table class="table--unstyled {if $item->logValue->oldValue}is--hidden{/if}">
        <tr>
            <td>{s name="OldReference"}Old reference{/s}:</td>
            <td>{$item->logValue->oldValue}</td>
        </tr>
    </table>
</li>