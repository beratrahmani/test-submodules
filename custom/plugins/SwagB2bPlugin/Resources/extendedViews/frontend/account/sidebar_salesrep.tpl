{namespace name=frontend/plugins/b2b_debtor_plugin}

<li class="navigation--entry">
    <a href="{url controller="b2bsalesrepresentative"}" title="{s name="ClientOverview"}Client overview{/s}" class="navigation--link {b2b_acl controller=b2bsalesrepresentative action=index} {if $Controller == 'b2bsalesrepresentative'}is--active{/if}">
        {s name="ClientOverview"}Client overview{/s}
    </a>
</li>
<li class="navigation--entry">
    <a href="{url controller='b2baccount'}" title="{s name="MyAccount"}My account{/s}" class="navigation--link {if $Controller == 'b2baccount'}is--active{/if}">
        {s name="MyAccount"}My account{/s}
    </a>
</li>