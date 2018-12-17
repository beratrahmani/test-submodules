{if $showMenuEntry}
    {include file='frontend/index/sites-navigation.tpl'}
{else}
    <div class="hide-menu">
        {include file='frontend/index/sites-navigation.tpl'}
    </div>
{/if}