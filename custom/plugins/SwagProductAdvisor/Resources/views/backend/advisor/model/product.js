//

//{namespace name="backend/advisor/main"}
//{block name="backend/advisor/model/product"}
Ext.define('Shopware.apps.Advisor.model.Product', {
    extend: 'Ext.data.Model',

    fields: [
        //{block name="backend/advisor/model/product/properties"}{/block}
        { name: 'id', type: 'int', useNull: true },
        { name: 'number', type: 'string' },
        { name: 'name', type: 'string' },
        { name: 'stock', type: 'int' },
        { name: 'cheapestPrice' }
    ]
});
//{/block}
