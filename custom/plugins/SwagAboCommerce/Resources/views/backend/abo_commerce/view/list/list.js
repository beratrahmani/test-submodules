
// {namespace name="backend/abo_commerce/abo_commerce/view/main"}
// {block name="backend/abo_commerce/view/list/list"}
Ext.define('Shopware.apps.AboCommerce.view.list.List', {

    extend: 'Ext.grid.Panel',

    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets.
     * @string
     */
    alias: 'widget.abo-commerce-list',

    /**
     * Contains all snippets for this view
     * @object
     */
    snippets: {
        tooltipExecuteOrder: '{s name=list/tooltip_execute_order}Execute order{/s}',
        tooltipOpenCustomer: '{s name=list/tooltip_open_customer}Open customer{/s}',
        tooltipOpenArticle: '{s name=list/tooltip_open_article}Open article{/s}',
        tooltipOpenOrder: '{s name=list/tooltip_open_order}Open order{/s}',
        tooltipOpenLastOrder: '{s name=list/tooltip_open_last_customer}Open last order{/s}',
        tooltipDeleteAbo: '{s name=list/tooltip_delete_abo}Delete Abo{/s}',

        columnName: '{s name=list/column_name}Name{/s}',
        columnCustomer: '{s name=list/column_customer}Customer{/s}',
        columnCustomerGroup: '{s name=list/column_customer_group}Customergroup{/s}',
        columnDuration: '{s name=list/column_duration}Duration{/s}',
        columnDeliveryInterval: '{s name=list/column_delivery_interval}Delivery interval{/s}',
        columnCreated: '{s name=list/column_created}Created{/s}',
        columnDueDate: '{s name=list/column_due_date}Due date{/s}',
        columnLastExecuted: '{s name=list/column_last_executed}Last executed{/s}',
        columnLastRun: '{s name=list/column_last_run}Last run{/s}',

        buttonFilterDue: '{s name=list/button_filter_due}Show only due items{/s}',
        buttonExecuteDueOrders: '{s name=list/button_execute_due_orders}Execute due orders{/s}',
        buttonExecuteSelectedOrders: '{s name=list/button_execute_selected_orders}Execute selected orders{/s}',

        emptyTextSearch: '{s name=list/empty_text_search}search...{/s}',

        messageConfirmOrderTitle: '{s name=controller/message_confirm_order_title}Confirm{/s}',
        messageConfirmOrderExpiredMessage: '{s name=controller/message_confirm_order_expired_message}The subscription expired on %s. Do you really want to execute this order?{/s}',

        unitWeeks: '{s name=list/unit_weeks}Weeks{/s}',
        unitMonths: '{s name=list/unit_months}Months{/s}'
    },

    /**
     * Called when the component will be initialed.
     */
    initComponent: function() {
        var me = this;

        me.registerEvents();
        me.columns = me.createColumns();
        me.tbar = me.createToolbar();
        me.bbar = me.createPagingBar();
        me.selModel = me.createSelectionModel();
        me.callParent(arguments);
    },

    /**
     * Registers additional component events
     */
    registerEvents: function() {
        this.addEvents(
            'executeOrder',
            'search',
            'openArticle',
            'openOrder',
            'openCustomer',
            'filterDue',
            'showExpired',
            'deleteAbo'
        );
    },

    /**
     * Creates the toolbar
     *
     * @return Object
     */
    createToolbar: function() {
        var me = this;

        return {
            xtype: 'toolbar',
            ui: 'shopware-ui',
            cls: 'shopware-toolbar',
            dock: 'top',
            items: me.createToolbarItems()
        };
    },

    /**
     * Creates the toolbar items
     *
     * @return Array
     */
    createToolbarItems: function() {
        var me = this, items = [];

        items.push({
            xtype: 'checkbox',
            boxLabel: me.snippets.buttonFilterDue,
            margin: '0 15px 0 0',
            handler: function(checkbox, checked) {
                me.fireEvent('filterDue', checked, me);
            }
        });

        me.executeOrdersButton = Ext.create('Ext.Button', {
            text: me.snippets.buttonExecuteDueOrders,
            disabled: true,
            iconCls: 'sprite-baggage-cart-box-label',
            handler: function() {
                var selection = me.selModel.getSelection();

                if (selection.length === 0) {
                    selection = [];

                    me.selModel.getStore().each(function(item) {
                        if (item.get('isDue') && !item.get('isExpired')) {
                            selection.push(item);
                        }
                    });
                } else {
                    for (var i = 0; i < selection.length; i++) {
                        if (selection[i].get('isExpired')) {
                            var expiredRecord = selection[i];
                        }
                    }
                }

                me.selModel.deselectAll();

                if (selection.length === 0) {
                    return;
                }

                if (expiredRecord !== undefined) {
                    var date = Ext.util.Format.date(expiredRecord.get('dueDate'), 'd/m/Y');
                    var message = me.snippets.messageConfirmOrderExpiredMessage.replace('%s', date);

                    Ext.MessageBox.confirm(me.snippets.messageConfirmOrderTitle, message, function (response) {
                        if (response === 'yes') {
                            me.fireEvent('executeOrderSelection', selection);
                        }
                    });
                } else {
                    me.fireEvent('executeOrderSelection', selection);
                }
            }
        });

        items.push(me.executeOrdersButton);

        items.push('->');
        items.push(me.createSearchField());

        return items;
    },

    /**
     * Creates the searchfield.
     *
     * @return Object
     */
    createSearchField: function() {
        var me = this;

        return {
            xtype: 'textfield',
            name: 'searchfield',
            action: 'search',
            width: 170,
            cls: 'searchfield',
            enableKeyEvents: true,
            checkChangeBuffer: 500,
            emptyText: me.snippets.emptyTextSearch,
            listeners: {
                change: function(field, value) {
                    me.fireEvent('search', value, me);
                }
            }
        };
    },

    updateExecuteButtonState: function() {
        var me = this;
        var dueItems = [];
        var selModel = me.getSelectionModel();
        var selection = selModel.getSelection();
        var store = me.store;

        if (selection.length > 0) {
            me.executeOrdersButton.setDisabled(false);
            me.executeOrdersButton.setText(me.snippets.buttonExecuteSelectedOrders);

            return;
        }

        store.each(function(item) {
            if (item.get('isDue')) {
                dueItems.push(item);
            }
        });

        if (dueItems.length > 0) {
            me.executeOrdersButton.setDisabled(false);
            me.executeOrdersButton.setText(me.snippets.buttonExecuteDueOrders);

            return;
        }

        me.executeOrdersButton.setText(me.snippets.buttonExecuteDueOrders);
        me.executeOrdersButton.setDisabled(true);
    },

    /**
     * Creates the grid selection model.
     *
     * @return Object
     */
    createSelectionModel: function() {
        var me = this;

        return Ext.create('Ext.selection.CheckboxModel', {
            listeners: {
                selectionchange: function () {
                    me.updateExecuteButtonState();
                }
            }
        });
    },

    /**
     * Creates the paging toolbar for the abo_commerce listing.
     *
     * @return Object
     */
    createPagingBar: function() {
        var me = this;

        return {
            xtype: 'pagingtoolbar',
            displayInfo: true,
            store: me.store
        };
    },

    /**
     * Creates the grid columns
     *
     * @return Array grid columns
     */
    createColumns: function () {
        var me = this, actionColumItems = [];

        actionColumItems.push({
            iconCls: 'sprite-baggage-cart-box-label',
            cls: 'order',
            tooltip: me.snippets.tooltipExecuteOrder,
            handler: function(grid, rowIndex, colIndex, metaData, event, record) {
                me.fireEvent('executeOrder', record, me);
            }
        });

        actionColumItems.push({
            iconCls: 'sprite-sticky-notes-pin',
            tooltip: me.snippets.tooltipOpenLastOrder,
            handler: function(grid, rowIndex, colIndex, metaData, event, record) {
                me.fireEvent('openOrder', record.get('lastOrderId'), me);
            }
        });

        actionColumItems.push({
            iconCls: 'sprite-inbox--arrow',
            tooltip: me.snippets.tooltipOpenArticle,
            handler: function(grid, rowIndex, colIndex, metaData, event, record) {
                me.fireEvent('openArticle', record.get('articleId'), me);
            }
        });

        actionColumItems.push({
            iconCls: 'sprite-user--arrow',
            tooltip: me.snippets.tooltipOpenCustomer,
            handler: function(grid, rowIndex, colIndex, metaData, event, record) {
                me.fireEvent('openCustomer', record.get('customerId'), me);
            }
        });

        actionColumItems.push({
            iconCls: 'sprite-document--pencil',
            tooltip: '{s name=list/tooltip_terminate_subscription}{/s}',
            handler: function(grid, rowIndex, colIndex, metaData, event, record) {
                me.fireEvent('terminateSubscription', record.get('id'));
            },
            getClass: function(v, meta, record) {
                if (record.get('endlessSubscription') !== true
                    || record.get('endlessSubscription') === true && !Ext.isEmpty(record.get('lastRun'))
                ) {
                    return 'x-hide-display';
                }
            }
        });

        actionColumItems.push({
            iconCls: 'sprite-minus-circle-frame',
            tooltip: me.snippets.tooltipDeleteAbo,
            handler: function(grid, rowIndex, colIndex, metaData, event, record) {
                me.fireEvent('deleteAbo', record, me);
            }
        });

        return [{
            header: me.snippets.columnName,
            dataIndex: 'articleName',
            flex: 1
        },
        {
            header: me.snippets.columnCustomer,
            dataIndex: 'customerMail',
            flex: 1
        },
        {
            header: me.snippets.columnCustomerGroup,
            dataIndex: 'customerGroup',
            flex: 1
        },
        {
            header: me.snippets.columnDuration,
            dataIndex: 'duration',
            flex: 1,
            renderer: function(value, metaData, record) {
                var unit = record.get('durationUnit');

                if (Ext.isEmpty(unit)) {
                    return '{s name=list/column_duration_endless}{/s}';
                } else if (unit === 'weeks') {
                    unit = me.snippets.unitWeeks;
                } else {
                    unit = me.snippets.unitMonths;
                }

                return value + ' ' + unit;
            }
        },
        {
            header: me.snippets.columnDeliveryInterval,
            dataIndex: 'deliveryInterval',
            flex: 1,
            renderer: function(value, metaData, record) {
                var unit = record.get('deliveryIntervalUnit');

                if (unit === 'weeks') {
                    unit = me.snippets.unitWeeks;
                } else {
                    unit = me.snippets.unitMonths;
                }

                return value + ' ' + unit;
            }
        },
        {
            xtype: 'datecolumn',
            header: me.snippets.columnCreated,
            dataIndex: 'created',
            flex: 1
        },
        {
            xtype: 'datecolumn',
            header: me.snippets.columnDueDate,
            dataIndex: 'dueDate',
            flex: 1,
            renderer: function(value, metaData, record) {
                value = Ext.util.Format.date(value);

                if (record.get('isExpired')) {
                    return '<span style="text-decoration: line-through">' + value + '</strong>';
                } else if (record.get('isDue')) {
                    return '<strong>' + value + '</strong>';
                } else {
                    return value;
                }
            }
        },
        {
            xtype: 'datecolumn',
            header: me.snippets.columnLastExecuted,
            dataIndex: 'recentRun',
            flex: 1
        },
        {
            xtype: 'datecolumn',
            header: me.snippets.columnLastRun,
            dataIndex: 'lastRun',
            flex: 1,
            renderer: function(value, meta, record) {
                if (!Ext.isDate(value)) {
                    return '-';
                }
                return Ext.util.Format.date(value, this.format);
            }
        },
        {
                /**
                 * Special column type which provides
                 * clickable icons in each row
                 */
            xtype: 'actioncolumn',
            width: actionColumItems.length * 26,
            items: actionColumItems
        }];
    }
});
// {/block}
