//{namespace name=backend/swag_advanced_cart/view/main}

Ext.define('Shopware.apps.SwagAdvancedCart.view.list.Window', {
    extend: 'Shopware.window.Listing',
    alias: 'widget.product-list-window',
    height: 500,
    title: '{s name="list/window/title"}Advanced Cart Übersicht{/s}',

    configure: function () {
        return {
            listingGrid: 'Shopware.apps.SwagAdvancedCart.view.list.Cart',
            listingStore: 'Shopware.apps.SwagAdvancedCart.store.Cart'
        };
    }
});
