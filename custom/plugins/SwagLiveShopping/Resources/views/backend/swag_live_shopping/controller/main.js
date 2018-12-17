//

// {namespace name="backend/live_shopping/live_shopping/view/main"}
// {block name="backend/swag_live_shopping/controller/main"}
Ext.define('Shopware.apps.SwagLiveShopping.controller.Main', {
    extend: 'Enlight.app.Controller',
    mainWindow: null,

    init: function () {
        var me = this;

        me.createMainWindow();

        me.control({
            'swag-live-shopping-list': {
                openProduct: me.onOpenProduct
            }
        });

        me.callParent(arguments);
    },

    /**
     * Create and show the main window
     */
    createMainWindow: function () {
        var me = this;

        me.mainWindow = me.getView('listing.Window').create().show();
    },

    /**
     * @param productId
     */
    onOpenProduct: function (productId) {
        var subApplication = {
            name: 'Shopware.apps.Article',
            action: 'detail',
            params: {
                articleId: productId
            }
        };

        Shopware.app.Application.addSubApplication(subApplication);
    }
});
// {/block}
