// {block name="backend/bundle/model/bundle"}
Ext.define('Shopware.apps.Bundle.model.Bundle', {
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
        // {block name="backend/article/model/article/fields"}{/block}
        { name: 'id', type: 'int', useNull: true },
        { name: 'name', type: 'string' },
        { name: 'type', type: 'int' },
        { name: 'articleId', type: 'int', useNull: true },
        { name: 'active', type: 'boolean' },
        { name: 'discountType', type: 'string' },
        { name: 'taxId', type: 'int', useNull: true },
        { name: 'number', type: 'string' },
        { name: 'limited', type: 'boolean' },
        { name: 'quantity', type: 'int' },
        { name: 'validFrom', type: 'date', useNull: true },
        { name: 'validTo', type: 'date', useNull: true },
        { name: 'created', type: 'date', useNull: true },
        { name: 'sells', type: 'int' }
    ],

    associations: [
        { type: 'hasMany', model: 'Shopware.apps.Base.model.CustomerGroup', name: 'getCustomerGroups', associationKey: 'customerGroups' },
        { type: 'hasMany', model: 'Shopware.apps.Bundle.model.Article', name: 'getArticles', associationKey: 'articles' },
        { type: 'hasMany', model: 'Shopware.apps.Base.model.Article', name: 'getArticle', associationKey: 'article' },
        { type: 'hasMany', model: 'Shopware.apps.Bundle.model.Detail', name: 'getLimitedDetails', associationKey: 'limitedDetails' },
        { type: 'hasMany', model: 'Shopware.apps.Bundle.model.Price', name: 'getPrices', associationKey: 'prices' },
        { type: 'hasMany', model: 'Shopware.apps.Bundle.model.Group', name: 'getGroups', associationKey: 'groups' }
    ],

    proxy: {
        type: 'ajax',

        api: {
            read: '{url controller="Bundle" action="getFullList"}',
            update: '{url controller="Bundle" action="updateBundle"}'
        },
        /**
         * Configure the data reader
         * @object
         */
        reader: {
            type: 'json',
            root: 'data',
            totalProperty: 'total'
        }
    }
});
// {/block}
