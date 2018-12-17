{namespace name=frontend/plugins/b2b_debtor_plugin}

<li>
    {if {b2b_acl_check controller=b2borderlist action=index}}
        <a title="{s name="OrderLists"}Order lists{/s}" href="{url controller=b2borderlist}">
            {s name="OrderLists"}Order lists{/s}
        </a>
    {else}
        <a title="{s name="Note"}Note{/s}" href="{url controller=note}">
            {s name="Note"}Note{/s}
        </a>
    {/if}
</li>