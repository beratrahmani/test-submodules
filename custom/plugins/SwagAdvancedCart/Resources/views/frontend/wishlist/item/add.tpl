{block name='frontend_wishlist_item_add'}
    <div class="advancedCart--buybox">
        {if $item.article.isAvailable == 1}
            {block name='frontend_wishlist_item_add_form'}
                <form data-add-article="true"
                      name="sAddToBasket-{$item.id}"
                      method="post"
                      data-eventName="submit"
                    {if $theme.offcanvasCart}
                      data-showModal="false"
                      data-addArticleUrl="{url controller=checkout action=ajaxAddArticleCart}"
                    {/if}
                >
                    {include file='frontend/wishlist/item/ordernumber.tpl'}
                    {block name='frontend_wishlist_item_add_form_button'}
                        <div class="advanced-cart--buy-button">
                            <button class="buybox--button btn is--primary is--icon-right is--center is--large item-id-{$item.id}">
                                {s namespace='frontend/plugins/swag_advanced_cart/plugin' name='AddToCart'}{/s}
                                <i class="icon--arrow-right"></i>
                            </button>
                        </div>
                    {/block}
                    {block name='frontend_wishlist_item_add_form_order_number_input'}
                        <input type="hidden" name="sAdd" value="{$item.article.ordernumber}">
                    {/block}
                </form>
            {/block}
        {else}
            {include file='frontend/_includes/messages.tpl' type='error' content="{s namespace='frontend/plugins/swag_advanced_cart/plugin' name='CurrentlyUnavailable'}{/s}"}
        {/if}
    </div>
{/block}
