//

// {namespace name="backend/live_shopping/live_shopping/view/main"}
// {block name="backend/swag_live_shopping/listing/window"}
Ext.define('Shopware.apps.SwagLiveShopping.view.listing.Window', {
    extend: 'Shopware.window.Listing',
    alias: 'widget.swag-live-shopping-window',

    layout: 'border',
    height: 550,
    width: '70%',

    snippets: {
        title: '{s name="liveShopping/window/title"}Liveshopping{/s}',
        priceTitle: '{s name=liveshopping/window/liveshopping_details}Liveshopping Details{/s}',
        columns: {
            nameColumn: '{s name=liveshopping/prices/customer_group_column}Kundengruppen{/s}',
            productPriceColumn: '{s name=liveshopping/prices/summarized_product_price_column}Produktpreis{/s}',
            endPriceColumn: '{s name=liveshopping/prices/end_price}Endpreis{/s}',
            pricePerMinuteColumn: '{s name=liveshopping/prices/per_minute}Pro Minute{/s}'
        }
    },

    /**
     * @return { Object }
     */
    configure: function () {
        var me = this;
        me.title = me.snippets.title;

        return {
            listingGrid: 'Shopware.apps.SwagLiveShopping.view.listing.List',
            listingStore: 'Shopware.apps.SwagLiveShopping.store.Main'
        };
    },

    /**
     * create the items
     * @returns { Array }
     */
    createItems: function () {
        var me = this,
            items = me.callParent(arguments);

        items.push(me.createPricePanel());

        return items;
    },

    /**
     * Create the PricePanel
     * @returns { Ext.panel.Panel }
     */
    createPricePanel: function () {
        var me = this;

        return Ext.create('Ext.panel.Panel', {
            region: 'east',
            title: me.snippets.priceTitle,
            collapsible: true,
            width: 400,
            items: me.createPriceItems()
        });
    },

    /**
     * Create the items
     * @returns { Array }
     */
    createPriceItems: function () {
        var me = this;

        return [me.createPriceGrid()];
    },

    /**
     * create the panel
     * @returns { Ext.grid.Panel }
     */
    createPriceGrid: function () {
        var me = this;

        me.priceGrid = Ext.create('Ext.grid.Panel', {
            columns: me.createColumns(),
            store: me.createEmptyStore()
        });

        return me.priceGrid;
    },

    /**
     * Create the Columns
     * @returns { Ext.grid.column.Column[] }
     */
    createColumns: function () {
        var me = this;

        return [
            me.createNameColumn(),
            me.createPriceColumn(),
            me.createEndPriceColumn(),
            me.createPerMinuteColumn()
        ];
    },

    /**
     * Creates the name column for the listing.
     * @return { Ext.grid.column.Column }
     */
    createNameColumn: function () {
        var me = this;

        return Ext.create('Ext.grid.column.Column', {
            header: me.snippets.columns.nameColumn,
            dataIndex: 'customerGroupName',
            flex: 1
        });
    },

    /**
     * Creates the end price column for the listing
     * @return { Ext.grid.column.Column }
     */
    createEndPriceColumn: function () {
        var me = this;

        return Ext.create('Ext.grid.column.Column', {
            header: me.snippets.columns.endPriceColumn,
            dataIndex: 'endprice',
            flex: 1,
            renderer: me.priceRenderer
        });
    },

    /**
     * Creates the end price column for the listing
     * @return { Ext.grid.column.Column }
     */
    createPerMinuteColumn: function () {
        var me = this;

        return Ext.create('Ext.grid.column.Column', {
            header: me.snippets.columns.pricePerMinuteColumn,
            dataIndex: 'perMinute',
            flex: 1,
            renderer: Ext.bind(me.perMinuteColumnRenderer, me)
        });
    },

    /**
     * Creates the price column for the listing
     * @return { Ext.grid.column.Column }
     */
    createPriceColumn: function () {
        var me = this;

        return Ext.create('Ext.grid.column.Column', {
            header: me.snippets.columns.productPriceColumn,
            dataIndex: 'price',
            flex: 1,
            renderer: me.priceRenderer
        });
    },

    /**
     * Renderer function for the per minute column.
     * @param value
     * @param metaData
     * @param record
     */
    perMinuteColumnRenderer: function (value, metaData, record) {
        var me = this;

        var price = me.getPerMinute(record);

        return Ext.util.Format.currency(price, '€', 5, true);
    },

    /**
     * Helper function to calculate the per minute discount/surcharges for the passed
     * price.
     * @param record
     * @return Number
     */
    getPerMinute: function (record) {
        var me = this;
        var perMinute = 0;
        var timeDiff = me.getTimeDiff(me.liveShoppingInstance);

        if (!Ext.isNumeric(record.get('price')) || !Ext.isNumeric(record.get('endprice')) ||
            record.get('price') < 0 || record.get('endprice') < 0) {
            return 0;
        }

        if (timeDiff === 0) {
            return record.get('endPrice');
        }
        var priceDiff = 0;
        switch (me.liveShoppingInstance.get('type')) {
            // standard live shopping product (fix price)
            case 1:
                perMinute = record.get('endprice');
                break;
            // discount per minute
            case 2:
                priceDiff = record.get('price') - record.get('endprice');
                perMinute = priceDiff / timeDiff;
                break;
            // surcharge per minute
            case 3:
                priceDiff = record.get('endprice') - record.get('price');
                perMinute = priceDiff / timeDiff;
                break;
            default:
                perMinute = record.get('endprice');
                break;
        }

        return perMinute;
    },

    /**
     * Helper function to get the time diff of the current selected
     * dates/times in the configuration panel.
     * Returns the minutes between the both dates.
     * @return Number
     */
    getTimeDiff: function (record) {
        // time calcualtion
        var validFrom = record.get('validFrom');
        var validTo = record.get('validTo');
        var validFromTime = record.get('validFrom');
        var validToTime = record.get('validTo');

        if (!(Ext.isDate(validFrom)) ||
            !(Ext.isDate(validTo)) ||
            !(Ext.isDate(validFromTime)) ||
            !(Ext.isDate(validToTime))) {
            return 0;
        }

        validFrom.setHours(validFromTime.getHours());
        validFrom.setMinutes(validFromTime.getMinutes());

        validTo.setHours(validToTime.getHours());
        validTo.setMinutes(validToTime.getMinutes());

        // timeDiff property contains now the minute value between the two date objects
        return (validTo.getTime() - validFrom.getTime()) / 1000 / 60;
    },

    /**
     * Format price to €
     * @param { number } value
     * @returns { number }
     */
    priceRenderer: function (value) {
        return Ext.util.Format.currency(value, '€', 2, true);
    },

    /**
     * Create a empty store for initial the grid
     * @returns { Ext.data.ArrayStore }
     */
    createEmptyStore: function () {
        return new Ext.data.ArrayStore({
            autoLoad: false
        });
    }
});
// {/block}
