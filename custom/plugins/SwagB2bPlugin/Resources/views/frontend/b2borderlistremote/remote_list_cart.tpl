{namespace name=frontend/plugins/b2b_debtor_plugin}

<h4>
    {s name="AddItemToListHeadline"}Add item to order list{/s}
</h4>

{if $message}
    {$snippetKey = 'OrderListRemoteCart'|cat:$message.key}
    {if $message.key === 'NoOrderList'}
        {$snippetKey = 'OrderListRemote'|cat:$message.key}
    {/if}

    {include file="frontend/_includes/messages.tpl" type=$message.type content=$snippetKey|snippet:$snippetKey:'frontend/plugins/b2b_debtor_plugin'}
{/if}
<div class="b2b--alert-container">
    {foreach $validationExceptions as $exception}
        {include file="frontend/_includes/messages.tpl" type='error' b2bcontent=$exception}
    {/foreach}
</div>

<div class="b2b-order-list--checkout">
    <form method="post" action="{url controller=b2borderlistremote action=addListThroughCart}">
        <input type="hidden" name="cartId" value="{$cartId}">

        <div class="block-group group--actions">
            {include file="frontend/_base/order_list_remote_inner.tpl" type=$type}
        </div>
    </form>
</div>
