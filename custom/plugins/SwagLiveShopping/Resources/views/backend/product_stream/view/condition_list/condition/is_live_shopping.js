
// {namespace name=backend/live_shopping/product_stream/view/main}
// {block name="backend/product_stream/view/condition_list/condition/is_live_shopping"}
Ext.define('Shopware.apps.ProductStream.view.condition_list.condition.IsLiveShopping', {
    extend: 'Shopware.apps.ProductStream.view.condition_list.condition.AbstractCondition',

    getName: function() {
        return 'SwagLiveShopping\\Bundle\\SearchBundle\\Condition\\LiveShoppingCondition';
    },

    getLabel: function() {
        return '{s name="is_liveshopping_condition"}Is liveshopping article{/s}';
    },

    isSingleton: function() {
        return true;
    },

    create: function(callback) {
        callback(this.createField());
    },

    load: function(key) {
        if (key !== this.getName()) {
            return;
        }
        return this.createField();
    },

    createField: function() {
        var me = this;

        return Ext.create('Ext.container.Container', {
            getName: function() {
                return 'condition.' + me.getName();
            },
            items: [{
                xtype: 'displayfield',
                value: '{s name="is_liveshopping/display_text"}Only liveshopping articles will be displayed.{/s}'
            }, {
                xtype: 'numberfield',
                name: 'condition.' + me.getName(),
                hidden: true,
                value: 1
            }]
        });
    }
});
// {/block}
