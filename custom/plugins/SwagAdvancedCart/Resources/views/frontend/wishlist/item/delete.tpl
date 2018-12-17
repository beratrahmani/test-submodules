{if !$hideDelete}
    <a href="{url controller='wishlist' action='removeOne' cartItemId=$item.id}" title="{s name='Delete' namespace='frontend/plugins/swag_advanced_cart/plugin'}{/s}" class="note--delete">
        <i class="icon--cross"></i>
    </a>
{/if}
