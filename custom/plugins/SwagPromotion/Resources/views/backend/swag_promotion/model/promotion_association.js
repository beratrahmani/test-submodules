//{block name="backend/swag_promotion/model/promotion_association"}
Ext.define('Shopware.apps.SwagPromotion.model.PromotionAssociation', {

    extend: 'Shopware.data.Model',

    configure: function () {
        return {
            related: 'Shopware.apps.SwagPromotion.view.detail.PromotionAssociation'

        }
    },

    fields: [
        //{block name="backend/swag_promotion/model/promotion_association/fields"}{/block}
        { name: 'name', type: 'string', useNull: true }
    ]
});
//{/block}
