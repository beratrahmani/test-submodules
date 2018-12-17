
//{block name="backend/ticket/model/file"}
Ext.define('Shopware.apps.Ticket.model.Attachment', {
    /**
     * Extends the standard Ext Model
     * @string
     */
    extend: 'Ext.data.Model',

    idProperty : 'hash',

    /**
     * The fields used for this model
     * @array
     */
    fields: [
		//{block name="backend/ticket/model/file/fields"}{/block}
        { name: 'hash', type: 'string' },
        { name: 'attachment', type: 'string' },
        { name: 'location', type: 'string' }
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
            read: '{url controller="Ticket" action="getAttachments"}',
            destroy: '{url controller="Ticket" action="deleteAttachment"}'
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
