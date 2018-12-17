
//{block name="backend/ticket/model/submission"}
Ext.define('Shopware.apps.Ticket.model.Submission', {
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
		//{block name="backend/ticket/model/submission/fields"}{/block}
        { name: 'id', type: 'int' },
        { name: 'name', type: 'string' },
        { name: 'description', type: 'string' },
        { name: 'fromMail', type: 'string' },
        { name: 'fromName', type: 'string' },
        { name: 'subject', type: 'string' },
        { name: 'content', type: 'string' },
        { name: 'contentHTML', type: 'string' },
        { name: 'isHTML', type: 'boolean' },
        { name: 'attachment', type: 'string' },
        { name: 'systemDependent', type: 'boolean' },
        { name: 'shopId', type: 'int' },
        { name: 'isoCode', type: 'de' },
        { name: 'shopName', type: 'string' }
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
            create: '{url controller="Ticket" action="createMail"}',
            read: '{url controller="Ticket" action="getMailList"}',
            update: '{url controller="Ticket" action="updateMail"}',
            destroy: '{url controller="Ticket" action="destroyMail"}'
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
