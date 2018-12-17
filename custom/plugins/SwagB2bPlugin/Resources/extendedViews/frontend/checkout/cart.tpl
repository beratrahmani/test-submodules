{extends file="parent:frontend/checkout/cart.tpl"}

{block name="frontend_checkout_cart_footer_add_product"}
    {if $b2bSuite}
        {include file="frontend/checkout/add_cart_to_list.tpl" cartId=$sessionId}
    {else}
        {$smarty.block.parent}
    {/if}
{/block}

{block name="frontend_checkout_actions_inquiry"}
    {if !$b2bSuite}
        {$smarty.block.parent}
    {/if}
{/block}


{block name="frontend_checkout_actions_link_last_bottom"}
    {if $b2bSuite}

        <a href="{url controller=b2bcreateofferthroughcart action=createOffer}"
           title="{s name="RequestAnOffer" namespace="frontend/plugins/b2b_debtor_plugin"}Request an Offer{/s}"
           data-preloader-anchor="true"
           class="btn is--default is--large action--offer-create is--icon-right {b2b_acl controller=b2bcreateofferthroughcart action=createOffer}">
            {s name="RequestAnOffer" namespace="frontend/plugins/b2b_debtor_plugin"}Request an Offer{/s}
            <i class="icon--arrow-right"></i>
        </a>

        {$smarty.block.parent}
    {else}
        {$smarty.block.parent}
    {/if}
{/block}