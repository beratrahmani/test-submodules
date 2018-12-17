
// {namespace name=backend/abo_commerce/product_stream/view/main}
// {block name="backend/product_stream/view/condition_list/condition/is_abo"}
Ext.define('Shopware.apps.ProductStream.view.condition_list.condition.IsAbo', {
    extend: 'Shopware.apps.ProductStream.view.condition_list.condition.AbstractCondition',

    getName: function() {
        return 'SwagAboCommerce\\Bundle\\SearchBundle\\Condition\\AboCommerceCondition';
    },

    getLabel: function() {
        return '{s name="is_abo_condition"}Is article with subscription{/s}';
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
                value: '{s name="is_abo/display_text"}Only articles with subscriptions will be displayed.{/s}'
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
