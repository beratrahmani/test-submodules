
//{namespace name=backend/ticket/main}
//{block name="backend/ticket/store/status_combo"}
Ext.define('Shopware.apps.Ticket.store.StatusCombo', {
    /**
     * Extend for the standard ExtJS 4
     * @string
     */
    extend:'Shopware.apps.Ticket.store.Status',

    /**
     * A config object containing one or more event handlers to be added to this object during initialization
     * @object
     */
    listeners: {
        /**
         * Fires whenever records have been prefetched
         * used to add some default values to the combobox
         *
         * @event load
         * @param [object] store - Ext.data.Store
         * @return void
         */
        load: function(store) {
            store.insert(0, Ext.create('Shopware.apps.Ticket.model.Status', {
                id: 0,
                description: '{s name=status_store/all_assigment}All{/s}'
            }));
        }
    }
});
//{/block}

