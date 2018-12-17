
//{namespace name="backend/swag_promotion/main"}
//{block name="backend/promotion/view/detail/free_goods_article"}
Ext.define('Shopware.apps.SwagPromotion.view.detail.FreeGoodsArticle', {
    extend: 'Shopware.grid.Association',
    alias: 'widget.swag-promotion-free-goods',
    height: 150,
    title: '{s namespace="backend/swag_promotion/snippets" name="freeGoods"}freeGoods{/s}',

    configure: function () {
        return {
            controller: 'SwagPromotion',
            columns: {
                name: {}
            }
        };
    }
});
//{/block}
