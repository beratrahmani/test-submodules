// {namespace name="backend/bundle/view/main"}
// {block name="backend/bundle/controller/bundle"}
Ext.define('Shopware.apps.Bundle.controller.Bundle', {
    /**
     * The parent class that this class extends.
     * @string
     */
    extend: 'Ext.app.Controller',

    refs: [
        { ref: 'bundleListing', selector: 'bundle-list-window bundle-bundle-list' },
        { ref: 'priceListing', selector: 'bundle-list-window bundle-price-list' },
        { ref: 'productListing', selector: 'bundle-list-window bundle-article-list' },
        { ref: 'groupListing', selector: 'bundle-list-window bundle-group-list' }
    ],

    /**
     * A template method that is called when your application boots.
     * It is called before the Application's launch function is executed
     * so gives a hook point to run any code before your Viewport is created.
     *
     * @return Ext.window.Window
     */
    init: function () {
        var me = this;

        me.mainWindow = me.createMainWindow();

        me.addControls();

        me.callParent(arguments);

        return me.mainWindow;
    },

    /**
     *
     */
    addControls: function() {
        var me = this;

        me.control({
            'bundle-list-window bundle-bundle-list': {
                selectBundle: me.onBundleSelect,
                searchBundle: me.onSearchBundle,
                openArticle: me.onOpenProduct,
                onEdit: me.onEdit
            },
            'bundle-list-window bundle-article-list': {
                openArticle: me.onOpenProduct
            }
        });
    },

    onEdit: function(view, record) {
        var me = this;

        me.getBundleListing().setLoading(true);
        record.save({
            success: function() {
                record.store.load({
                    callback: function() {
                        me.getBundleListing().setLoading(false);
                    }
                });
            },
            failure: function() {
                me.getBundleListing().setLoading(false);
            }
        });
    },
    /**
     * Event listener function of the products, groups and bundle listing.
     * Fired over the action column of the grids.
     * @param productId
     */
    onOpenProduct: function(productId) {
        Shopware.app.Application.addSubApplication({
            name: 'Shopware.apps.Article',
            action: 'detail',
            params: {
                articleId: productId
            }
        });
    },

    /**
     * Event listener function of the search field of the bundle listing.
     * The event fired when the user insert a search string in the search field.
     * @param value
     */
    onSearchBundle: function(value) {
        var me = this;
        var listing = me.getBundleListing();
        var store = listing.getStore();

        store.filters.clear();
        store.currentPage = 1;
        if (value.length > 0) {
            store.filter({ property: 'free', value: '%' + value + '%' });
        } else {
            store.load();
        }
    },

    /**
     * Event listener function of the bundle listing grid.
     * Fired when the user clicks on a grid item.
     * Reloads the associated stores and display the association data in the detail panel.
     */
    onBundleSelect: function(record) {
        var me = this;

        if (!(record instanceof Ext.data.Model)) {
            return false;
        }

        var priceListing = me.getPriceListing(),
            productListing = me.getProductListing(),
            productStore = record.getArticles();

        productStore.groupField = 'groupId';

        priceListing.bundle = record;
        priceListing.reconfigure(record.getPrices(), priceListing.createColumns());

        productListing.reconfigure(productStore);
        productListing.show();
        return true;
    },

    /**
     * Creates and shows the list window of the bundle module.
     * @return Shopware.apps.Bundle.view.list.Window
     */
    createMainWindow: function() {
        var me = this, window;

        window = me.getView('list.Window').create({
            bundleStore: Ext.create('Shopware.apps.Bundle.store.Bundle').load()
        }).show();

        return window;
    }

});
// {/block}
