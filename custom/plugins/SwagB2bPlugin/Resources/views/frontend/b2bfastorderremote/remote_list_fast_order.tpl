{namespace name=frontend/plugins/b2b_debtor_plugin}

<div class="b2b-remote-list-fast-order">
    {if $message}
        {$snippetKey = 'RemoteListFastOrder'|cat:$message.key}
        {if $message.key === 'NoOrderList'}
            {$snippetKey = 'OrderListRemote'|cat:$message.key}
        {/if}

        {include file="frontend/_includes/messages.tpl" type=$message.type content=$snippetKey|snippet:$snippetKey:'frontend/plugins/b2b_debtor_plugin'}
    {/if}

    {foreach $validationExceptions as $exception}
        {include file="frontend/_includes/messages.tpl" type='error' b2bcontent=$exception}
    {/foreach}

    <div class="block-group group--actions">
        <div class="block block--orderlist action--order-list">
            <form class="form--inline order-list-form" method="post" action="{url controller=b2bfastorderremote action=addProductsToOrderList}">
                {include file="frontend/_base/order_list_remote_inner.tpl" type=$type}
            </form>
        </div>
        <div class="block block--cart action--cart-add">
            <form class="form--inline cart-form" method="post" action="{url controller=b2bfastorderremote action=addProductsToCart}">
                <button class="btn is--primary is--large cart--link is--icon-right is--center"
                        name="addToCart"
                        title="{s name="AddProductsToBasketSubmitButton"}Add to basket{/s}">
                    {s name="AddProductsToBasketSubmitButton"}Add to basket{/s}
                    <i class="icon--arrow-right"></i>
                </button>
            </form>
        </div>
    </div>
</div>