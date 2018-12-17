{if $showAccountMenu}
    {include file="frontend/account/sidebar.tpl"}
{else}
    <div class="hide-menu">
        {include file="frontend/account/sidebar.tpl"}
    </div>
{/if}