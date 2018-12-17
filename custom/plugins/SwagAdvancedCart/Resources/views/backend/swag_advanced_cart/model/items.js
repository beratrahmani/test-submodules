Ext.define('Shopware.apps.SwagAdvancedCart.model.Items', {
    extend: 'Shopware.data.Model',

    configure: function () {
        return {
            controller: 'SwagAdvancedCart',

            proxy: {
                type: 'ajax',
                api: {
                    detail: '{url controller="base" action="detail"}',
                    create: '{url controller="base" action="create"}',
                    update: '{url controller="base" action="update"}',
                    destroy: '{url controller="base" action="deleteItems"}'
                },
                reader: {
                    type: 'json',
                    root: 'data',
                    totalProperty: 'total'
                }
            }

        };
    },

    fields: [
        { name: 'id', type: 'int', useNull: true },
        { name: 'articleOrderNumber', type: 'string' },
        { name: 'articleId', type: 'int' },
        { name: 'name', type: 'string' },
        { name: 'quantity', type: 'int' },
        { name: 'price', type: 'float' },
        { name: 'sumPrice', type: 'float' }
    ]
});
