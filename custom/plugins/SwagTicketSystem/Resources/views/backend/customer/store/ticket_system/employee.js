
//{namespace name="backend/customer/view/main"}
//{block name="backend/customer/ticket-system/store/employee"}
Ext.define('Shopware.apps.Customer.store.ticket_system.Employee', {

    /**
     * Extend for the standard ExtJS 4
     * @string
     */
    extend:'Ext.data.Store',
    /**
     * Define the used model for this store
     * @string
     */
    model:'Shopware.apps.Customer.model.ticket_system.Employee',
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
    pageSize:5,
    /**
     * to upload all selected items in one request
     * @boolean
     */
    batch:true,

    /**
     * A config object containing one or more event handlers to be added to this object during initialization
     * @object
     */
    listeners: {
        /**
         * @event load
         * @param [object] store - Ext.data.Store
         * @return void
         */
        load: function(store) {
            store.insert(0, Ext.create('Shopware.apps.Customer.model.ticket_system.Employee', {
                id: 0,
                name: '{s name=ticket/no_assignment}Keine Zuweisung{/s}'
            }));
        }
    },

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

        api: {
            read: '{url controller=Ticket action="getEmployeeList"}'
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