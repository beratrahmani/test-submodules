// {block name="backend/article/view/detail/window"}
// {$smarty.block.parent}
// {namespace name="backend/bundle/article/view/main"}
Ext.define('Shopware.apps.Article.view.detail.BundleWindow', {
    override: 'Shopware.apps.Article.view.detail.Window',

    /**
     * @Override
     * Creates the main tab panel which displays the different tabs for the product sections.
     *
     * @return { Ext.tab.Panel }
     */
    createMainTabPanel: function() {
        var me = this,
            result;

        result = me.callParent(arguments);

        me.registerAdditionalTab({
            title: 'Bundle',
            contentFn: me.createBundleTab,
            articleChangeFn: me.productChange,
            tabConfig: {
                layout: {
                    type: 'hbox',
                    align: 'stretch'
                },
                listeners: {
                    activate: function() {
                        me.fireEvent('bundleTabActivated', me);
                    },
                    deactivate: function() {
                        me.fireEvent('bundleTabDeactivated', me);
                    }
                }
            },
            scope: me
        });

        return result;
    },

    /**
     * Callback function called when the product changed (splitview).
     *
     * @param product
     */
    productChange: function(product) {
        var me = this;

        me.bundleListStore.getProxy().extraParams.articleId = product.get('id');
        me.bundleListStore.load();
    },

    /**
     * @Override
     * Creates the toolbar with the save and cancel button.
     *
     * @return { Ext.toolbar.Toolbar }
     */
    createToolbar: function() {
        var me = this,
            result;

        result = me.callParent(arguments);

        result.add(me.createBundleSaveButton());

        return result;
    },

    /**
     * Creates the save button for the bundle tab.
     *
     * @return { Ext.button.Button }
     */
    createBundleSaveButton: function() {
        var me = this;

        me.bundleSaveButton = Ext.create('Ext.button.Button', {
            cls: 'primary',
            name: 'save-bundle-button',
            text: '{s name=window/save_bundle_button}Save bundle{/s}',
            hidden: true,
            handler: function() {
                me.fireEvent('saveBundle', me);
            }
        });
        return me.bundleSaveButton;
    },

    /**
     * Creates the tab container for the bundle plugin.
     */
    createBundleTab: function(product, stores, eOpts) {
        var me = this,
            tab = eOpts.tab;

        me.bundleListStore = Ext.create('Shopware.apps.Article.store.bundle.List');
        me.bundleListStore.getProxy().extraParams.productId = product.get('id');

        tab.add(me.createBundleComponents());
        tab.setDisabled(product.get('id') === null);
    },

    /**
     * Creates all components for the bundle tab which displayed in the product detail window.
     *
     * @return { Array }
     */
    createBundleComponents: function() {
        var me = this,
            items = [];

        items.push(me.createBundleList());
        items.push(me.createDetailContainer());

        return items;
    },

    /**
     * Creates the listing component which displays all bundles of the current product
     * @return { Shopware.apps.Article.view.bundle.List }
     */
    createBundleList: function() {
        var me = this;

        return Ext.create('Shopware.apps.Article.view.bundle.List', {
            width: 265,
            store: me.bundleListStore
        });
    },

    /**
     * Creates the bundle detail container.
     * The detail container contains the bundle configuration panel and
     * an additional tab panel for the associated data.
     *
     * @return { Ext.container.Container }
     */
    createDetailContainer: function() {
        var me = this;

        return Ext.create('Ext.container.Container', {
            layout: {
                type: 'vbox',
                align: 'stretch'
            },
            disabled: true,
            cls: 'shopware-form',
            name: 'bundle-detail-container',
            flex: 1,
            items: me.createDetailContainerItems()
        });
    },

    /**
     * Creates the elements for the detail container.
     * The detail container contains the bundle configuration panel and
     * an additional tab panel for the associated data.
     *
     * @return { Array }
     */
    createDetailContainerItems: function() {
        var me = this,
            items = [];

        items.push(me.createBundleConfiguration());
        items.push(me.createBundleTabPanel());

        return items;
    },

    /**
     * Creates the bundle configuration panel.
     * The configuration panel contains the data of the s_articles_bundles like the bundle type, discount type, etc.
     *
     * @return { Shopware.apps.Article.view.bundle.Configuration }
     */
    createBundleConfiguration: function() {
        var me = this;

        return Ext.create('Shopware.apps.Article.view.bundle.Configuration', {
            height: 300,
            minHeight: 300,
            taxStore: me.taxStore
        });
    },

    /**
     * Creates the tab panel for the bundle associated data.
     * This tab panel contains the tab for the bundle products, prices, allowed customer groups, etc.
     *
     * @return { Ext.tab.Panel }
     */
    createBundleTabPanel: function() {
        var me = this;

        return Ext.create('Ext.tab.Panel', {
            plain: true,
            style: 'background: none repeat scroll 0 0 #F0F2F4;',
            items: me.createBundleTabPanelItems(),
            flex: 1,
            minHeight: 240
        });
    },

    /**
     * Creates all tabs for the bundle tab panel.
     *
     * @return { Array }
     */
    createBundleTabPanelItems: function() {
        var me = this,
            items = [];

        items.push(me.createProductTabPanelItem());
        items.push(me.createPriceTabPanelItem());
        items.push(me.createCustomerGroupTabPanelItem());
        items.push(me.createLimitedDetailTabPanelItem());
        items.push(me.createDescriptionTabPanelItem());

        return items;
    },

    /**
     * Creates the tab panel item for the bundle product listing.
     *
     * @return { Shopware.apps.Article.view.bundle.tabs.Article }
     */
    createProductTabPanelItem: function() {
        var me = this;

        return Ext.create('Shopware.apps.Article.view.bundle.tabs.Article', {
            customerGroupStore: me.customerGroupStore,
            article: me.subApplication.article,
            bundleController: me.subApplication.getController('Bundle')
        });
    },

    /**
     * Creates the tab panel item for the bundle prices.
     *
     * @return { Shopware.apps.Article.view.bundle.tabs.Price }
     */
    createPriceTabPanelItem: function() {
        var me = this;

        return Ext.create('Shopware.apps.Article.view.bundle.tabs.Price', {
            customerGroupStore: me.customerGroupStore,
            article: me.subApplication.article,
            bundleController: me.subApplication.getController('Bundle')
        });
    },

    /**
     * Creates the tab panel item for the bundle customer group.
     *
     * @return { Shopware.apps.Article.view.bundle.tabs.CustomerGroup }
     */
    createCustomerGroupTabPanelItem: function() {
        var me = this;

        return Ext.create('Shopware.apps.Article.view.bundle.tabs.CustomerGroup', {
            customerGroupStore: me.customerGroupStore,
            article: me.subApplication.article
        });
    },

    /**
     * Creates the tab panel item for the bundle stints.
     *
     * @return { Shopware.apps.Article.view.bundle.tabs.LimitedDetail }
     */
    createLimitedDetailTabPanelItem: function() {
        var me = this;

        return Ext.create('Shopware.apps.Article.view.bundle.tabs.LimitedDetail', {
            product: me.subApplication.article
        });
    },

    /**
     * Creates the tab panel item for the bundle description.
     *
     * @return { Shopware.apps.Article.view.bundle.tabs.Description }
     */
    createDescriptionTabPanelItem: function() {
        return Ext.create('Shopware.apps.Article.view.bundle.tabs.Description');
    }
});
// {/block}
