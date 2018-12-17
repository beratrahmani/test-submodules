// {namespace name="backend/bundle/article/view/main"}
// {block name="backend/article/view/bundle/tabs/article"}
Ext.define('Shopware.apps.Article.view.bundle.tabs.Article', {

    extend: 'Ext.grid.Panel',

    title: '{s name=articles/title}Products{/s}',

    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets
     */
    alias: 'widget.bundle-article-listing',

    /**
     * Reference to the bundle controller to use the global function to calculate
     * the total amount for each customer group.
     * Shopware.apps.Article.controller.Bundle
     */
    bundleController: null,

    /**
     * @type { Shopware.form.field.PagingComboBox }
     */
    productSearch: null,

    initComponent: function() {
        var me = this;
        me.registerEvents();
        me.columns = me.createColumns();
        me.tbar = me.createToolBar();
        me.summaryFeature = me.createSummaryFeature();
        me.viewConfig = me.getViewConfig();
        me.features = [me.summaryFeature];
        me.plugins = [me.createCellEditor()];
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
             * Fired when the user select a product in the suggest search.
             * @param Ext.data.Model The selected record
             */
            'addBundleArticle',

            /**
             * @Event
             * Custom component event.
             * Fired when the user clicks the delete action column item.
             * @param Ext.data.Model The row record
             */
            'deleteBundleArticle',

            /**
             * @Event
             * Custom component event.
             * Fired after the user change a bundle product over the row editor.
             * @param Ext.data.Model The row record
             */
            'changeBundleArticle',

            /**
             * @Event
             * Custom component event.
             * Fired when the customer clicks the "open product" action column item.
             * @param int product id
             */
            'openArticle',

            /**
             * @Event
             * Custom component event.
             * Fired when the user drags and drops a bundle product
             */
            'onDropItem'
        );
    },

    /**
     * Creates a grid-view-drag-drop-plugin
     *
     * @return { Object }
     */
    getViewConfig: function() {
        var me = this;

        return {
            plugins: {
                pluginId: 'my-gridviewdragdrop',
                ptype: 'gridviewdragdrop'
            },
            listeners: {
                drop: {
                    fn: function() {
                        me.fireEvent('onDropItem');
                    }
                }
            }
        };
    },

    /**
     * Creates the columns for the grid panel.
     * @return { Array }
     */
    createColumns: function() {
        var me = this, columns = [];

        columns.push(me.createNumberColumn());
        columns.push(me.createNameColumn());
        columns.push(me.createQuantityColumn());
        columns.push(me.createIsConfiguratorColumn());
        columns.push(me.createConfigurableColumn());
        me.customerGroupStore.each(function(customerGroup) {
            if (customerGroup) {
                columns.push(me.createCustomerGroupPriceColumn(customerGroup));
            }
        });

        columns.push(me.createActionColumn());

        return columns;
    },

    /**
     * Creates the number column for the listing
     * @return { Ext.grid.column.Column }
     */
    createNumberColumn: function() {
        var me = this;

        return Ext.create('Ext.grid.column.Column', {
            header: '{s name=articles/article_number_column}Product number{/s}',
            dataIndex: 'articleDetail.number',
            flex: 1,
            renderer: me.productNumberColumnRenderer
        });
    },

    /**
     * Creates the name column for the listing.
     * @return { Ext.grid.column.Column }
     */
    createNameColumn: function() {
        var me = this;

        return Ext.create('Ext.grid.column.Column', {
            header: '{s name=articles/article_name_column}Product name{/s}',
            dataIndex: 'articleDetail.name',
            flex: 1,
            renderer: me.productNameColumnRenderer
        });
    },

    /**
     * Creates the quantity column for the listing.
     * @return { Ext.grid.column.Column }
     */
    createQuantityColumn: function() {
        return Ext.create('Ext.grid.column.Column', {
            header: '{s name=articles/quantity_column}Quantity{/s}',
            dataIndex: 'quantity',
            flex: 1,
            editor: {
                xtype: 'numberfield',
                allowBlank: false,
                minValue: 1,
                decimalPrecision: 0
            }
        });
    },

    /**
     * Creates the configurable flag column for the listing.
     * @return { Ext.grid.column.Column }
     */
    createIsConfiguratorColumn: function() {
        var me = this;

        return Ext.create('Ext.grid.column.Column', {
            header: '{s name=articles/is_configurator_column}Is configurator{/s}',
            dataIndex: 'isConfigurator',
            flex: 1,
            sortable: false,
            renderer: me.isConfiguratorColumnRenderer
        });
    },

    /**
     * Creates the configurable flag column for the listing.
     * @return { Ext.grid.column.Column }
     */
    createConfigurableColumn: function() {
        var me = this;

        return Ext.create('Ext.grid.column.Column', {
            header: '{s name=articles/configurable}Configurable{/s}',
            dataIndex: 'configurable',
            flex: 1,
            editor: {
                xtype: 'checkbox',
                inputValue: true,
                uncheckedValue: false
            },
            renderer: me.configurableColumnRenderer
        });
    },

    /**
     * Creates a dynamic column for the passed customer group to display
     * the customer group prices in the listing.
     * @param customerGroup
     * @return { Ext.grid.column.Column }
     */
    createCustomerGroupPriceColumn: function(customerGroup) {
        var me = this;

        return Ext.create('Ext.grid.column.Column', {
            header: customerGroup.get('name') + ' ' + '{s name=articles/price_column}price{/s}',
            customerGroup: customerGroup,
            dataIndex: 'customerGroup.id',
            flex: 1,
            renderer: me.customerGroupPriceRenderer,
            summaryType: 'sum',
            // renderer for the summary row
            summaryRenderer: function(value, summaryData, dataIndex) {
                var column = me.bundleController.getColumnByDataIndex(me.columns, dataIndex);
                var price = me.bundleController.getTotalAmountForCustomerGroup(
                    column.customerGroup,
                    me.customerGroupStore
                );
                return '<b>' + Ext.util.Format.number(price) + '</b>';
            }
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
        items.push(me.createOpenProductActionColumnItem());
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
            tooltip: '{s name=articles/delete_article_column}Delete product{/s}',
            handler: function(grid, rowIndex, colIndex, metaData, event, record) {
                me.fireEvent('deleteBundleArticle', [record]);
            }
        };
    },

    /**
     * Creates the open product action column item for the listing action column
     * @return { Object }
     */
    createOpenProductActionColumnItem: function() {
        var me = this;

        return {
            iconCls: 'sprite-inbox--arrow',
            cls: 'open-product',
            tooltip: '{s name=articles/open_article_column}Open product{/s}',
            handler: function(grid, rowIndex, colIndex, metaData, event, record) {
                var productId = record.getArticleDetail().first().get('articleId');
                me.fireEvent('openArticle', productId);
            }
        };
    },

    /**
     * Creates the summary feature for the listing component.
     * @return { Ext.grid.feature.Summary }
     */
    createSummaryFeature: function() {
        return Ext.create('Ext.grid.feature.Summary');
    },

    /**
     * Creates the cell editor plugin for the listing component.
     * @return { Ext.grid.plugin.CellEditing }
     */
    createCellEditor: function() {
        var me = this;

        me.cellEditor = Ext.create('Ext.grid.plugin.CellEditing', {
            clicksToEdit: 1,
            listeners: {
                edit: function(editor, event) {
                    me.fireEvent('changeBundleArticle', event.record);
                }
            }
        });
        return me.cellEditor;
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
        items.push(me.createToolBarProductSearch());
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
     * Creates the product suggest search for the toolbar to add
     * a new product to the bundle.
     * @return { Shopware.form.field.PagingComboBox }
     */
    createToolBarProductSearch: function() {
        var me = this;

        // create an own search store because we need all product prices.
        me.searchStore = Ext.create('Shopware.apps.Article.store.bundle.Search');

        // create the product search component
        me.productSearch = Ext.create('Shopware.form.field.PagingComboBox', me.createProductSearchConfig());

        return me.productSearch;
    },

    /**
     * @return { Object }
     */
    createProductSearchConfig: function() {
        var me = this,
            config = {
                name: 'number',
                fieldLabel: '{s name=articles/add_article_field}Add product{/s}',
                displayField: 'name',
                valueField: 'number',
                returnValue: 'name',
                hiddenReturnValue: 'number',
                disableLoadingSelectedName: true,
                store: me.searchStore,
                labelWidth: 180,
                listeners: {
                    select: function(combo, records) {
                        var record = records[0];
                        if (record instanceof Ext.data.Model) {
                            me.fireEvent('addBundleArticle', record);
                        }
                    }
                }
            };

        config.tpl = Ext.create('Ext.XTemplate',
            '<tpl for=".">',
            '<div class="x-boundlist-item">' +
            // number + data renderer
            ' {literal}<b>{number}</b> - {name}{/literal}' +
            '</div>',
            '</tpl>'
        );

        return config;
    },

    /**
     * Renderer function for the product number column.
     * @return { String }
     */
    productNumberColumnRenderer: function(value, metaData, record) {
        if (record.getArticleDetail() instanceof Ext.data.Store && record.getArticleDetail().first() instanceof Ext.data.Model) {
            return record.getArticleDetail().first().get('number');
        }

        return '';
    },

    /**
     * Renderer function for the product name column.
     * @param value
     * @param metaData
     * @param record
     * @return { String }
     */
    productNameColumnRenderer: function(value, metaData, record) {
        if (record.getArticleDetail() instanceof Ext.data.Store && record.getArticleDetail().first() instanceof Ext.data.Model) {
            return record.getArticleDetail().first().raw.article.name;
        }

        return '';
    },

    /**
     * Renderer function of the isConfigurator column.
     * @param value
     * @param metaData
     * @param record
     */
    isConfiguratorColumnRenderer: function(value, metaData, record) {
        var checked = 'sprite-ui-check-box-uncheck';
        if (record.getArticleDetail() instanceof Ext.data.Store &&
            record.getArticleDetail().first() instanceof Ext.data.Model &&
            record.getArticleDetail().first().raw.article.configuratorSetId > 0
        ) {
            checked = 'sprite-ui-check-box';
        }

        return '<span style="display:block; margin: 0 auto; height:16px; width:16px;" class="' + checked + '"></span>';
    },

    /**
     * Renderer function of the configurable column.
     * @param value
     * @param metaData
     * @param record
     */
    configurableColumnRenderer: function(value, metaData, record) {
        var checked = 'sprite-ui-check-box-uncheck';
        if (record.get('configurable')) {
            checked = 'sprite-ui-check-box';
        }

        return '<span style="display:block; margin: 0 auto; height:16px; width:16px;" class="' + checked + '"></span>';
    },

    /**
     * Renderer function for the customer group price for the selected product variant and the customer group of this
     * columns and the selected quantity.
     * @return { String }
     */
    customerGroupPriceRenderer: function(value, metaData, record, rowIndex, colIndex) {
        var me = this;
        var column = me.columns[colIndex];

        if (record.getArticleDetail() instanceof Ext.data.Store && record.getArticleDetail().first() instanceof Ext.data.Model) {
            var detail = record.getArticleDetail().first();
            var prices = detail.getPrice();
            var quantity = record.get('quantity');

            if (!quantity > 0) {
                quantity = 1;
            }
            if (prices instanceof Ext.data.Store && prices.getCount() > 0) {
                var customerGroupPrice = me.bundleController.getPriceForCustomerGroupAndQuantity(prices, column.customerGroup, quantity);
                if (customerGroupPrice === null) {
                    customerGroupPrice = me.bundleController.getPriceForCustomerGroupAndQuantity(prices, me.customerGroupStore.first(), quantity);
                }
                var price = customerGroupPrice.get('price') * quantity;

                return Ext.util.Format.number(price);
            } else {
                return '';
            }
        } else {
            return '';
        }
    }
});
// {/block}
