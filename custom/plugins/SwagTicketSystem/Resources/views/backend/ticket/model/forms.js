
//{block name="backend/ticket/model/forms"}
Ext.define('Shopware.apps.Ticket.model.Forms', {
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
        { name:'id', type: 'int' },
        { name: 'name', type: 'string' },
        { name: 'email', type: 'string' },
        { name: 'email_subject', type: 'string' },
        { name: 'email_template', type: 'string' },
        { name: 'isocode', type: 'string' },
        { name: 'text', type: 'string' },
        { name: 'text2', type: 'string' },
        { name: 'ticketTypeid', type: 'int', defaultValue: false },
        { name: 'ext', type: 'string' }
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
            create: '{url controller="Ticket" action="createForm"}',
            read: '{url controller="Ticket" action="getForms"}',
            update: '{url controller="Ticket" action="updateForm"}',
            destroy: '{url controller="Ticket" action="destroyForm"}'
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
    },
    associations: [
       { type: 'hasMany', model: 'Shopware.apps.Ticket.model.FormField', name: 'getFormFields', associationKey: 'fields' },
       { type: 'hasMany', model: 'Shopware.apps.Ticket.model.Mapping', name: 'getFormMapping', associationKey: 'mapping' }
   ]
});
//{/block}
