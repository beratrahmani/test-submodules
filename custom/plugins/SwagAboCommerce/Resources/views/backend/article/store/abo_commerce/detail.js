
// {block name="backend/article/store/abo_commerce/detail"}
Ext.define('Shopware.apps.Article.store.abo_commerce.Detail', {

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
    /**
     * Define the used model for this store
     * @string
     */
    model: 'Shopware.apps.Article.model.abo_commerce.AboCommerce'
});
// {/block}
