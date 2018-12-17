//

//{namespace name="backend/advisor/main"}
//{block name="backend/advisor/model/stream"}
Ext.define('Shopware.apps.Advisor.model.Stream', {
    extend: 'Shopware.data.Model',

    fields: [
        //{block name="backend/advisor/model/stream/properties"}{/block}
        { name: 'id', type: 'int', useNull: true },
        { name: 'name', type: 'string', useNull: false },
        { name: 'description', type: 'string', useNull: false },
        { name: 'type', type: 'int', defaultValue: 1 },
        { name: 'sorting' },
        { name: 'conditions', useNull: false }
    ]
});
//{/block}