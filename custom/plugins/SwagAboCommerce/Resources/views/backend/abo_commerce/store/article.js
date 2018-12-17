
// {block name="backend/abo_commerce/store/article"}
Ext.define('Shopware.apps.AboCommerce.store.Article', {

    /**
     * Define that this component is an extension of the Ext.data.Store
     */
    extend: 'Ext.data.Store',

    /**
     * Auto load the store after the component
     * is initialized
     * @boolean
     */
    autoLoad: true,

    remoteSort: true,

    remoteFilter: true,

    /**
     * Define the used model for this store
     * @string
     */
    model: 'Shopware.apps.AboCommerce.model.Article'
});
// {/block}
