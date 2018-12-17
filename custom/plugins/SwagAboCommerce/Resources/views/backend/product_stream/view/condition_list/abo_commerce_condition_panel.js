
// {block name="backend/product_stream/view/condition_list/condition_panel"}
// {$smarty.block.parent}

// {include file="backend/product_stream/view/condition_list/condition/is_abo.js"}

Ext.define('Shopware.apps.ProductStream.view.condition_list.AboCommerceConditionPanel', {
    override: 'Shopware.apps.ProductStream.view.condition_list.ConditionPanel',

    /**
     * @returns { Shopware.apps.ProductStream.view.condition_list.condition.AbstractCondition }
     */
    createConditionHandlers: function () {
        var items = this.callParent(arguments);
        items.push(Ext.create('Shopware.apps.ProductStream.view.condition_list.condition.IsAbo'));
        return items;
    }
});
// {/block}
