
// {namespace name="backend/abo_commerce/article/view/main"}
// {block name="backend/article/view/abo_commerce/tabs/price"}
Ext.define('Shopware.apps.Article.view.abo_commerce.tabs.Price', {

    extend: 'Ext.panel.Panel',
    bodyBorder: 0,
    border: false,

    layout: {
        type: 'vbox',
        align: 'stretch',
        pack: 'start'
    },

    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets
     */
    alias: 'widget.abo-commerce-price-listing',

    /**
     * Contains all snippets for this view
     * @object
     */
    snippets: {
        title: '{s name=price/title}Discounts{/s}',
        headerText: '{s name=price/header_text}All settings for subscription discounts can be found here. The prices of the items can be calculated, either based on the duration of the subscription and the percentage of discount entered, or rather a fixed discount can be entered which functions as the monthly price for the customer.{/s}'
    },

    /**
     * @return void
     */
    initComponent: function() {
        var me = this;

        me.title = me.snippets.title;

        me.items = me.createElements();

        me.basePrice = me.article.getPrice().first().get('price');
        me.registerEvents();
        me.callParent(arguments);
    },

    /**
     * Registers additional component events.
     */
    registerEvents: function() {
        this.addEvents(
            /**
             * Event will be fired when the user change the tab panel in the price field set.
             *
             * @event
             * @param [object] The previous tab panel
             * @param [object] The clicked tab panel
             * @param [Ext.data.Store] The price store
             * @param [array] The price data of the first customer group.
             */

            'priceTabChanged'
        );
    },

    /**
     * @param { Ext.data.Model } record
     */
    loadRecordIntoView: function(record) {
        var me = this;

        me.record = record;

        me.tabPanel.removeAll();
        me.priceStore = record.getPrices();

        me.preparePriceStore();

        var tabs = [];

        me.customerGroupStore.each(function(customerGroup, index) {
            var isDefault = (index === 0);

            if (customerGroup.get('mode') === false) {
                var tab = me.createPriceGrid(customerGroup, me.priceStore, isDefault);
                tabs.push(tab);
            }
        });

        me.tabPanel.add(tabs);
        me.tabPanel.setActiveTab(0);
    },

    /**
     * Creates the elements for the description field set.
     * @return Object - Contains all Ext.form.Fields for the description field set
     */
    createElements: function () {
        var me = this;

        me.tabPanel = Ext.create('Ext.tab.Panel', {
            activeTab: 0,
            border: 0,
            bodyBorder: 0,
            plain: true,
            flex: 1,
            listeners: {
                beforetabchange: function(panel, newTab, oldTab) {
                    me.fireEvent('priceTabChanged', oldTab, newTab, me.priceStore, me.customerGroupStore);
                }
            }
        });

        return [{
            xtype: 'container',
            html: me.snippets.headerText,
            margin: '15px',
            flex: 0,
            style: 'color: #999; font-style: italic;'
        }, me.tabPanel ];
    },

    /**
     * Prepares the price store items for the selected customer group
     */
    preparePriceStore: function() {
        var me = this,
            firstGroup = me.customerGroupStore.first();

        me.priceStore.clearFilter();
        me.priceStore.filter({
            filterFn: function(item) {
                return item.get('customerGroupId') === firstGroup.get('id');
            }
        });

        if (me.priceStore.getCount() === 0) {
            var newRecord = Ext.create('Shopware.apps.Article.model.abo_commerce.Price', {
                durationFrom: 1,
                customerGroupId: firstGroup.get('id')
            });

            me.priceStore.add(newRecord);
        }
    },

    /**
     * Creates a grid for the product prices.
     *
     * @param customerGroup
     * @param priceStore
     * @param { boolean } isDefault
     * @return Ext.grid.Panel
     */
    createPriceGrid: function(customerGroup, priceStore, isDefault) {
        var me = this;

        return Ext.create('Shopware.apps.AboCommerce.view.tabs.PriceGrid', {
            customerGroupStore: me.customerGroupStore,
            customerGroup: customerGroup,
            priceStore: priceStore,
            isDefault: isDefault,
            basePrice: me.basePrice,
            record: me.record
        });
    }
});
// {/block}
