//

//{namespace name="backend/advisor/main"}
//{block name="backend/advisor/view/details/ui/stream-grid"}
Ext.define('Shopware.apps.Advisor.view.details.ui.StreamGrid', {
    extend: 'Ext.grid.Panel',
    title: '{s name=tabs_stream_grid_title}Preview{/s}',
    alias: 'widget.product-stream-preview-grid',
    //region: 'center',
    flex: 5,
    width: '100%',
    margin: '0px 20px 20px 20px',

    snippets: {
        shop: '{s name="tabs_stream_shop"}Shop{/s}',
        currency: '{s name="tabs_stream_currency"}Currency{/s}',
        customerGroup: '{s name="tabs_stream_customer_group"}Customer group{/s}',
        shopComboFieldLabel: '{s name=tabs_stream_shop}Shop{/s}',
        customerComboFieldLabel: '{s name=tabs_stream_customer_group}Customer group{/s}',
        currencyComboFieldLabel: '{s name=tabs_stream_currency}Currency{/s}',
        columns: {
            number: '{s name="tabs_stream_col_number"}Number{/s}',
            name: '{s name="tabs_stream_col_name"}Name{/s}',
            stock: '{s name="tabs_stream_col_stock"}Stock{/s}',
            price: '{s name="tabs_stream_col_price"}Price{/s}'
        }
    },

    /**
     * @overwrite
     */
    initComponent: function () {
        var me = this;

        me.store = Ext.create('Shopware.apps.Advisor.store.StreamPreview');
        me.store.on('beforeload', me.onBeforeLoadStore, me);

        me.pagingbar = Ext.create('Ext.toolbar.Paging', {
            store: me.store,
            dock: 'bottom',
            displayInfo: true
        });

        me.toolbar = Ext.create('Shopware.apps.Advisor.view.details.ui.Context');
        me.columns = me.createColumns();
        me.dockedItems = [me.toolbar, me.pagingbar];

        me.callParent(arguments);
    },

    /**
     * @param { Ext.data.Store } store
     */
    onBeforeLoadStore: function (store) {
        var me = this;
        store.getProxy().setExtraParam('shopId', me.toolbar.getShopValue());
        store.getProxy().setExtraParam('currencyId', me.toolbar.getCurrencyValue());
        store.getProxy().setExtraParam('customerGroupKey', me.toolbar.getCustomerValue());
    },

    /**
     * @returns { *[] }
     */
    createColumns: function () {
        var me = this;

        return [{
            header: me.snippets.columns.number,
            width: 110,
            dataIndex: 'number'
        }, {
            header: me.snippets.columns.name,
            flex: 1,
            dataIndex: 'name'
        }, {
            header: me.snippets.columns.stock,
            width: 80,
            dataIndex: 'stock'
        }, {
            header: me.snippets.columns.price,
            dataIndex: 'cheapestPrice',
            renderer: this.priceRenderer
        }];
    },

    /**
     * @param { * } value
     * @returns { * }
     */
    priceRenderer: function (value) {
        if (!Ext.isObject(value)) {
            return '';
        }

        if (!value.hasOwnProperty('calculatedPrice')) {
            return '';
        }

        return value.calculatedPrice;
    }
});
//{/block}