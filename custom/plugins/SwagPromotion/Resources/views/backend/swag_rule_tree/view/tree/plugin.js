
//{block name="backend/swag_rule_tree/view/tree/plugin"}
Ext.define('Shopware.apps.SwagRuleTree.view.tree.Plugin', {
    extend: 'Ext.tree.plugin.TreeViewDragDrop',

    alias: 'plugin.swagruletree',

    onViewRender: function () {
        var me = this;

        me.callParent(arguments);

    }
});
//{/block}
