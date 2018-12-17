//{namespace name=backend/swag_advanced_cart/view/main}

Ext.define('Shopware.apps.SwagAdvancedCart.view.list.Cart', {
    extend: 'Shopware.grid.Panel',
    alias: 'widget.cart-listing-grid',
    region: 'center',

    configure: function () {
        var me = this;

        return {
            detailWindow: 'Shopware.apps.SwagAdvancedCart.view.detail.Window',

            columns: {
                name: {
                    header: '{s name="list/cart/name"}Wunschlisten Name{/s}',
                    renderer: me.renderNameColumn
                },
                customer: '{s name="list/cart/customer"}Kunde{/s}',
                modified: {
                    header: '{s name="list/cart/modified"}Letzte Änderung{/s}',
                    renderer: me.dateColumn
                },
                cartItems: '{s name="list/cart/items"}Artikel{/s}',
                published: {
                    header: '{s name="list/cart/publish"}Veröffentlicht{/s}',
                    renderer: me.activeColumnRenderer
                },
                shopId: {
                    header: '{s name="list/cart/shop"}Shop{/s}',
                    renderer: me.shopColumnRenderer
                }
            },
            addButton: false
        };
    },

    /**
     * Formats the date column
     *
     * @param value
     * @return [string] - The passed value, formatted with Ext.util.Format.date()
     */
    dateColumn: function (value) {
        if (value == '0000-00-00 00:00:00') {
            return '';
        }
        if (value === Ext.undefined) {
            return value;
        }

        if (typeof timeFormat === 'undefined') {
            var timeFormat = 'H:i';
        }

        return Ext.util.Format.date(value) + ' ' + Ext.util.Format.date(value, timeFormat);
    },

    /**
     * @param { boolean } value
     * @return { string }
     */
    activeColumnRenderer: function (value) {
        if (value) {
            return '<div class="sprite-tick-small"  style="width: 25px; height: 25px">&nbsp;</div>';
        }

        return '<div class="sprite-cross-small" style="width: 25px; height: 25px">&nbsp;</div>';
    },

    /**
     * @param { string } value
     * @param { object } metaData
     * @param { Ext.data.Model } record
     * @return { string }
     */
    shopColumnRenderer: function (value, metaData, record) {
        return record.getShop().first().get('name');
    },

    /**
     * @param { string } value
     * @param { object } metaData
     * @param { Ext.data.Model } record
     * @return { string }
     */
    renderNameColumn: function(value, metaData, record) {
        if (record.raw.isSessionCart) {
            return '<span style="color:#999">' + value + '</span>';
        }
        
        return value;
    },

    /**
     * @override
     */
    createActionColumnItems: function () {
        var me = this,
            items = me.callParent(arguments);

        items.push({
            action: 'notice',
            iconCls: 'sprite-user',
            tooltip: '{s name="list/cart/opencustomer"}Kundenkonto aufrufen{/s}',
            handler: function (view, rowIndex, colIndex, item, opts, record) {

                Shopware.app.Application.addSubApplication({
                    name: 'Shopware.apps.Customer',
                    action: 'detail',
                    params: {
                        customerId: record.get('customerId')
                    }
                });
            }
        });
        return items;
    }
});
