<a href="{url controller='wishlist'}" rel="nofollow"
   class="buybox--button btn is--large is--center btn--notepad btn--wishlist"
   title="{"{s namespace="frontend/plugins/swag_advanced_cart/checkout_actions" name='Wishlists'}{/s}"|escape}"
   data-ajaxUrl="{url controller='note' action='ajaxAdd' ordernumber=$sArticle.ordernumber}"
   data-text="{s namespace="frontend/plugins/swag_advanced_cart/checkout_actions" name='Wishlists'}{/s}"
   data-open-wishlist-modal="true"
   data-ordernumber="{$sArticle.ordernumber}">

    <i class="icon--text is--large cart--info-text"></i>
    <span class="action--text">
        {s namespace="frontend/plugins/swag_advanced_cart/article_detail" name='AddToWishlist'}{/s}
    </span>
</a>
