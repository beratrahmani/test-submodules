{extends file="parent:frontend/checkout/items/product.tpl"}
{namespace name="frontend/checkout/cart_item"}

{* Remove product from basket *}
{block name='frontend_checkout_cart_item_delete_article'}
    <div class="panel--td column--actions">
        <form action="{url action='deleteArticle' sDelete=$sBasketItem.id sTargetAction=$sTargetAction}"
              method="post">
            <button type="submit" class="btn is--small column--actions-link"
                    title="{"{s name='CartItemLinkDelete'}{/s}"|escape}"
            {if $sBasketItem.erasable === false}
                disabled
            {/if}
            >
                <i class="icon--cross"></i>
            </button>
        </form>
    </div>
{/block}

{block name='frontend_checkout_cart_item_details_sku'}
    {if $sBasketItem.additional_details.attributes.b2b_ordernumber && $sBasketItem.additional_details.attributes.b2b_ordernumber->get('custom_ordernumber')}
        <p class="content--sku content">
            {s name="CartItemInfoId"}{/s} {$sBasketItem.additional_details.attributes.b2b_ordernumber->get('custom_ordernumber')}
        </p>
    {else}
        {$smarty.block.parent}
    {/if}
{/block}