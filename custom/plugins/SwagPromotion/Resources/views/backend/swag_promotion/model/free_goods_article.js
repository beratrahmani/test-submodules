//{block name="backend/swag_promotion/model/free_goods_article"}
Ext.define('Shopware.apps.SwagPromotion.model.FreeGoodsArticle', {

    extend: 'Shopware.data.Model',

    configure: function () {
        return {
            related: 'Shopware.apps.SwagPromotion.view.detail.FreeGoodsArticle'

        }
    },

    fields: [
        //{block name="backend/swag_promotion/model/free_goods_article/fields"}{/block}
        { name: 'id', type: 'int', useNull: true },
        { name: 'name', type: 'string', useNull: true }
    ]
});
//{/block}
