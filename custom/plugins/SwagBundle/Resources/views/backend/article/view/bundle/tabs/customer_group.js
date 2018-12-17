// {namespace name="backend/bundle/article/view/main"}
// {block name="backend/article/view/bundle/tabs/customer_group"}
Ext.define('Shopware.apps.Article.view.bundle.tabs.CustomerGroup', {
    extend: 'Ext.grid.Panel',

    title: '{s name=customergroups/title}Customer groups{/s}',

    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets
     */
    alias: 'widget.bundle-customer-group-listing',

    initComponent: function() {
        var me = this;
        me.registerEvents();
        me.columns = me.createColumns();
        me.tbar = me.createToolBar();
        me.callParent(arguments);
    },

    /**
     * Adds the specified events to the list of events which this Observable may fire
     */
    registerEvents: function() {
        this.addEvents(
            /**
             * @Event
             * Custom component event.
             * Fired when the user opens the toolbar customer group combo box
             * and select a combo box row.
             * @param Ext.data.Model The selected record
             */
            'addCustomerGroup',

            /**
             * @Event
             * Custom component event.
             * Fired when the user clicks the delete action column item
             * in the listing.
             * @param Ext.data.Model The row record
             */
            'deleteCustomerGroup'
        );
    },

    /**
     * Creates the columns for the grid panel.
     * @retun { Array }
     */
    createColumns: function() {
        var me = this, columns = [];
        columns.push(me.createNameColumn());
        columns.push(me.createActionColumn());
        return columns;
    },

    /**
     * Creates the name column for the listing.
     * @retun { Ext.grid.column.Column }
     */
    createNameColumn: function() {
        return Ext.create('Ext.grid.column.Column', {
            header: '{s name=customergroups/name_column}Customer group{/s}',
            dataIndex: 'name',
            flex: 1
        });
    },

    /**
     * Creates the action column for the listing.
     * @retun { Ext.grid.column.Action }
     */
    createActionColumn: function() {
        var me = this, items;

        items = me.getActionColumnItems();

        return Ext.create('Ext.grid.column.Action', {
            items: items,
            width: items.length * 30
        });
    },

    /**
     * Creates the action column items for the listing.
     * @retun { Array }
     */
    getActionColumnItems: function() {
        var me = this,
            items = [];

        items.push(me.createDeleteActionColumnItem());
        return items;
    },

    /**
     * Creates the delete action column item for the listing action column
     * @retun { Object }
     */
    createDeleteActionColumnItem: function() {
        var me = this;

        return {
            iconCls: 'sprite-minus-circle-frame',
            width: 30,
            tooltip: '{s name=customergroups/delete_column}Delete customer group{/s}',
            handler: function(grid, rowIndex, colIndex, metaData, event, record) {
                me.fireEvent('deleteCustomerGroup', [ record ]);
            }
        };
    },

    /**
     * Creates the tool bar for the listing component.
     * @retun { Ext.toolbar.Toolbar }
     */
    createToolBar: function() {
        var me = this;

        return Ext.create('Ext.toolbar.Toolbar', {
            items: me.createToolBarItems(),
            dock: 'top'
        });
    },

    /**
     * Creates the elements for the listing toolbar.
     * @retun { Array }
     */
    createToolBarItems: function() {
        var me = this, items = [];

        items.push(me.createToolBarSpacer(6));
        items.push(me.createToolBarCustomerGroupComboBox());
        return items;
    },

    /**
     * Creates a toolbar spacer with the passed width value.
     * @param width
     * @retun { Ext.toolbar.Spacer }
     */
    createToolBarSpacer: function(width) {
        return Ext.create('Ext.toolbar.Spacer', {
            width: width
        });
    },

    /**
     * Creates the customer group combo box for the bundle customer group listing.
     * @retun { Ext.form.field.ComboBox }
     */
    createToolBarCustomerGroupComboBox: function() {
        var me = this;

        me.customerGroupComboBox = Ext.create('Ext.form.field.ComboBox', {
            store: me.customerGroupStore,
            queryMode: 'local',
            name: 'customerGroup',
            margin: '0 0 9 0',
            displayField: 'name',
            valueField: 'id',
            fieldLabel: '{s name=customergroups/add_customergroup_field}Add customer group{/s}',
            labelWidth: 180,
            width: 400,
            listeners: {
                select: function(combo, record) {
                    me.fireEvent('addCustomerGroup', record[0]);
                }
            }
        });
        return me.customerGroupComboBox;
    }

});
// {/block}
