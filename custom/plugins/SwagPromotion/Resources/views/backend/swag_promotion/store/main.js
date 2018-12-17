//{block name="backend/swag_promotion/store/main"}
Ext.define('Shopware.apps.SwagPromotion.store.Main', {
    extend: 'Shopware.store.Listing',

    configure: function () {
        return {
            controller: 'SwagPromotion'
        };
    },
    model: 'Shopware.apps.SwagPromotion.model.Main'
});
//{/block}
