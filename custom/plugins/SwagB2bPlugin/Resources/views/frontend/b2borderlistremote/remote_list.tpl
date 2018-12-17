{namespace name=frontend/plugins/b2b_debtor_plugin}

{if $message}
    {$snippetKey = 'OrderListRemote'|cat:$message.key}
    {include file="frontend/_includes/messages.tpl" type=$message.type content=$snippetKey|snippet:$snippetKey:'frontend/plugins/b2b_debtor_plugin'}
{/if}

<div class="b2b--alert-container">
    {foreach $validationExceptions as $exception}
        <div class="b2b-orderlist-notification-container">
            {include file="frontend/_includes/messages.tpl" type='error' b2bcontent=$exception}
        </div>
    {/foreach}
</div>

<div>
    <form method="post" action="{url controller=b2borderlistremote action=processAddProductsToOrderList}">
        <input type="hidden" name="referenceNumber" value="{$referenceNumber}" />
        <input type="hidden" name="products[0][referenceNumber]" value="{$referenceNumber}">
        <input type="hidden" name="products[0][quantity]" value="{if $b2b_quantity}{$b2b_quantity}{else}1{/if}">

        <div class="block-group group--actions">
            {include file="frontend/_base/order_list_remote_inner.tpl" type=$type}
        </div>
    </form>
</div>