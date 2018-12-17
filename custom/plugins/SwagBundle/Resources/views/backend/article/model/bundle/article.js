// {block name="backend/article/model/bundle/article"}
Ext.define('Shopware.apps.Article.model.bundle.Article', {
    /**
     * Extends the standard Ext Model
     * @string
     */
    extend: 'Ext.data.Model',

    /**
     * The fields used for this model
     * @array
     */
    fields: [
        // {block name="backend/article/model/bundle/article/fields"}{/block}
        { name: 'id', type: 'int', useNull: true },
        { name: 'articleDetailId', type: 'string' },
        { name: 'quantity', type: 'int' },
        { name: 'bundleId', type: 'int', useNull: true },
        { name: 'configurable', type: 'boolean' },
        { name: 'position', type: 'int' }
    ],

    associations: [
        { type: 'hasMany', model: 'Shopware.apps.Article.model.Detail', name: 'getArticleDetail', associationKey: 'articleDetail' }
    ]
});
// {/block}
