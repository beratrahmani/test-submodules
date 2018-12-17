{namespace name=frontend/plugins/b2b_debtor_plugin}

    {if $item->logValue->newValue === "0"}
        <li class="is--notice {if !$item->authorIdentity->isBackend}is--right{else}is--left{/if}">
        <i class="icon--warning" title="{s name="Info"}Info{/s}"></i>
    {elseif $item->logValue->oldValue}
        <li class="is--success {if !$item->authorIdentity->isBackend}is--right{else}is--left{/if}">
        <i class="icon--check" title="{s name="Info"}Info{/s}"></i>
    {else}
        <li class="is--success {if !$item->authorIdentity->isBackend}is--right{else}is--left{/if}">
        <i class="icon--check" title="{s name="Info"}Info{/s}"></i>
    {/if}

    <i class="icon--check" title="{s name="Info"}Info{/s}"></i>
    <span>
        {$item->eventDate|date_format:'d.m.Y H:i'} &bull; {$item->authorIdentity->firstName} {$item->authorIdentity->lastName}
        {if $item->authorIdentity->isApi} ({s name="ApiLog"}API{/s}){/if}
    </span>

    {if !$item->logValue->newValue}
        {s name="DiscountDeleted"}The discount has been deleted.{/s}
    {elseif !$item->logValue->oldValue}
        {s name="DiscountAdded"}A discount was added.{/s}
    {else}
        {s name="DiscountChanged"}The discount has been changed to{/s}:
    {/if}

    <br>

    {if $item->logValue->newValue}
        <strong>{$item->logValue->newValue|currency}</strong>
    {/if}

    <table class="table--unstyled">
        <tr>
            <td>{s name="PreviousDiscount"}Previous discount{/s}:</td>
            <td>{$item->logValue->oldValue|currency}</td>
        </tr>
    </table>
</li>