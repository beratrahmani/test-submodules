//

//{namespace name="backend/advisor/main"}
//{block name="backend/advisor/model/answer"}
Ext.define('Shopware.apps.Advisor.model.Answer', {
    extend: 'Ext.data.Model',

    fields: [
        //{block name="backend/advisor/model/answer/properties"}{/block}
        { name: 'id', type: 'int', useNull: true, defaultValue: null },
        { name: 'order', type: 'int', useNull: true, defaultValue: null },
        { name: 'key', type: 'string' },
        { name: 'value', type: 'string' },
        { name: 'answer', type: 'string' },
        { name: 'cssClass', type: 'string' },

        { name: 'mediaId', type: 'int', useNull: true, defaultValue: null },
        { name: 'thumbnail', type: 'string' },

        { name: 'rowId', type: 'string', useNull: true, defaultValue: null },
        { name: 'columnId', type: 'string', useNull: true, defaultValue: null },
        { name: 'targetId', type: 'string' }
    ]
});
//{/block}