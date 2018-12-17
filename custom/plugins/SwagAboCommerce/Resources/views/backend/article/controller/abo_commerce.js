
// {namespace name="backend/abo_commerce/article/view/main"}
// {block name="backend/abo_commerce/controller/abo_commerce"}
Ext.define('Shopware.apps.Article.controller.AboCommerce', {

    /**
     * The parent class that this class extends.
     * @string
     */
    extend: 'Ext.app.Controller',

    refs: [
        { ref: 'aboCommerceTabSettings', selector: 'article-detail-window abo-commerce-tab-settings' },
        { ref: 'aboCommerceTabSettingsOrderNumber', selector: 'article-detail-window abo-commerce-tab-settings textfield[name=ordernumber]' },
        { ref: 'aboCommerceConfiguration', selector: 'article-detail-window abo-commerce-configuration' },
        { ref: 'aboCommercePriceListing', selector: 'article-detail-window abo-commerce-price-listing' },
        { ref: 'detailWindow', selector: 'article-detail-window' }
    ],

    /**
     * Contains all snippets for this view
     * @object
     */
    snippets: {
        messageTitle: '{s name=controller/message_title}Successful{/s}',
        messageText: '{s name=controller/message_text}Abo-Article has successfully been saved{/s}'
    },

    record: null,

    /**
     * A template method that is called when your application boots.
     * It is called before the Application's launch function is executed
     * so gives a hook point to run any code before your Viewport is created.
     */
    init: function() {
        var me = this;

        me.control({
            'article-detail-window': {
                aboCommerceTabActivated: me.onAboCommerceTabActivated,
                aboCommerceStoreReloadNeeded: me.onAboCommerceStoreReloadNeeded,
                aboCommerceTabDeactivated: me.onAboCommerceTabDeactivated,
                saveAboCommerce: me.onSaveAboCommerce
            },

            'article-detail-window abo-commerce-price-listing': {
                priceTabChanged: me.onPriceTabChanged
            },

            'article-detail-window article-price-grid': {
                copyDefaultDiscounts: me.onCopyDefaultDiscounts
            },

            'article-detail-window abo-commerce-price-listing grid': {
                edit: me.onAfterEditPrice,
                beforeedit: me.onBeforeEditPrice
            }
        });
    },

    /**
     * Event listener function which fired when the user
     * starts the edit of a price row.
     *
     * @param plugin
     * @param event
     * @return boolean
     */
    onBeforeEditPrice: function(plugin, event) {
        var store = event.grid.store,
            maxValue = null,
            minValue = 1,
            record = event.record,
            editor = event.column.getEditor(event.record),
            previousRecord = store.getAt(event.rowIdx - 1),
            nextRecord = store.getAt(event.rowIdx + 1);

        // check if the current row is the last row
        if (event.field === 'durationFrom') {
            // if the current row isn't the last row, we want to cancel the edit.
            if (nextRecord) {
                maxValue = nextRecord.get('durationFrom') - 1;
            }

            // check if the current row has a previous row.
            if (previousRecord) {
                minValue = previousRecord.get('durationFrom') + 1;
            } else {
                minValue = 1;
                maxValue = 1;
            }

            editor.setMinValue(minValue);
            editor.setMaxValue(maxValue);

            return true;
        }

        if (event.field === 'to') {
            // if the current row isn't the last row, we want to cancel the edit.
            if (nextRecord) {
                return false;
            }

            if (previousRecord) {
                minValue = record.get('durationFrom');
            }

            editor.setMinValue(minValue);
            return true;
        }

        return true;
    },

    /**
     * Event listener function which fired when the user
     * edit a column of the price grid.
     * This function handles the calculation for the
     * prices and discounts.
     *
     * @param editor
     * @param event
     */
    onAfterEditPrice: function(editor, event) {
        var record = event.record,
            store = event.grid.store;

        // user changed the "to" field?
        if (event.field === 'to') {
            if (Ext.isNumeric(event.value)) {
                // if the current row is the last row, we need to add a new row with "to any"
                var newRecord = Ext.create('Shopware.apps.Article.model.abo_commerce.Price', {
                    durationFrom: event.value + 1,
                    customerGroupId: record.get('customerGroupId', null)
                });

                store.add(newRecord);
            }
        }

        if (event.field === 'discountAbsolute') {
            record.set('discountPercent', null);
        }

        if (event.field === 'discountPercent') {
            record.set('discountAbsolute', null);
        }

        event.grid.reconfigure(event.grid.getStore());
    },

    onAboCommerceStoreReloadNeeded: function() {
        var me = this;

        me.doReloadAboCommerceStores();
    },

    /**
     * @EventListener
     * Event listener function of the product detail window.
     * Fired when the user activate the AboCommerce tab.
     *
     * @param window Shopware.apps.Article.view.detail.Window
     */
    onAboCommerceTabActivated: function(window) {
        var me = this;

        window.aboCommerceSaveButton.show();
        window.saveButton.hide();
        window.configuratorSaveButton.hide();

        me.doReloadAboCommerceStores();
    },

    doReloadAboCommerceStores: function() {
        var me = this,
            number,
            textFieldOrderNumber = me.getAboCommerceTabSettingsOrderNumber();

        me.aboCommerceDetailStore.getProxy().extraParams.articleId = me.subApplication.article.internalId;
        me.aboCommerceDetailStore.load({
            callback: function(records, operation) {
                if (!operation.wasSuccessful()) {
                    return;
                }
                if (records.length > 0) {
                    me.record = records[0];
                    me.getAboCommerceTabSettings().loadRecord(me.record);
                    me.getAboCommerceConfiguration().loadRecord(me.record);
                    me.getAboCommercePriceListing().loadRecordIntoView(me.record);
                } else {
                    // Create a new, empty record
                    me.record = Ext.create('Shopware.apps.Article.model.abo_commerce.AboCommerce');

                    // Do not pass empty record here: This way the components' default values will be used
                    me.getAboCommerceTabSettings().loadRecord();
                    me.getAboCommerceConfiguration().loadRecord();

                    // Set the empty record for the priceListing in order to clear values
                    me.getAboCommercePriceListing().loadRecordIntoView(me.record);

                    // Set the product's ordernumber as abo-ordernumber (with .ABO appended)
                    if (textFieldOrderNumber) {
                        number = me.getDetailWindow().article.raw.mainDetail.number || '';
                        textFieldOrderNumber.setValue(number + '.ABO');
                    }
                }
            }
        });
    },

    /**
     * Event listener function of the product detail window.
     * Fired when the user change the active tab of the main tab panel from AboCommerce to
     * another tab.
     * @param window Shopware.apps.Article.view.detail.Window
     */
    onAboCommerceTabDeactivated: function(window) {
        window.aboCommerceSaveButton.hide();
    },

    /**
     * Event will be fired when the user change the tab panel in the price field set.
     *
     * @param { Ext.tab.Tab }       oldTab The previous tab panel
     * @param { Ext.tab.Tab }       newTab The clicked tab panel
     * @param { Ext.data.Store }    priceStore - The price store
     */
    onPriceTabChanged: function(oldTab, newTab, priceStore) {
        var customerGroup = newTab.customerGroup;

        priceStore.clearFilter();

        // now we can filter the price store for the current customer group.
        priceStore.filter({
            filterFn: function(item) {
                return item.get('customerGroupId') == customerGroup.get('id');
            }
        });

        // if no prices given for the current customer group, we have to copy the prices of the main customer group
        if (priceStore.data.length > 0) {
            newTab.copyDefaultDiscountsButton.disable();
        } else {
            newTab.copyDefaultDiscountsButton.enable();
        }
    },

    /**
     * @param { Ext.data.Store } priceStore - The price store
     * @param { Ext.data.Model } customerGroup
     * @param { Ext.data.Store } customerGroupStore - The price store
     * @param { Ext.Button } button
     */
    onCopyDefaultDiscounts: function(priceStore, customerGroup, customerGroupStore, button) {
        var me = this,
            firstGroupPrices = [],
            firstGroup = customerGroupStore.first();

        priceStore.clearFilter();
        priceStore.each(function(item) {
            if (item.get('customerGroupId') == firstGroup.get('id')) {
                firstGroupPrices.push(item);
            }
        });

        // now we can filter the price store for the current customer group.
        priceStore.filter({
            filterFn: function(item) {
                return item.get('customerGroupId') == customerGroup.get('id');
            }
        });

        // if no prices given for the current customer group, we have to copy the prices of the main customer group
        if (priceStore.data.length === 0) {
            priceStore.add(me.copyPrices(firstGroupPrices, customerGroup));
        }

        button.disable();
    },

    /**
     * @param firstGroupPrices
     * @param customerGroup
     */
    copyPrices: function(firstGroupPrices, customerGroup) {
        var copiedPrices = [];

        Ext.each(firstGroupPrices, function(price) {
            var priceCopy = Ext.create('Shopware.apps.Article.model.abo_commerce.Price', price.data);
            priceCopy.set('customerGroupId', customerGroup.get('id'));
            priceCopy.set('id', null);
            copiedPrices.push(priceCopy);
        });

        return copiedPrices;
    },

    /**
     * @EventListener
     * Event listener function of the product detail window.
     * Fired when the user activate the AboCommerce tab.
     */
    onSaveAboCommerce: function() {
        var me = this,
            configurationFormPanel = me.getAboCommerceConfiguration(),
            configurationForm = configurationFormPanel.getForm(),
            settingsFormPanel = me.getAboCommerceTabSettings(),
            settingsForm = settingsFormPanel.getForm(),
            lastFilter,
            articleId = me.subApplication.article.internalId;

        if (!settingsForm.isValid()) {
            return;
        }

        if (me.record === undefined) {
            me.record = Ext.create('Shopware.apps.Article.model.abo_commerce.AboCommerce');
        }

        // save last price store filter to filter again after the product saved.
        lastFilter = me.record.getPrices().filters.items;
        me.record.getPrices().clearFilter();

        settingsForm.updateRecord(me.record);
        configurationForm.updateRecord(me.record);

        me.record.save({
            params: {
                articleId: articleId
            },
            success: function(record) {
                Shopware.Notification.createGrowlMessage(me.snippets.messageTitle, me.snippets.messageText);
                me.record = record;
                record.getPrices().filter(lastFilter);
                settingsForm.loadRecord(me.record);
                configurationForm.loadRecord(me.record);
                me.getAboCommercePriceListing().loadRecordIntoView(me.record);
            },
            failure: function() {

            }
        });
    }
});
// {/block}
