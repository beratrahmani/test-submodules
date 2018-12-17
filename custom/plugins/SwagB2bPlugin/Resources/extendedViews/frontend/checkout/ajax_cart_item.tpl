{extends file="parent:frontend/checkout/ajax_cart_item.tpl"}

{namespace name="frontend/checkout/ajax_cart"}

{* Article actions *}
{block name='frontend_checkout_ajax_cart_actions'}
    <div class="action--container">

        {$deleteUrl = {url controller="checkout" action="ajaxDeleteArticleCart" sDelete=$basketItem.id}}

        {if $basketItem.modus == 2}
            {$deleteUrl = {url controller="checkout" action="ajaxDeleteArticleCart" sDelete="voucher"}}
        {/if}

        {if $basketItem.modus != 4}
            <form action="{$deleteUrl}" method="post">
                <button
                        type="submit"
                        class="btn is--small action--remove"
                        title="{s name="AjaxCartRemoveArticle"}{/s}"
                        {if $basketItem.erasable === false}
                            disabled
                        {/if}
                >
                    <i class="icon--cross"></i>
                </button>
            </form>
        {/if}
    </div>
{/block}