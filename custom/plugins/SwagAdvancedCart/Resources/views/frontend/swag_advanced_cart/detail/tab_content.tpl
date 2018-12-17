{block name="frontend_detail_index_wishlist_tab_content"}
    {if $wishlistArticles|@count && !empty({config name=alsoListShow})}
        <div class="tab--container" data-tab-id="wishlist">

            {block name="frontend_detail_index_wishlist_tab_content_header"}
                <div class="tab--header">
                    <a href="#" class="tab--title"
                       title="{s name='DetailAlsoWishlistSlider' namespace='frontend/plugins/swag_advanced_cart/article_detail'}{/s}">
                        {s name='DetailAlsoWishlistSlider' namespace='frontend/plugins/swag_advanced_cart/article_detail'}{/s}
                    </a>
                </div>
            {/block}

            {block name="frontend_detail_index_wishlist_tab_content_content"}
                <div class="tab--content content--wishlist">
                    {include file="frontend/_includes/product_slider.tpl" articles=$wishlistArticles}
                </div>
            {/block}
        </div>
    {/if}
{/block}
