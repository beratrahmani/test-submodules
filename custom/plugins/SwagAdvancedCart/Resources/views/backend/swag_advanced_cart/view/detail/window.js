//{namespace name=backend/swag_advanced_cart/view/main}

Ext.define('Shopware.apps.SwagAdvancedCart.view.detail.Window', {
    extend: 'Shopware.window.Listing',
    alias: 'widget.cart-detail-window',
    title: '{s name="detail/window/title"}Advanced Cart Details{/s}',
    height: 420,
    width: 900,

    configure: function () {
        return {
            listingGrid: 'Shopware.apps.SwagAdvancedCart.view.detail.Cart',
            listingStore: 'Shopware.apps.SwagAdvancedCart.store.Items'
        };
    },

    createListingStore: function () {
        var me = this;
        var store = me.callParent(arguments);
        store.getProxy().extraParams.id = me.record.get('id');
        return store;
    }
});
