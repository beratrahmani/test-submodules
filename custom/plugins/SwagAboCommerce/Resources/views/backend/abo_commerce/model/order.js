
// {block name="backend/abo_commerce/model/order"}
Ext.define('Shopware.apps.AboCommerce.model.Order', {

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

        { name: 'customerId', type: 'int' },
        { name: 'customerMail', type: 'string' },
        { name: 'customerGroup', type: 'string' },

        { name: 'orderId', type: 'int' },
        { name: 'lastOrderId', type: 'int' },

        { name: 'duration', type: 'int', useNull: true },
        { name: 'durationUnit', type: 'string' },

        { name: 'deliveryInterval', type: 'int' },
        { name: 'deliveryIntervalUnit', type: 'string' },

        { name: 'endlessSubscription', type: 'boolean'},
        { name: 'periodOfNoticeInterval', type: 'int', defaultValue: 1 },
        { name: 'periodOfNoticeUnit', type: 'string' },
        { name: 'directTermination', type: 'boolean' },

        { name: 'created', type: 'datetime' },
        { name: 'dueDate', type: 'date' },
        { name: 'recentRun', type: 'datetime' },
        { name: 'lastRun', type: 'date' },

        { name: 'isDue', type: 'boolean' },
        { name: 'isExpired', type: 'boolean' }
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
            read: '{url controller="AboCommerce" action="getOrders"}',
            create: '{url controller="AboCommerce" action="createAboCommerce"}',
            update: '{url controller="AboCommerce" action="updateAboCommerce"}',
            destroy: '{url controller="AboCommerce" action="removeAboCommerce"}'
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
