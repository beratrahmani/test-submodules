Ext.define('Shopware.apps.SwagAdvancedCart.store.Cart', {
    extend: 'Shopware.store.Listing',

    configure: function () {
        return {
            controller: 'SwagAdvancedCart'
        };
    },
    model: 'Shopware.apps.SwagAdvancedCart.model.Cart'
});
