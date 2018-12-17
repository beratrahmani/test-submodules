Ext.define('Shopware.apps.SwagAdvancedCart.store.Items', {
    extend: 'Shopware.store.Listing',

    configure: function () {
        return {
            controller: 'SwagAdvancedCart',

            /**
             * Model proxy which defines
             * the urls for the CRUD actions.
             */
            proxy: {
                type: 'ajax',
                api: {
                    read: '{url controller="base" action="articles"}'
                },
                reader: {
                    type: 'json',
                    root: 'data',
                    totalProperty: 'total'
                }
            }

        };
    },

    model: 'Shopware.apps.SwagAdvancedCart.model.Items'
});
