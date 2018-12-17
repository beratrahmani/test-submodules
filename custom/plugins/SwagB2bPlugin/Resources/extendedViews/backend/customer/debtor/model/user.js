//{namespace name=backend/plugins/b2b_debtor_plugin}
Ext.define('Shopware.apps.Customer.debtor.model.User', {
    extend: 'Ext.data.Model',
    fields: [
        { name: 'id', type: 'int' },
        { name: 'email',  type: 'string' },
        { name: 'name', type: 'string' },
        { name: 'type', type: 'string' },
        { name: 'emotion', type: 'int'}
    ]
});
