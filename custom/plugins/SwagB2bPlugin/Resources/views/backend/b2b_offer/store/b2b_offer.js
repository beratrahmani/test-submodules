Ext.define('Shopware.apps.b2bOffer.store.B2bOffer', {
    extend: 'Shopware.store.Listing',
    model: 'Shopware.apps.b2bOffer.model.B2bOffer',
    remoteSort: true,

    configure: function() {
        return {
            controller: 'B2bOffer',
            proxy: {
                type: 'ajax',
                url: '{url module=backend controller=B2bOffer action=list}',
                reader: {
                    type: 'json',
                    root: 'data',
                    totalProperty: 'count'
                }
            },
        };
    }
});