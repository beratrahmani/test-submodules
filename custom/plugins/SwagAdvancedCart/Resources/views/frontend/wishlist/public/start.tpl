{if $wishlist}
    {$sBreadcrumb = [['name'=>"{s name='MyWishlists' namespace='frontend/plugins/swag_advanced_cart/plugin'}Wunschlisten{/s}", 'link'=>{url controller=wishlist}], ['name'=>"{$wishlist.name}", 'link'=>{url id=$wishlist.hash}]]}
{else}
    {$sBreadcrumb = [['name'=>"{s namespace="frontend/plugins/swag_advanced_cart/public_list_notfound" name='NotFound'}{/s}", 'link'=>{url}]]}
{/if}
