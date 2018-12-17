// {namespace name=backend/bundle/product_stream/view/main}
// {block name="backend/product_stream/view/condition_list/condition/is_bundle"}
Ext.define('Shopware.apps.ProductStream.view.condition_list.condition.IsBundle', {
    extend: 'Shopware.apps.ProductStream.view.condition_list.condition.AbstractCondition',

    getName: function() {
        return 'SwagBundle\\Bundle\\SearchBundle\\Condition\\BundleCondition';
    },

    getLabel: function() {
        return '{s name="is_bundle_condition"}Is a bundle product{/s}';
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
                value: '{s name="is_bundle/display_text"}Only bundle products will be displayed.{/s}'
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
