//{namespace name=backend/plugins/b2b_debtor_plugin}
//{block name="backend/customer/sales_representative/store/base"}
Ext.define('Shopware.apps.Customer.salesRepresentative.store.Base', {
    extend: 'Ext.data.Store',
    model: 'Shopware.apps.Customer.salesRepresentative.model.Client',
    proxy: {
        type: 'ajax',
        url: '{url module=backend controller=b2bsalesrepresentative action=clientList}',
        extraParams: {
            sales_representative_id: null
        },
        reader: {
            type: 'json',
            root: 'data'
        }
    }
});
//{/block}