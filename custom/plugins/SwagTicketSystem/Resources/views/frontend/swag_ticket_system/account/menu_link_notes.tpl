<li class="navigation--entry">
    <a href="{url controller='ticket' action='listing'}" class="navigation--link{if {controllerName} == 'ticket' && ({controllerAction} == 'detail' || {controllerAction} == 'listing') }  is--active{/if}">
        {s namespace="frontend/account/sidebar" name="TicketsystemAccountLinkListing"}{/s}
    </a>
</li>
<li class="navigation--entry">
    <a href="{url controller='ticket' action='request'}" class="navigation--link{if $sAction == 'request'} is--active{/if}">
        {s namespace="frontend/account/sidebar" name="TicketsystemAccountLinkRequest"}{/s}
    </a>
</li>