
// {block name="backend/abo_commerce/model/article"}
Ext.define('Shopware.apps.AboCommerce.model.Article', {

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
        { name: 'id', type: 'int', useNull: true },

        { name: 'articleId', type: 'int' },
        { name: 'articleName', type: 'string' },
        { name: 'articleNumber', type: 'articleNumber' },
        { name: 'isActive', type: 'boolean' },
        { name: 'isExclusive', type: 'boolean' }
    ],

    /**
     * Configure the data communication
     * @object
     */
    proxy: {
        type: 'ajax',

        /**
         * Configure the url mapping for the different
         * store operations based on
         * @object
         */
        api: {
            read: '{url controller="AboCommerce" action="getArticles"}'
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
