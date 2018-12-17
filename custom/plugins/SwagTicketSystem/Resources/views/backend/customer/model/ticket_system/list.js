
//{block name="backend/customer/swag_ticket_system/model/list"}
Ext.define('Shopware.apps.Customer.model.ticket_system.List', {
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
        //{block name="backend/ticket/model/list/fields"}{/block}

        { name: 'id', type: 'int' },
        { name: 'uniqueId', type: 'string' },
        { name: 'userId', type: 'int' },
        { name: 'employeeId', type: 'int' },
        { name: 'ticketTypeId', type: 'int' },
        { name: 'statusId', type: 'int' },
        { name: 'email', type: 'string' },
        { name: 'subject', type: 'string' },
        { name: 'additional', type: 'string' },
        { name: 'message', type: 'string' },
        { name: 'receipt', type: 'date' },
        { name: 'lastContact', type: 'date' },
        { name: 'isoCode', type: 'string' },
        { name: 'contact', type: 'string' },
        { name: 'company', type: 'string' },
        { name: 'status', type: 'string' },
        { name: 'statusColor', type: 'string' },
        { name: 'ticketTypeName', type: 'string' },
        { name: 'ticketTypeColor', type: 'string' },
		{ name: 'shopId', type: 'int' }

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
            create: '{url action="createTicket"}',
            read: '{url action="getList"}',
            update: '{url action="updateTicket"}',
            destroy: '{url controller= Ticket action="destroyTicket"}'
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