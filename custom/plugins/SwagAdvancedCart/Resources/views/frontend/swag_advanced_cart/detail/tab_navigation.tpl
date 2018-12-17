{block name="frontend_detail_index_wishlist_tab_navigation"}
    {if $wishlistArticles|@count && !empty({config name=alsoListShow})}
        <a href="#content--wishlist-slider"
           title="{s name='DetailAlsoWishlistSlider' namespace='frontend/plugins/swag_advanced_cart/article_detail'}{/s}"
           class="tab--link">
            {s name='DetailAlsoWishlistSlider' namespace='frontend/plugins/swag_advanced_cart/article_detail'}{/s}
        </a>
    {/if}
{/block}
