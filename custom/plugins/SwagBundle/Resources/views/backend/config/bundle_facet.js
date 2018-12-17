
// {namespace name=backend/bundle/product_stream/view/main}

Ext.define('Shopware.apps.Config.BundleFacet', {

    getClass: function() {
        return 'SwagBundle\\Bundle\\SearchBundle\\Facet\\BundleFacet';
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
