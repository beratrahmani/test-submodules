
//{block name="backend/swag_promotion/application"}
Ext.define('Shopware.apps.SwagPromotion', {
    extend: 'Enlight.app.SubApplication',

    name: 'Shopware.apps.SwagPromotion',

    loadPath: '{url action=load}',
    bulkLoad: true,

    controllers: [
        'Main'
    ],

    views: [
        'list.Window',
        'list.List',

        'detail.Container',
        'detail.CustomerGroup',
        'detail.PromotionAssociation',
        'detail.Rules',
        'detail.Shop',
        'detail.Window',
        'detail.Statistics',
        'detail.FreeGoodsArticle'
    ],

    models: [
        'Main',
        'Voucher',
        'CustomerGroup',
        'Shop',
        'Rules',
        'PromotionAssociation',
        'FreeGoodsArticle'
    ],

    stores: [
        'Main'
    ],

    launch: function () {
        return this.getController('Main').mainWindow;
    }
});
//{/block}
