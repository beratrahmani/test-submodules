//{namespace name=backend/swag_advanced_cart/view/main}

Ext.define('Shopware.apps.SwagAdvancedCart.view.detail.Cart', {
    extend: 'Shopware.grid.Panel',
    alias: 'widget.cart-listing-grid',
    region: 'center',

    configure: function () {
        return {
            detailWindow: 'Shopware.apps.SwagAdvancedCart.view.detail.Window',

            columns: {
                articleOrderNumber: '{s name="detail/cart/number"}Bestellnummer{/s}',
                name: { header: '{s name="detail/cart/article"}Artikel{/s}', flex: 3 },
                quantity: '{s name="detail/cart/quantity"}Anzahl{/s}',
                price: '{s name="detail/cart/price"}Einzelpreis{/s}',
                sumPrice: '{s name="detail/cart/sumprice"}Gesamtpreis{/s}'
            },

            addButton: false,
            editColumn: false
        };
    },

    createActionColumnItems: function () {
        var me = this,
            items = me.callParent(arguments);

        items.push({
            action: 'notice',
            iconCls: 'sprite-inbox',
            tooltip: '{s name="list/cart/openarticle"}Artikel aufrufen{/s}',
            handler: function (view, rowIndex, colIndex, item, opts, record) {

                Shopware.app.Application.addSubApplication({
                    name: 'Shopware.apps.Article',
                    action: 'detail',
                    params: {
                        articleId: record.get('articleId')
                    }
                });
            }
        });
        return items;
    }
});
