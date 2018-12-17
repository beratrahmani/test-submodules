// {block name="backend/article/model/bundle/bundle"}
Ext.define('Shopware.apps.Article.model.bundle.Bundle', {
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
        { name: 'showName', type: 'boolean' },
        { name: 'type', type: 'int' },
        { name: 'articleId', type: 'int', useNull: true },
        { name: 'active', type: 'boolean' },
        { name: 'discountType', type: 'string' },
        { name: 'description', type: 'string' },
        { name: 'taxId', type: 'int', useNull: true },
        { name: 'number', type: 'string' },
        { name: 'position', type: 'int', defaultValue: 0 },
        { name: 'displayGlobal', type: 'boolean', defaultValue: false },
        { name: 'displayDelivery', type: 'int', defaultValue: 1 },
        { name: 'limited', type: 'boolean', defaultValue: false },
        { name: 'quantity', type: 'int' },
        { name: 'validFrom', type: 'date', useNull: true, dateFormat: 'd.m.Y' },
        { name: 'validTo', type: 'date', useNull: true, dateFormat: 'd.m.Y' },
        { name: 'created', type: 'date', useNull: true },
        { name: 'sells', type: 'int' }
    ],

    associations: [
        {
            type: 'hasMany',
            model: 'Shopware.apps.Base.model.CustomerGroup',
            name: 'getCustomerGroups',
            associationKey: 'customerGroups'
        },
        {
            type: 'hasMany',
            model: 'Shopware.apps.Article.model.bundle.Article',
            name: 'getArticles',
            associationKey: 'articles'
        },
        {
            type: 'hasMany',
            model: 'Shopware.apps.Article.model.Detail',
            name: 'getLimitedDetails',
            associationKey: 'limitedDetails'
        },
        {
            type: 'hasMany',
            model: 'Shopware.apps.Article.model.Price',
            name: 'getPrices',
            associationKey: 'prices'
        }
    ],

    /**
     * Configure the data communication
     * @object
     */
    proxy: {
        /**
         * Set proxy type to ajax
         * @string
         */
        type: 'ajax',

        /**
         * Configure the url mapping for the different
         * store operations based on
         * @object
         */
        api: {
            create: '{url controller="Bundle" action="createBundle"}',
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
