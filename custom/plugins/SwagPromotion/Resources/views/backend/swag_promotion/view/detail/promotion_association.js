
//{namespace name="backend/swag_promotion/main"}
//{block name="backend/promotion/view/detail/promotion_association"}
Ext.define('Shopware.apps.SwagPromotion.view.detail.PromotionAssociation', {
    extend: 'Shopware.grid.Association',
    alias: 'widget.swag-promotion-association',
    height: 150,
    title: 'blacklist-temp',

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
