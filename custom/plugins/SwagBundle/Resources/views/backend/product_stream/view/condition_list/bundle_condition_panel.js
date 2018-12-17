// {block name="backend/product_stream/view/condition_list/condition_panel"}
// {$smarty.block.parent}
// {include file="backend/product_stream/view/condition_list/condition/is_bundle.js"}
Ext.define('Shopware.apps.ProductStream.view.condition_list.BundleConditionPanel', {
    override: 'Shopware.apps.ProductStream.view.condition_list.ConditionPanel',

    /**
     * @returns { array }
     */
    createConditionHandlers: function () {
        var items = this.callParent(arguments);
        items.push(Ext.create('Shopware.apps.ProductStream.view.condition_list.condition.IsBundle'));
        return items;
    }
});
// {/block}
