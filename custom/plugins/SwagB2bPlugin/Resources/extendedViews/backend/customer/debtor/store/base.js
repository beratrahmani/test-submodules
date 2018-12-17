//{namespace name=backend/plugins/b2b_debtor_plugin}

Ext.define('Shopware.apps.Customer.debtor.store.Base', {
    extend: 'Ext.data.Store',
    model: 'Shopware.apps.Customer.debtor.model.User',
    proxy: {
        type: 'ajax',
        url: '{url module=backend controller=b2bdebtor action=userList}',
        extraParams: {
            debtor_id: null
        },
        reader: {
            type: 'json',
            root: 'data'
        }
    }
});
