
// {namespace name=backend/live_shopping/product_stream/view/main}

Ext.define('Shopware.apps.Config.LiveShoppingFacet', {

    getClass: function() {
        return 'SwagLiveShopping\\Bundle\\SearchBundle\\Facet\\LiveShoppingFacet';
    },

    createItems: function () {
        return [{
            xtype: 'textfield',
            name: 'label',
            labelWidth: 150,
            translatable: true,
            fieldLabel: '{s name="facet_label"}{/s}'
        }];
    }
});
