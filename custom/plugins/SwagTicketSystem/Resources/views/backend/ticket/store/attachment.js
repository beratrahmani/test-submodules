
//{namespace name=backend/ticket/main}
//{block name="backend/ticket/store/file"}
Ext.define('Shopware.apps.Ticket.store.Attachment', {
    /**
     * Extend for the standard ExtJS 4
     * @string
     */
    extend: 'Ext.data.Store',
    /**
     * Define the used model for this store
     * @string
     */
    model: 'Shopware.apps.Ticket.model.Attachment',
    /**
     * Enable remote sort.
     * @boolean
     */
    remoteSort: true,
    /**
     * Enable remote filtering
     * @boolean
     */
    remoteFilter: true,
    /**
     * Amount of data loaded at once
     * @integer
     */
    pageSize: 25
    /**
     * Configure the data communication
     * @object
     */
});
//{/block}
