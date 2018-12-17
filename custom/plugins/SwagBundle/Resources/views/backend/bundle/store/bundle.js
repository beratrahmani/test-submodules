// {block name="backend/bundle/store/bundle"}
Ext.define('Shopware.apps.Bundle.store.Bundle', {

    /**
     * Define that this component is an extension of the Ext.data.Store
     */
    extend: 'Ext.data.Store',

    /**
     * Define how much rows loaded with one request
     */
    pageSize: 15,

    /**
     * Auto load the store after the component
     * is initialized
     * @boolean
     */
    autoLoad: false,

    /**
     * Enable remote sorting
     */
    remoteSort: true,

    /**
     * Enable remote filtering
     */
    remoteFilter: true,
    /**
     * Define the used model for this store
     * @string
     */
    model: 'Shopware.apps.Bundle.model.Bundle'
});
// {/block}
