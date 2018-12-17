//

// {namespace name="backend/live_shopping/live_shopping/view/main"}
// {block name="backend/swag_live_shopping/store/main"}
Ext.define('Shopware.apps.SwagLiveShopping.store.Main', {
    extend: 'Shopware.store.Listing',

    model: 'Shopware.apps.SwagLiveShopping.model.LiveShopping',

    configure: function () {
        return {
            controller: 'SwagLiveShopping'
        };
    }
});
// {/block}
