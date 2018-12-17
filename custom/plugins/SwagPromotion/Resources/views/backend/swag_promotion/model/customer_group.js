//{block name="backend/swag_promotion/model/customer_group"}
Ext.define('Shopware.apps.SwagPromotion.model.CustomerGroup', {

    extend: 'Shopware.apps.Base.model.CustomerGroup',

    configure: function () {
        return {
            related: 'Shopware.apps.SwagPromotion.view.detail.CustomerGroup'
        }
    }
});
//{/block}
