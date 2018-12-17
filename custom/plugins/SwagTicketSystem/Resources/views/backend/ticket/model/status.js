
//{block name="backend/ticket/model/status"}
Ext.define('Shopware.apps.Ticket.model.Status', {
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
		//{block name="backend/ticket/model/status/fields"}{/block}

        { name: 'id', type: 'int' },
        { name: 'description', type: 'string' },
        { name: 'responsible', type: 'int' },
        { name: 'closed', type: 'int' },
        { name: 'color', type: 'string' }
    ],

    /**
     * Configure the data communication
     * @object
     */
    proxy:{
        /**
         * Set proxy type to ajax
         * @string
         */
        type:'ajax',

        /**
         * Configure the url mapping for the different
         * store operations based on
         * @object
         */
        api: {
            create: '{url controller="Ticket" action="createStatus"}',
            read: '{url controller="Ticket" action="getStatusList"}',
            update: '{url controller="Ticket" action="updateStatus"}',
            destroy: '{url controller="Ticket" action="destroyStatus"}'
        },

        /**
         * Configure the data reader
         * @object
         */
        reader:{
            type:'json',
            root:'data',
            totalProperty:'total'
        }
    }
});
//{/block}
