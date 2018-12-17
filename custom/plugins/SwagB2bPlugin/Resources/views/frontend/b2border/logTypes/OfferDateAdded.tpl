{namespace name=frontend/plugins/b2b_debtor_plugin}

<li class="is--notice {if !$item->authorIdentity->isBackend}is--right{else}is--left{/if}">
    <i class="icon--warning"></i>
    <span>
        {$item->eventDate|date_format:'d.m.Y H:i'} &bull; {$item->authorIdentity->firstName} {$item->authorIdentity->lastName}
        {if $item->authorIdentity->isApi} ({s name="ApiLog"}API{/s}){/if}
    </span>

    {s name="ExpirationDateWasAdded"}An expiration date was added.{/s}
    <br>
    <table class="table--unstyled">
        <tr>
            <td>{s name="NewDateAdded"}New date added{/s}:</td>
            <td>{$item->logValue->newValue|date_format:'d.m.Y H:i'}</td>
        </tr>
    </table>
</li>