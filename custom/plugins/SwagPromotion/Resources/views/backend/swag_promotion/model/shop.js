//{block name="backend/swag_promotion/model/shop"}
Ext.define('Shopware.apps.SwagPromotion.model.Shop', {

    extend: 'Shopware.apps.Base.model.Shop',

    configure: function () {
        return {
            related: 'Shopware.apps.SwagPromotion.view.detail.Shop'
        }
    }
});
//{/block}
