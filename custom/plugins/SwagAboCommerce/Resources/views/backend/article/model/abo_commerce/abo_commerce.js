
// {block name="backend/article/model/abo_commerce/abo_commerce"}
Ext.define('Shopware.apps.Article.model.abo_commerce.AboCommerce', {

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

        { name: 'active', type: 'boolean' },
        { name: 'exclusive', type: 'boolean' },

        { name: 'ordernumber', type: 'string' },
        { name: 'description', type: 'string' },

        { name: 'minDuration', type: 'integer', useNull: true },
        { name: 'maxDuration', type: 'integer', useNull: true },
        { name: 'durationUnit', type: 'string' },

        { name: 'minDeliveryInterval', type: 'integer' },
        { name: 'maxDeliveryInterval', type: 'integer' },
        { name: 'deliveryIntervalUnit', type: 'string' },

        { name: 'endlessSubscription', type: 'boolean' },
        { name: 'periodOfNoticeInterval', type: 'integer' },
        { name: 'periodOfNoticeUnit' },
        { name: 'directTermination', type: 'boolean'},

        { name: 'limited', type: 'boolean' },
        { name: 'maxUnitsPerWeek', type: 'integer' }
    ],

    associations: [
        { type: 'hasMany', model: 'Shopware.apps.Article.model.abo_commerce.Price', name: 'getPrices', associationKey: 'prices' }
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
            read: '{url controller="AboCommerce" action="getDetail"}',
            create: '{url controller="AboCommerce" action="createAboCommerce"}',
            update: '{url controller="AboCommerce" action="updateAboCommerce"}'
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
