
//{block name="backend/swag_rule_tree/model/tree"}
Ext.define('Shopware.apps.SwagRuleTree.model.Tree', {
    extend: 'Shopware.data.Model',

    configure: function() {
        return {
            field: 'Shopware.apps.SwagRuleTree.view.TreeField'
        };
    },

    fields: [
        //{block name="backend/swag_rule_tree/model/tree/fields"}{/block}
    ]
});
//{/block}
