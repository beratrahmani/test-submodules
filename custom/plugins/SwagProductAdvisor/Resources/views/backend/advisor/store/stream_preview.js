//

//{namespace name="backend/advisor/main"}
//{block name="backend/advisor/store/stream-store"}
Ext.define('Shopware.apps.Advisor.store.StreamPreview', {
    extend: 'Ext.data.Store',
    model: 'Shopware.apps.Advisor.model.Product',

    proxy: {
        type: 'ajax',
        url: '{url controller=Advisor action=getProductsByStreamIdAjax}',
        reader: {
            type: 'json',
            root: 'data'
        }
    }
});
//{/block}