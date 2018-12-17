// {namespace name=backend/abo_commerce/product_stream/view/main}
Ext.define('Shopware.apps.Config.AboCommerceFacet', {

    getClass: function() {
        return 'SwagAboCommerce\\Bundle\\SearchBundle\\Facet\\AboCommerceFacet';
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
