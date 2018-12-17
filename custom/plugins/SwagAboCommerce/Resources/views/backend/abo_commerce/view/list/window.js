
// {namespace name="backend/abo_commerce/abo_commerce/view/main"}
// {block name="backend/abo_commerce/view/list/window"}
Ext.define('Shopware.apps.AboCommerce.view.list.Window', {

    /**
     * Define that the order main window is an extension of the enlight application window
     * @string
     */
    extend: 'Enlight.app.Window',

    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets.
     * @string
     */
    alias: 'widget.abo-commerce-list-window',

    /**
     * Define window width
     * @integer
     */
    width: '80%',

    /**
     * Define window height
     * @integer
     */
    height: '90%',

    layout: 'fit',

    /**
     * Contains all snippets for this view
     * @object
     */
    snippets: {
        title: '{s name=window/title}Subscriptions{/s}',

        tabDiscountSubscriptions: '{s name=window/tab_discount_subscriptions}Discount subscriptions{/s}',
        tabSettings: '{s name=window/tab_settings}Settings{/s}',
        tabPayments: '{s name=window/tab_payments}Payment means{/s}',
        tabArticles: '{s name=window/tab_articles}Subscription articles{/s}'
    },

    /**
     * The initComponent template method is an important initialization step for a Component.
     * It is intended to be implemented by each subclass of Ext.Component to provide any needed constructor logic.
     * The initComponent method of the class being created is called first,
     * with each initComponent method up the hierarchy to Ext.Component being called thereafter.
     * This makes it easy to implement and, if needed, override the constructor logic of the Component at any step in the hierarchy.
     * The initComponent method must contain a call to callParent in order to ensure that the parent class' initComponent method is also called.
     *
     * @return void
     */
    initComponent: function () {
        var me = this;
        me.items = me.createItems();
        me.title = me.snippets.title;

        me.callParent(arguments);
    },

    /**
     * Creates the items for the list window.
     */
    createItems: function() {
        var me = this;

        return [{
            xtype: 'tabpanel',
            items: [{
                title: me.snippets.tabDiscountSubscriptions,
                store: me.orderStore,
                xtype: 'abo-commerce-list'
            }, {
                title: me.snippets.tabArticles,
                store: me.articleStore,
                xtype: 'abo-commerce-articles'
            }, {
                title: me.snippets.tabPayments,
                settingsStore: me.settingsStore,
                xtype: 'abo-commerce-settings-payment'
            }, {
                title: me.snippets.tabSettings,
                settingsStore: me.settingsStore,
                xtype: 'abo-commerce-settings-settings'
            }]
        }];
    }
});
// {/block}
