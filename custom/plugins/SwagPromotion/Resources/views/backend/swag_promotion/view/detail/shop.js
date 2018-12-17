
//{namespace name="backend/swag_promotion/main"}
//{block name="backend/promotion/view/detail/shop"}
Ext.define('Shopware.apps.SwagPromotion.view.detail.Shop', {
    extend: 'Shopware.grid.Association',
    alias: 'widget.swag-promotion-shop',
    height: 150,
    title: '{s namespace="backend/swag_promotion/snippets" name="titleShops"}Shops{/s}',

    configure: function () {
        return {
            controller: 'SwagPromotion',
            columns: {
                name: {}
            }
        };
    },

    createSearchCombo: function () {
        var me = this,
            combo = me.callParent(arguments);

        combo.editable = false;

        return combo;
    }
});
//{/block}
