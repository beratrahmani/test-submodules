
//{block name="backend/ticket/store/customer"}
Ext.define('Shopware.apps.Ticket.store.Customer', {
    /**
     * Extend for the standard ExtJS 4
     * @string
     */
    extend:'Ext.data.Store',
    /**
     * Define the used model for this store
     * @string
     */
    model:'Shopware.apps.Ticket.model.Customer',
    /**
     * Enable remote sort.
     * @boolean
     */
    remoteSort:true,
    /**
     * Enable remote filtering
     * @boolean
     */
    remoteFilter:true,
    /**
     * Amount of data loaded at once
     * @integer
     */
    pageSize:25,
    /**
     * to upload all selected items in one request
     * @boolean
     */
    batch:true,
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
         url: '{url controller="Ticket" action="getCustomerList"}',

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
    
    listeners: {
        load: function (store) {
            store.each(function (record) {
                var name = record.raw.lastname + ' ' + record.raw.firstname;
                record.set('name', name);
            });
        }
    }
});
//{/block}

