{namespace name=frontend/plugins/b2b_debtor_plugin}

<li class="navigation--entry">
    <a href="{url controller='b2bdashboard'}" title="{s name="Dashboard"}Dashboard{/s}" class="navigation--link {b2b_acl controller=b2bdashboard action=index} {if $Controller == 'b2bdashboard'}is--active{/if}">
        {s name="Dashboard"}Dashboard{/s}
    </a>
</li>
<li class="navigation--entry">
    <a href="{url controller='b2bcompany'}" title="{s name="Company"}Company{/s}" class="navigation--link {b2b_acl controller=b2bcompany action=index} {if $Controller == 'b2bcompany'}is--active{/if}">
        {s name="Company"}Company{/s}
    </a>
</li>
<li class="navigation--entry">
    <a href="{url controller='b2bstatistic'}" title="{s name="Statistics"}Statistics{/s}" class="navigation--link {b2b_acl controller=b2bstatistic action=index} {if $Controller == 'b2bstatistic'}is--active{/if}">
        {s name="Statistics"}Statistics{/s}
    </a>
</li>
<li class="navigation--entry">
    <a href="{url controller='b2border'}" title="{s name="Orders"}Orders{/s}" class="navigation--link {b2b_acl controller=b2border action=index} {if $Controller == 'b2border'}is--active{/if}">
        {s name="Orders"}Orders{/s}
    </a>
</li>
<li class="navigation--entry">
    <a href="{url controller='b2borderlist'}" title="{s name="OrderLists"}Order lists{/s}" class="navigation--link {b2b_acl controller=b2borderlist action=index} {if $Controller == 'b2borderlist'}is--active{/if}">
        {s name="OrderLists"}Order lists{/s}
    </a>
</li>
<li class="navigation--entry">
    <a href="{url controller='b2bfastorder'}" title="{s name="FastOrder"}Fast order{/s}" class="navigation--link {b2b_acl controller=b2bfastorder action=index} {if $Controller == 'b2bfastorder'}is--active{/if}">
        {s name="FastOrder"}Fast order{/s}
    </a>
</li>
<li class="navigation--entry">
    <a href="{url controller='b2boffer'}" title="{s name="Offers"}Offers{/s}" class="navigation--link {b2b_acl controller=b2boffer action=index} {if $Controller == 'b2boffer'}is--active{/if}">
        {s name="Offers"}Offers{/s}
    </a>
</li>
<li class="navigation--entry">
    <a href="{url controller='b2bordernumber'}" title="{s name="OrderNumbers"}Order numbers{/s}" class="navigation--link {b2b_acl controller=b2bordernumber action=index} {if $Controller == 'b2bordernumber'}is--active{/if}">
        {s name="OrderNumbers"}Order numbers{/s}
    </a>
</li>
<li class="navigation--entry">
    <a href="{url controller='b2baccount'}" title="{s name="MyAccount"}My account{/s}" class="navigation--link {if $Controller == 'b2baccount'}is--active{/if}">
        {s name="MyAccount"}My account{/s}
    </a>
</li>