
//{block name="backend/swag_rule_tree/model/rule"}
Ext.define('Shopware.apps.SwagTreeRule.model.Rule', {
    extend: 'Ext.data.Model',

    fields: [
        //{block name="backend/swag_rule_tree/model/rule/fields"}{/block}
        { name: 'id', type: 'string', mapping: 'id' },
        { name: 'text', type: 'string', mapping: 'text' },
        { name: 'index', type: 'int', mapping: 'index' },
        { name: 'adapter', type: 'string', mapping: 'adapter' },
        { name: 'parentKey', type: 'string', mapping: 'parentKey' },
        { name: 'type', type: 'string', mapping: 'type' },
        { name: 'swColumn', type: 'string', mapping: 'swColumn' },
        { name: 'inIteration', type: 'boolean', mapping: 'inIteration' }
    ]
});
//{/block}
Ext.data.NodeInterface.decorate('Shopware.apps.SwagTreeRule.model.Rule');
