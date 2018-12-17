{if empty({config name=replaceNote})}
    {block name="frontend_index_cart_shop_menu_entry"}
        <div class="top-bar--cart-list navigation--entry">
            {block name="frontend_index_cart_shop_menu_entry_link"}
                <a href="{url controller='wishlist'}" title="{"{s namespace="frontend/plugins/swag_advanced_cart/shop_menu" name='Wishlists'}Wishlists{/s}"|escape}" class="cart--navigation-link note navigation--link">
                    <i class="icon--text"></i>
                    {s namespace="frontend/plugins/swag_advanced_cart/shop_menu" name='Wishlists'}Wishlists{/s}
                </a>
            {/block}
        </div>
    {/block}
{/if}
