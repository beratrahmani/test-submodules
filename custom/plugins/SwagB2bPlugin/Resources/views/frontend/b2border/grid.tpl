{namespace name=frontend/plugins/b2b_debtor_plugin}

{if $message}
    {include file="frontend/_includes/messages.tpl" type="{$message.type}" content="{$message.snippet|snippet:$message.snippet:"frontend/plugins/b2b_debtor_plugin"}"}
{/if}

{include file="frontend/_grid/order-grid.tpl" gridState=$orderGrid}