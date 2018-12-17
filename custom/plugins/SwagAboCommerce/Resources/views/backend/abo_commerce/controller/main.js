
// {namespace name="backend/abo_commerce/abo_commerce/view/main"}
// {block name="backend/abo_commerce/controller/main"}
Ext.define('Shopware.apps.AboCommerce.controller.Main', {
    /**
     * The parent class that this class extends.
     * @string
     */
    extend: 'Ext.app.Controller',

    shouldCancel: false,

    /**
     * all references to get the elements by the applicable selector
     * @Array
     */
    refs: [
        { ref: 'progressBar', selector: 'abo-commerce-progress progressbar' },
        { ref: 'progressWindow', selector: 'abo-commerce-progress' },
        { ref: 'closeButton', selector: 'abo-commerce-progress button[action=closeWindow]' },
        { ref: 'cancelButton', selector: 'abo-commerce-progress button[action=cancel]' },
        { ref: 'settingsTab', selector: 'abo-commerce-settings-settings' },
        { ref: 'paymentTab', selector: 'abo-commerce-settings-payment' },
        { ref: 'orderList', selector: 'abo-commerce-list' }
    ],

    /**
     * Contains all snippets for the component
     * @object
     */
    snippets: {
        process: '{s name=controller/process}Execute order [0] of [1] ...{/s}',
        done: {
            message: '{s name=controller/done_message}All orders have been executed{/s}',
            title: '{s name=controller/done_title}Successful{/s}'
        },

        messageConfirmOrderTitle: '{s name=controller/message_confirm_order_title}Confirm{/s}',
        messageConfirmOrderMessage: '{s name=controller/message_confirm_order_message}Do you really want to execute this order?{/s}',
        messageConfirmOrderExpiredMessage: '{s name=controller/message_confirm_order_expired_message}The subscription expired on %s. Do you really want to execute this order?{/s}',

        messageConfirmDeleteTitle: '{s name=controller/message_confirm_delete_title}Confirm{/s}',
        messageConfirmDeleteMessage: '{s name=controller/message_confirm_delete_message}Do you really want to delete this order?{/s}',

        messageSuccessTitle: '{s name=controller/message_success_title}Successful{/s}',
        messageSuccessMessage: '{s name=controller/message_success_message}Settings have been successfully saved{/s}',

        messageOrderSuccessMessage: '{s name=controller/message_order_success_message}Order has been successfully executed. Ordernumber: {/s}',

        messageDeleteSuccessMessage: '{s name=controller/message_delete_success_message}Order has been successfully deleted.{/s}',

        messageFailureTitle: '{s name=controller/message_failure_title}Failure{/s}',
        messageFailureMessage: '{s name=controller/message_failure_message}There was an error. Message from server: {/s}'
    },

    /**
     * A template method that is called when your application boots.
     * It is called before the Application's launch function is executed
     * so gives a hook point to run any code before your Viewport is created.
     *
     * @return Ext.window.Window
     */
    init: function () {
        var me = this;

        me.orderStore = Ext.create('Shopware.apps.AboCommerce.store.Order').load();
        me.settingsStore = Ext.create('Shopware.apps.AboCommerce.store.Settings');
        me.articleStore = Ext.create('Shopware.apps.AboCommerce.store.Article');

        me.orderStore.on('load', function() {
            me.getOrderList().updateExecuteButtonState();
        });

        me.mainWindow = me.createListWindow();

        me.control({
            'abo-commerce-settings-payment': {
                activate: me.onActivatePaymentsTab,
                saveSettings: me.onSaveSettings
            },

            'abo-commerce-settings-settings': {
                activate: me.onActivatePaymentsTab,
                saveSettings: me.onSaveSettings
            },

            'abo-commerce-progress': {
                startProcess: me.onStartProcess,
                cancelProcess: me.onCancelProcess,
                closeWindow: me.onCloseProcessWindow
            },

            'abo-commerce-list': {
                executeOrder: me.onExecuteOrder,
                executeOrderSelection: me.onExecuteOrderSelection,
                search: me.onSearch,
                openArticle: me.onOpenArticle,
                openOrder: me.onOpenOrder,
                openCustomer: me.onOpenCustomer,
                terminateSubscription: me.onTerminateSubscription,
                filterDue: me.onFilterDue,
                deleteAbo: me.onDeleteAbo
            },

            'abo-commerce-articles': {
                search: me.onSearchArticle,
                openArticle: me.onOpenArticle
            }
        });

        me.callParent(arguments);

        return me.mainWindow;
    },

    /**
     * Creates and shows the list window of the AboCommerce module.
     * @return { Shopware.apps.AboCommerce.view.list.Window }
     */
    createListWindow: function() {
        var me = this;

        return me.getView('list.Window').create({
            orderStore: me.orderStore,
            settingsStore: me.settingsStore,
            articleStore: me.articleStore
        }).show();
    },

    onSaveSettings: function() {
        var me = this,
            settingsTab = me.getSettingsTab(),
            form = settingsTab.getForm(),
            paymentTab = me.getPaymentTab(),
            record = settingsTab.getRecord();

        if (!form.isValid()) {
            return;
        }

        form.updateRecord(record);

        record.save({
            success: function(record) {
                paymentTab.loadRecordIntoView(record);
                settingsTab.loadRecord(record);
                Shopware.Notification.createGrowlMessage(me.snippets.messageSuccessTitle, me.snippets.messageSuccessMessage);
            },
            failure: function(record) {
                var rawData = record.getProxy().getReader().rawData,
                    message = rawData.message;

                Shopware.Notification.createGrowlMessage(
                    me.snippets.messageFailureTitle,
                    me.snippets.messageFailureMessage + message,
                    'SwagAboCommerce'
                );

                me.settingsStore.load({
                    callback: function() {
                        var record = null;

                        me.settingsStore.isLoaded = true;

                        if (me.settingsStore.getCount() > 0) {
                            record = me.settingsStore.getAt(0);
                            settingsTab.loadRecord(record);
                        } else {
                            record = Ext.create('Shopware.apps.AboCommerce.model.Settings');
                        }

                        paymentTab.loadRecordIntoView(record);
                    }
                });

                paymentTab.reload();
            }
        });
    },

    /**
     * Cancel the document creation.
     */
    onActivatePaymentsTab: function() {
        var me = this,
            settingsTab = me.getSettingsTab(),
            paymentTab = me.getPaymentTab();

        if (me.settingsStore.isLoaded) {
            return;
        }

        me.settingsStore.load({
            callback: function() {
                var record = null;

                me.settingsStore.isLoaded = true;

                if (me.settingsStore.getCount() > 0) {
                    record = me.settingsStore.getAt(0);
                    settingsTab.loadRecord(record);
                } else {
                    record = Ext.create('Shopware.apps.AboCommerce.model.Settings');
                }

                paymentTab.loadRecordIntoView(record);
            }
        });
    },

    /**
     * Cancel the order creation.
     */
    onCancelProcess: function() {
        var me = this;

        me.shouldCancel = true;
    },

    /**
     * Cancel the document creation.
     * @param { Enlight.app.Window } window
     */
    onCloseProcessWindow: function(window) {
        var me = this;

        me.orderStore.load();
        window.destroy();
    },

    /**
     * @param { String } value
     */
    onSearch: function(value) {
        var me = this,
            store = me.orderStore,
            searchString = Ext.String.trim(value);

        // scroll the store to first page
        store.currentPage = 1;

        // If the search-value is empty, reset the filter
        if (searchString.length === 0) {
            store.getProxy().extraParams.search = null;
        } else {
            store.getProxy().extraParams.search = searchString;
        }
        store.load();
    },

    /**
     * @param { boolean } active
     * @param { Shopware.apps.AboCommerce.view.list.Window } view
     */
    onFilterDue: function(active, view) {
        var store = view.store;

        // scroll the store to first page
        store.currentPage = 1;

        if (active) {
            store.getProxy().extraParams.filterDue = 1;
        } else {
            store.getProxy().extraParams.filterDue = 0;
        }

        store.filter();
    },

    /**
     * @param { Ext.data.Model } record
     * @param { Shopware.apps.AboCommerce.view.list.Window } view
     */
    onDeleteAbo: function(record, view) {
        var me = this,
            store = view.store;

        Ext.MessageBox.confirm(me.snippets.messageConfirmDeleteTitle, me.snippets.messageConfirmDeleteMessage, function (response) {
            if (response !== 'yes') {
                return false;
            }

            record.destroy({
                callback: function() {
                    Shopware.Notification.createGrowlMessage(
                        me.snippets.messageSuccessTitle,
                        me.snippets.messageDeleteSuccessMessage,
                        'SwagAboCommerce'
                    );
                    store.load();
                }
            });
        });
    },

    /**
     * @param { Array } selection
     */
    onExecuteOrderSelection: function(selection) {
        var me = this;

        me.getView('batch.Progress').create({
            selection: selection
        }).show();

        var progressBar = me.getProgressBar();
        progressBar.updateProgress(0, Ext.String.format(me.snippets.process, 0, selection.length), true);
    },

    /**
     * @param { Array } selection
     */
    onStartProcess: function(selection) {
        var me = this;

        var progressBar = me.getProgressBar();

        me.executeSingleOrder(selection, 0, progressBar);
    },

    /**
     * @param { Array } selection
     * @param { Integer } index
     * @param { Ext.ProgressBar } progressBar
     */
    executeSingleOrder: function(selection, index, progressBar) {
        var me = this;

        if (index === selection.length) {
            // display finish update progress bar and display finish message
            progressBar.updateProgress((index + 1) / selection.length, me.snippets.done.message, true);

            me.getCancelButton().disable();
            me.getCloseButton().enable();

            // display shopware notification message that the batch process finished
            Shopware.Notification.createGrowlMessage(me.snippets.done.title, me.snippets.done.message);

            return;
        }

        if (me.shouldCancel) {
            me.getCloseButton().enable();
            return;
        }

        var item = selection[index];

        // updates the progress bar value and text, the last parameter is the animation flag
        progressBar.updateProgress((index + 1) / selection.length, Ext.String.format(me.snippets.process, (index + 1), selection.length), true);

        Ext.Ajax.request({
            url: '{url controller="AboCommerce" action="createOrder"}',
            method: 'POST',
            params: {
                aboId: item.get('id')
            },
            success: function() {
                me.executeSingleOrder(selection, index + 1, progressBar);
            },
            failure: function(response) {
                Shopware.Notification.createGrowlMessage(
                    me.snippets.messageFailureTitle,
                    me.snippets.messageFailureMessage + response.responseText,
                    'SwagAboCommerce'
                );

                me.shouldCancel = true;
                me.executeSingleOrder(selection, index + 1, progressBar);
            }
        });
    },

    /**
     * @param { Ext.data.Model } record
     */
    onExecuteOrder: function(record) {
        var me = this,
            message;

        if (record.get('isExpired')) {
            var date = Ext.util.Format.date(record.get('lastRun'), 'd/m/Y');
            message = me.snippets.messageConfirmOrderExpiredMessage.replace('%s', date);
        } else {
            message = me.snippets.messageConfirmOrderMessage;
        }

        Ext.MessageBox.confirm(me.snippets.messageConfirmOrderTitle, message, function (response) {
            if (response !== 'yes') {
                return false;
            }

            Ext.Ajax.request({
                url: '{url controller="AboCommerce" action="createOrder"}',
                method: 'POST',
                params: {
                    aboId: record.get('id')
                },
                success: function(response) {
                    var operation = Ext.decode(response.responseText);

                    if (operation.success === true && operation.data.orderNumber) {
                        Shopware.Notification.createGrowlMessage(
                            me.snippets.messageSuccessTitle,
                            me.snippets.messageOrderSuccessMessage + operation.data.orderNumber,
                            'SwagAboCommerce'
                        );
                    }

                    // fix for pre 4.0.7 paypal module, data is wrapped in array twice
                    if (operation.data[0] && operation.data[0].orderNumber) {
                        Shopware.Notification.createGrowlMessage(
                            me.snippets.messageSuccessTitle,
                            me.snippets.messageOrderSuccessMessage + operation.data[0].orderNumber,
                            'SwagAboCommerce'
                        );
                    }

                    me.orderStore.load();
                },
                failure: function(response) {
                    Shopware.Notification.createGrowlMessage(
                        me.snippets.messageFailureTitle,
                        me.snippets.messageFailureMessage + response.responseText,
                        'SwagAboCommerce'
                    );
                    me.orderStore.load();
                }
            });
        });
    },

    /**
     * @param { int } productId
     */
    onOpenArticle: function(productId) {
        Shopware.app.Application.addSubApplication({
            name: 'Shopware.apps.Article',
            action: 'detail',
            params: {
                articleId: productId
            }
        });
    },

    /**
     * @param { int } customerId
     */
    onOpenCustomer: function(customerId) {
        Shopware.app.Application.addSubApplication({
            name: 'Shopware.apps.Customer',
            action: 'detail',
            params: {
                customerId: customerId
            }
        });
    },

    onTerminateSubscription: function(orderId) {
        var me = this;

        Ext.MessageBox.confirm(
            '{s name="controller/message_termination_confirm"}{/s}',
            '{s name="controller/message_termination_confirm_msg"}{/s}',
            function (response) {
                if (response !== 'yes') {
                    return;
                }

                Ext.Ajax.request({
                    url: '{url controller="AboCommerce" action="terminateSubscription"}',
                    method: 'POST',
                    params: {
                        orderId: orderId
                    },
                    scope: me,
                    callback: function(operation, success, response) {
                        var result = Ext.JSON.decode(response.responseText);

                        if (result.success) {
                            me.orderStore.load();
                            Shopware.Notification.createGrowlMessage(
                                '{s name=controller/message_termination_success}{/s}',
                                '{s name=controller/message_termination_success_msg}{/s}'
                            );

                            if (result.mailSent === false) {
                                Shopware.Notification.createGrowlMessage(
                                    '{s name=controller/message_termination_mail_failure}{/s}',
                                    '{s name=controller/message_termination_mail_failure_msg}{/s}'
                                );
                            }
                        } else {
                            Shopware.Notification.createGrowlMessage(
                                '{s name=controller/message_termination_failure}{/s}',
                                '{s name=controller/message_termination_failure_msg}{/s}'
                            );
                        }
                    }
            });
        });
    },

    /**
     * @param { int } orderId
     */
    onOpenOrder: function(orderId) {
        Shopware.app.Application.addSubApplication({
            name: 'Shopware.apps.Order',
            params: {
                orderId: orderId
            }
        });
    }
});
// {/block}
