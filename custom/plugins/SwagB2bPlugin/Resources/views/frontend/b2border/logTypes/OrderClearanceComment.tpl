{namespace name=frontend/plugins/b2b_debtor_plugin}

<li class="{if !$item->authorIdentity->isBackend}is--right{else}is--left{/if} {if !$item->logValue->newValue}is--error {else}is--info{/if}">
    <i class="icon--info" title="{s name="Info"}Info{/s}"></i>
    <span>
        {$item->eventDate|date_format:'d.m.Y H:i'} &bull; {$item->authorIdentity->firstName} {$item->authorIdentity->lastName}
        {if $item->authorIdentity->isApi} ({s name="ApiLog"}API{/s}){/if}
    </span>

    {if !$item->logValue->newValue}
        {s name="OrderCommentWasDeleted"}The order comment has been deleted.{/s}
    {else}
        {s name="OrderCommentChange"}The order comment has been changed to{/s}:
    {/if}

    <br>

    <strong>{$item->logValue->newValue}</strong>

    <table class="table--unstyled {if !$item->logValue->oldValue}is--hidden{/if}">
        <tr>
            <td>{s name="OldComment"}Old comment{/s}:</td>
            <td>{$item->logValue->oldValue}</td>
        </tr>
    </table>
</li>