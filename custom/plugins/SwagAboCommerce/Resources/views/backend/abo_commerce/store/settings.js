
// {block name="backend/abo_commerce/store/settings"}
Ext.define('Shopware.apps.AboCommerce.store.Settings', {

    /**
     * Define that this component is an extension of the Ext.data.Store
     */
    extend: 'Ext.data.Store',

    /**
     * Auto load the store after the component
     * is initialized
     * @boolean
     */
    autoLoad: false,
    remoteSort: true,
    remoteFilter: true,
    /**
     * Define the used model for this store
     * @string
     */
    model: 'Shopware.apps.AboCommerce.model.Settings'
});
// {/block}
