
// {namespace name="backend/abo_commerce/article/view/main"}
// {block name="backend/article/view/detail/window"}
// {$smarty.block.parent}
Ext.define('Shopware.apps.Article.view.detail.AboCommerceWindow', {
    override: 'Shopware.apps.Article.view.detail.Window',

    /**
     * @Override
     * Creates the main tab panel which displays the different tabs for the product sections.
     * To extend the tab panel this function can be override.
     *
     * @return Ext.tab.Panel
     */
    createMainTabPanel: function() {
        var me = this, result;

        result = me.callParent(arguments);

        me.registerAdditionalTab({
            title: '{s name=window/tab_abo_commerce}Abo{/s}',
            contentFn: me.createAboCommerceTab,
            articleChangeFn: me.articleChangeAboCommerce,
            tabConfig: {
                layout: {
                    type: 'vbox',
                    align: 'stretch'
                },
                listeners: {
                    activate: function() {
                        me.fireEvent('aboCommerceTabActivated', me);
                    },
                    deactivate: function() {
                        me.fireEvent('aboCommerceTabDeactivated', me);
                    }
                }
            },
            scope: me
        });

        return result;
    },

    /**
     * @Override
     * Creates the toolbar with the save and cancel button.
     */
    createToolbar: function() {
        var me = this, result;

        result = me.callParent(arguments);

        result.add(me.createAboCommerceSaveButton());

        return result;
    },

    /**
     * Creates the save button for the AboCommerce tab.
     * @return Ext.button.Button
     */
    createAboCommerceSaveButton: function() {
        var me = this;

        me.aboCommerceSaveButton = Ext.create('Ext.button.Button', {
            cls: 'primary',
            name: 'save-abo-commerce-button',
            text: '{s name=window/save_abo_commerce_button}Save Abo{/s}',
            hidden: true,
            handler: function() {
                me.fireEvent('saveAboCommerce', me);
            }
        });

        return me.aboCommerceSaveButton;
    },

    /**
     * Callback function called when the product changed (splitview).
     */
    articleChangeAboCommerce: function() {
        var me = this;

        me.aboCommerceDetailStore.getProxy().extraParams.articleId = me.article.get('id');
        me.fireEvent('aboCommerceStoreReloadNeeded');
    },

    /**
     * Creates the tab container for the AboCommerce plugin.
     * @return Ext.container.Container
     */
    createAboCommerceTab: function(article, stores, eOpts) {
        var me = this,
            tab = eOpts.tab;

        var controller = me.subApplication.getController('AboCommerce');

        me.aboCommerceDetailStore = Ext.create('Shopware.apps.Article.store.abo_commerce.Detail');
        me.aboCommerceDetailStore.getProxy().extraParams.articleId = me.article.get('id');

        controller.aboCommerceDetailStore = me.aboCommerceDetailStore;

        tab.add(me.createAboCommerceComponents());
        tab.setDisabled(article.get('id') === null);
    },

    /**
     * Creates the elements for the detail container.
     * The detail container contains the AboCommerce configuration panel and
     * an additional tab panel for the associated data.
     * @return Array
     */
    createAboCommerceComponents: function() {
        var me = this, items = [];

        items.push(me.createAboCommerceConfiguration());
        items.push(me.createAboCommerceTabPanel());

        return items;
    },

    /**
     * Creates the AboCommerce configuration panel.
     * The configuration panel contains the data of the s_articles_lives like the AboCommerce typ, discount typ, etc.
     * @return Shopware.apps.Article.view.abo_commerce.Configuration
     */
    createAboCommerceConfiguration: function() {
        var me = this;

        return Ext.create('Shopware.apps.Article.view.abo_commerce.Configuration', {
            flex: 0,
            article: me.subApplication.article,
            aboCommerceController: me.subApplication.getController('AboCommerce')
        });
    },

    /**
     * Creates the tab panel for the AboCommerce associated data.
     * This tab panel contains the tab for the AboCommerce prices, allowed customer groups, etc.
     * @return Ext.tab.Panel
     */
    createAboCommerceTabPanel: function() {
        var me = this;

        return Ext.create('Ext.tab.Panel', {
            style: 'background: #f0f2f4',
            plain: true,
            items: [ me.createAboCommerceSettingsTabPanelItem(), me.createAboCommercePriceTabPanelItem() ],
            flex: 1
        });
    },

    /**
     * Creates the tab panel item for the AboCommerce prices.
     * @return Shopware.apps.Article.view.abo_commerce.tabs.Price
     */
    createAboCommercePriceTabPanelItem: function() {
        var me = this;

        return Ext.create('Shopware.apps.Article.view.abo_commerce.tabs.Price', {
            customerGroupStore: me.customerGroupStore,
            article: me.subApplication.article,
            aboCommerceController: me.subApplication.getController('AboCommerce')
        });
    },

    /**
     * Creates the tab panel item for the AboCommerce settings.
     * @return Shopware.apps.Article.view.abo_commerce.tabs.Settings
     */
    createAboCommerceSettingsTabPanelItem: function() {
        var me = this;

        return Ext.create('Shopware.apps.Article.view.abo_commerce.tabs.Settings', {
            customerGroupStore: me.customerGroupStore,
            article: me.subApplication.article,
            aboCommerceController: me.subApplication.getController('AboCommerce')
        });
    }
});
// {/block}
