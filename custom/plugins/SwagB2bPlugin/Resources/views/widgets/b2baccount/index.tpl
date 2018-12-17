{namespace name=frontend/plugins/b2b_debtor_plugin}

{if $identity->debtor}
    <span>
        {s name="LoggedInAs"}Logged in as{/s} {$identity->firstName} {$identity->lastName}
    </span>
    <i class="icon--star-half"></i> {s name="Contact"}Contact{/s}
{elseif $isSalesRep}
    <span>
        {s name="LoggedInAs"}Logged in as{/s} {$identity->firstName} {$identity->lastName}
    </span>
    <i class="icon--star-empty"></i> {s name="SalesRepresentative"}Sales representative{/s}
{else}
    <span>
        {s name="LoggedInAs"}Logged in as{/s} {$identity->firstName} {$identity->lastName}
    </span>
    <i class="icon--star"></i> {s name="Debtor"}Debtor{/s}
{/if}