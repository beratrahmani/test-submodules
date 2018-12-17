
//{block name="backend/ticket/model/file"}
Ext.define('Shopware.apps.Ticket.model.File', {
    /**
     * Extends the standard Ext Model
     * @string
     */
    extend: 'Ext.data.Model',

    idProperty : 'id',

    /**
     * The fields used for this model
     * @array
     */
    fields: [
		//{block name="backend/ticket/model/file/fields"}{/block}
        { name: 'id', type: 'int' },
        { name: 'ticketId', type: 'int' },
        { name: 'hash', type: 'string' },
        { name: 'attachment', type: 'string' }
    ],
    
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
            read: '{url controller="Ticket" action="getFiles"}',
            destroy: '{url controller="Ticket" action="deleteFile"}'
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
//{/block}
