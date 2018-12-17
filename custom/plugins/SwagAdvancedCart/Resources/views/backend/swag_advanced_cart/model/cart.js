Ext.define('Shopware.apps.SwagAdvancedCart.model.Cart', {
    extend: 'Shopware.data.Model',

    configure: function () {
        return {
            controller: 'SwagAdvancedCart',
            detail: 'Shopware.apps.SwagAdvancedCart.view.detail.Cart'
        };
    },

    fields: [
        { name: 'id', type: 'int', useNull: true },
        { name: 'name', type: 'string' },
        { name: 'customer', type: 'string' },
        { name: 'customerId', type: 'int' },
        { name: 'modified', type: 'date', dateFormat: 'Y-m-d H:i:s' },
        { name: 'expire', type: 'date' },
        { name: 'cartItems', type: 'string' },
        { name: 'published', type: 'boolean' },
        { name: 'shopId', type: 'int' }
    ],

    associations: [
        {
            relation: 'ManyToOne',
            field: 'shopId',

            type: 'hasMany',
            model: 'Shopware.apps.Base.model.Shop',
            name: 'getShop',
            associationKey: 'shop'
        }
    ]
});
