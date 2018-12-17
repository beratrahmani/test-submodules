// {namespace name="backend/bundle/article/view/main"}
// {block name="backend/article/view/bundle/list"}
Ext.define('Shopware.apps.Article.view.bundle.List', {

    /**
     * The listing component is an extension of the Ext.grid.Panel.
     */
    extend: 'Ext.grid.Panel',

    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets
     */
    alias: 'widget.article-bundle-list',

    /**
     * The initComponent template method is an important initialization step for a Component.
     * It is intended to be implemented by each subclass of Ext.Component to provide any needed constructor logic.
     * The initComponent method of the class being created is called first,
     * with each initComponent method up the hierarchy to Ext.Component being called thereafter.
     * This makes it easy to implement and, if needed, override the constructor logic of the Component at any step in the hierarchy.
     * The initComponent method must contain a call to callParent in order to ensure that the parent class' initComponent method is also called.
     *
     * @returns { void }
     */
    initComponent: function() {
        var me = this;
        me.registerEvents();
        me.columns = me.createColumns();
        me.tbar = me.createToolBar();
        me.selModel = me.createSelectionModel();
        me.bbar = me.createPagingBar();
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
             * Fired when the user clicks the add button in the toolbar to add a new bundle.
             */
            'addBundle',

            /**
             * @Event
             * Custom component event.
             * Fired when the user clicks the delete action column within the grid to
             * delete a single bundle or if the user select one or many grid rows
             * and clicks the delete button in the grid toolbar.
             * @param array The selected record(s)
             */
            'deleteBundle',

            /**
             * @Event
             * Custom component event.
             * Fired when the user change the grid selection.
             * @param Ext.data.Model The record of the first selected grid row
             */
            'selectBundle'
        );
    },

    /**
     * Creates the columns for the grid panel.
     *
     * @returns { Array }
     */
    createColumns: function() {
        var me = this, columns = [];

        columns.push(me.createNameColumn());
        columns.push(me.createActionColumn());

        return columns;
    },

    /**
     * Creates the name column for the listing.
     *
     * @returns { Ext.grid.column.Column }
     */
    createNameColumn: function() {
        return Ext.create('Ext.grid.column.Column', {
            header: '{s name=list/bundle_name_column}Name{/s}',
            dataIndex: 'name',
            flex: 1
        });
    },

    /**
     * Creates the action column for the listing.
     *
     * @returns { Ext.grid.column.Action }
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
     *
     * @returns { Array }
     */
    getActionColumnItems: function() {
        var me = this,
            items = [];

        items.push(me.createDeleteActionColumnItem());
        return items;
    },

    /**
     * Creates the delete action column item for the listing action column
     *
     * @returns { Object }
     */
    createDeleteActionColumnItem: function() {
        var me = this;

        return {
            iconCls: 'sprite-minus-circle-frame',
            width: 30,
            tooltip: '{s name=list/delete_bundle_column}Delete bundle{/s}',
            handler: function(grid, rowIndex, colIndex, metaData, event, record) {
                me.fireEvent('deleteBundle', [ record ]);
            }
        };
    },

    /**
     * Creates the tool bar for the listing component.
     *
     * @returns { Ext.toolbar.Toolbar }
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
     *
     * @returns { Array }
     */
    createToolBarItems: function() {
        var me = this, items = [];

        items.push(me.createToolBarAddButton());
        items.push(me.createToolBarDeleteButton());

        return items;
    },

    /**
     * Creates the add button for the listing toolbar.
     *
     * @returns { Ext.button.Button }
     */
    createToolBarAddButton: function() {
        var me = this;

        me.addButton = Ext.create('Ext.button.Button', {
            text: '{s name=list/add_bundle_button}Add bundle{/s}',
            cls: 'secondary small',
            handler: function() {
                me.fireEvent('addBundle');
            }
        });

        return me.addButton;
    },

    /**
     * Creates the delete button for the listing toolbar.
     *
     * @returns { Ext.button.Button }
     */
    createToolBarDeleteButton: function() {
        var me = this;

        me.deleteButton = Ext.create('Ext.button.Button', {
            text: '{s name=list/delete_button}Delete selected{/s}',
            cls: 'secondary small',
            disabled: true,
            handler: function() {
                var records = me.selModel.getSelection();
                if (records.length > 0) {
                    me.fireEvent('deleteBundle', records);
                }
            }
        });

        return me.deleteButton;
    },

    /**
     * Creates the selection model for the listing component.
     *
     * @returns { Ext.selection.Model }
     */
    createSelectionModel: function() {
        var me = this;

        return Ext.create('Ext.selection.CheckboxModel', {
            listeners: {
                selectionchange: function(view, selected) {
                    me.deleteButton.setDisabled(selected.length === 0);
                    if (selected.length > 0) {
                        me.fireEvent('selectBundle', selected[0]);
                    }
                }
            }
        });
    },

    /**
     * @returns { Object }
     */
    createPagingBar: function() {
        return {
            xtype: 'pagingtoolbar',
            store: this.store
        };
    }

});
// {/block}
