//{namespace name=backend/plugins/b2b_debtor_plugin}

Ext.define('Shopware.apps.Customer.debtor.store.Emotion', {
    extend: 'Ext.data.Store',
    model: 'Shopware.apps.Customer.debtor.model.Emotion',
    proxy: {
        type: 'ajax',
        url: '{url module=backend controller=b2bdebtor action=getAllEmotions}',
        reader: {
            type: 'json',
            root: 'data'
        }
    }
});
