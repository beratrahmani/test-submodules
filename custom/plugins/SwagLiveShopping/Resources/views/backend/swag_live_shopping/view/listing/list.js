//

// {namespace name="backend/live_shopping/live_shopping/view/main"}
// {block name="backend/swag_live_shopping/overview/list"}
Ext.define('Shopware.apps.SwagLiveShopping.view.listing.List', {
    extend: 'Shopware.grid.Panel',
    alias: 'widget.swag-live-shopping-list',

    region: 'center',

    snippets: {
        columns: {
            name: '{s name="liveshopping/list/name_column"}Liveshopping name{/s}',
            articleName: '{s name="liveshopping/list/product_name_column"}Product name{/s}',
            active: '{s name="liveshopping/list/active_column"}Active{/s}',
            type: '{s name="liveshopping/list/typ_column"}Type{/s}',
            number: '{s name="liveshopping/list/product_number_column"}Product number{/s}',
            validFrom: '{s name="liveshopping/list/valid_from_column"}Valid from{/s}',
            validTo: '{s name="liveshopping/list/valid_to"}Valid to{/s}',
            createdAt: '{s name="liveshopping/list/created_at_column"}Created{/s}'
        }
    },

    /**
     * init the List
     */
    initComponent: function () {
        var me = this;

        me.callParent(arguments);
        me.registerEvents();
        me.priceWindow = null;

        me.on('select', Ext.bind(me.onSelectRow, me));
    },

    /**
     * register all events for the controller
     */
    registerEvents: function () {
        this.addEvents(
            'openProduct'
        );
    },

    /**
     * @returns { Object }
     */
    configure: function () {
        var me = this;

        return {
            columns: me.getColumns(),
            addButton: false,
            deleteButton: false,
            deleteColumn: false,
            editColumn: false
        };
    },

    /**
     * @returns { Object }
     */
    getColumns: function () {
        var me = this;

        return {
            name: { flex: 2, header: me.snippets.columns.name },
            articleName: { flex: 2, header: me.snippets.columns.articleName },
            active: { width: 42, header: me.snippets.columns.active },
            type: { flex: 1, header: me.snippets.columns.type },
            number: { flex: 1, header: me.snippets.columns.number },
            validFrom: { flex: 1, header: me.snippets.columns.validFrom },
            validTo: { flex: 1, header: me.snippets.columns.validTo },
            created: { flex: 1, header: me.snippets.columns.createdAt }
        };
    },

    /**
     * create a extra actionColumnItem to open the product detail page
     *
     * @overwrite
     * @return { Array }
     */
    createActionColumnItems: function () {
        var me = this,
            items = me.callParent(arguments),
            openProductColumn = {
                iconCls: 'sprite-inbox--arrow',
                handler: Ext.bind(me.onOpenProduct, me)
            };

        items.push(openProductColumn);

        return items;
    },

    /**
     * On select a row load the selectd priceDetails in the Price grid.
     *
     * @param { Ext.grid.Panel } grid
     * @param { Ext.data.Model } record
     */
    onSelectRow: function (grid, record) {
        var me = this,
            window = me.up('window');

        window.liveShoppingInstance = record;
        window.priceGrid.reconfigure(record.getPrices());
    },

    /**
     * the handler to fire a event for open the product detail page
     *
     * @param { Ext.grid.Panel } grid
     * @param { int } rowIndex
     * @param { int } colIndex
     * @param { object } metaData
     * @param { event } event
     * @param { Ext.data.Model } record
     */
    onOpenProduct: function (grid, rowIndex, colIndex, metaData, event, record) {
        var me = this,
            articleId = record.get('articleId');

        me.fireEvent('openProduct', articleId);
    }
});
// {/block}
