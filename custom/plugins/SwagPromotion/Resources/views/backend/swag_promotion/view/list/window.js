
//{namespace name="backend/swag_promotion/main"}
//{block name="backend/swag_promotion/view/list//list"}
Ext.define('Shopware.apps.SwagPromotion.view.list.Window', {
    extend: 'Shopware.window.Listing',
    alias: 'widget.swag-promotion-list-window',
    height: 450,
    title : '{s name=window_title}Promotion list{/s}',

    configure: function() {
        return {
            listingGrid: 'Shopware.apps.SwagPromotion.view.list.List',
            listingStore: 'Shopware.apps.SwagPromotion.store.Main'
        };
    }
});
//{/block}
