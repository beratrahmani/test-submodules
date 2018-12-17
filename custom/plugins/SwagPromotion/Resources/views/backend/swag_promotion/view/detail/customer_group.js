
//{namespace name="backend/swag_promotion/main"}
//{block name="backend/promotion/view/detail/customer_group"}
Ext.define('Shopware.apps.SwagPromotion.view.detail.CustomerGroup', {
    extend: 'Shopware.grid.Association',
    alias: 'widget.swag-promotion-customer-group',
    height: 150,
    title: '{s namespace="backend/swag_promotion/snippets" name="titleCustomerGroups"}Customer groups{/s}',

    configure: function () {
        return {
            controller: 'SwagPromotion',
            columns: {
                name: {}
            }
        };
    },

    /**
     * @override
     */
    createSearchCombo: function (store) {
        var me = this,
            combo = me.callParent(arguments);

        combo.editable = false;

        return combo;
    }
});
//{/block}
