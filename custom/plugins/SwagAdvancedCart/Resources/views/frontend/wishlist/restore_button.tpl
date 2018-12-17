{block name="frontend_wishlist_index_list_main_buttons_add"}
    <a href="{url controller=wishlist action=restore id=$wishList.basketID}" class="manage-buttons--button btn is--primary{if $wishList.items|count <= 0} cart--hidden{/if}">
        {s namespace="frontend/plugins/swag_advanced_cart/plugin" name='AllIntoBasket'}{/s}
    </a>
{/block}
