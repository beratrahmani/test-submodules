// {namespace name="backend/bundle/article/view/main"}
// {block name="backend/article/view/bundle/tabs/limited_detail"}
Ext.define('Shopware.apps.Article.view.bundle.tabs.LimitedDetail', {

    extend: 'Ext.grid.Panel',

    title: '{s name=limited_details/title}Limit variants{/s}',

    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets
     */
    alias: 'widget.bundle-limited-detail-listing',

    initComponent: function() {
        var me = this;
        me.tbar = me.createToolBar();
        me.columns = me.createColumns();
        me.callParent(arguments);
    },

    /**
     * Creates the columns for the grid panel.
     * @return { Array }
     */
    createColumns: function() {
        var me = this, columns = [];

        columns.push(me.createNumberColumn());
        columns.push(me.createNameColumn());
        columns.push(me.createActionColumn());

        return columns;
    },

    /**
     * Creates the number column for the listing
     * @return { Ext.grid.column.Column }
     */
    createNumberColumn: function() {
        return Ext.create('Ext.grid.column.Column', {
            header: '{s name=limited_details/product_number_column}Product number{/s}',
            dataIndex: 'number',
            flex: 1
        });
    },

    /**
     * Creates the number column for the listing
     * @return { Ext.grid.column.Column }
     */
    createNameColumn: function() {
        return Ext.create('Ext.grid.column.Column', {
            header: '{s name=limited_details/additional_text_column}Additional text{/s}',
            dataIndex: 'additionalText',
            flex: 1
        });
    },

    /**
     * Creates the action column for the listing.
     * @return { Ext.grid.column.Action }
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
     * @return { Array }
     */
    getActionColumnItems: function() {
        var me = this,
            items = [];

        items.push(me.createDeleteActionColumnItem());
        return items;
    },

    /**
     * Creates the delete action column item for the listing action column
     * @return { Object }
     */
    createDeleteActionColumnItem: function() {
        var me = this;

        return {
            iconCls: 'sprite-minus-circle-frame',
            width: 30,
            tooltip: '{s name=limited_details/delete_variant_column}Delete variant{/s}',
            handler: function(grid, rowIndex, colIndex, metaData, event, record) {
                me.fireEvent('deleteLimitedDetail', [record]);
            }
        };
    },

    /**
     * Creates the tool bar for the listing component.
     * @return { Ext.toolbar.Toolbar }
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
     * @return { Array }
     */
    createToolBarItems: function() {
        var me = this, items = [];

        items.push(me.createToolBarSpacer(6));
        items.push(me.createToolBarVariantComboBox());
        return items;
    },

    /**
     * Creates a toolbar spacer with the passed width value.
     * @param width
     * @return { Ext.toolbar.Spacer }
     */
    createToolBarSpacer: function(width) {
        return Ext.create('Ext.toolbar.Spacer', {
            width: width
        });
    },

    /**
     * Creates the paging combo box for the listing combo box.
     * @return { Shopware.form.field.PagingComboBox }
     */
    createToolBarVariantComboBox: function() {
        var me = this;

        var store = Ext.create('Shopware.apps.Article.store.bundle.Variant');
        store.getProxy().extraParams.productId = me.product.get('id');

        me.variantComboBox = Ext.create('Shopware.form.field.PagingComboBox', {
            store: store,
            triggerAction: 'all',
            queryMode: 'remote',
            margin: '0 0 9 0',
            pageSize: 10,
            width: 400,
            fieldLabel: '{s name=limited_details/add_variant_field}Add variant{/s}',
            labelWidth: 180,
            displayField: 'number',
            valueField: 'id',
            tpl: me.createComboBoxTemplate(),
            // template for the content inside text field
            displayTpl: me.createComboBoxDisplayTemplate(),
            listeners: {
                beforeselect: function(comboBox, record) {
                    me.fireEvent('addLimitedDetail', record);
                    // we want to prevent the default actions from the combo box.
                    return false;
                }
            }
        });

        return me.variantComboBox;
    },

    /**
     * Creates the xTemplate for the "tpl" property of the
     * variant combo box.
     * @return { Ext.XTemplate }
     */
    createComboBoxTemplate: function() {
        return Ext.create('Ext.XTemplate',
            '{literal}<tpl for=".">',
                '<div class="x-boundlist-item">',
                    '<span style="font-weight: 700; color: #475c6b; text-shadow: 1px 1px 1px #ffffff;">',
                        '{number}',
                    '</span>',
                    '<p style="color: #999999;">',
                        '{additionalText}',
                    '</p>',
                '</div>',
            '</tpl>{/literal}'
        );
    },

    /**
     * Creates the xTemplate for the "displayTpl" property of the
     * variant combo box.
     * @return { Ext.XTemplate }
     */
    createComboBoxDisplayTemplate: function() {
        return Ext.create('Ext.XTemplate',
            '{literal}<tpl for=".">',
                '<span style="font-weight: 700; color: #475c6b; text-shadow: 1px 1px 1px #ffffff;">',
                    '{number}',
                '</span>',
                '<p style="color: #999999;">',
                    '{additionalText}',
                '</p>',
            '</tpl>{/literal}'
        );
    }
});
// {/block}
