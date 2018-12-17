//{namespace name=backend/plugins/b2b_debtor_plugin}
//{block name="backend/customer/sales_representative/model/client.js"}
Ext.define('Shopware.apps.Customer.salesRepresentative.model.Client', {
    extend: 'Ext.data.Model',
    fields: [
        { name: 'id', type: 'int' },
        { name: 'client',  type: 'bool' },
        { name: 'email',  type: 'string' },
        { name: 'name', type: 'string' }
    ]
});
//{/block}