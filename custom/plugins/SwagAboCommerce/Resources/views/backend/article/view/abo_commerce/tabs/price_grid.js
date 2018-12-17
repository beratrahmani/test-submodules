
// {namespace name="backend/abo_commerce/article/view/main"}
// {block name="backend/abo_commerce/view/tabs/price_grid"}
Ext.define('Shopware.apps.AboCommerce.view.tabs.PriceGrid', {

    extend: 'Ext.grid.Panel',

    alias: 'widget.article-price-grid',

    sortableColumns: false,
    plugins: [{
        ptype: 'cellediting',
        clicksToEdit: 1
    }],

    /**
     * Contains all snippets for this view
     * @object
     */
    snippets: {
        columnFrom: '{s name=price/column_from}Duration from{/s}',
        columnTo: '{s name=price/column_to}Duration to{/s}',
        columnDiscountPercent: '{s name=price/column_discount_percent}Discount percent{/s}',
        columnDiscountAbsolute: '{s name=price/column_discount_absolute}Discount absolute{/s}',
        columnPrice: '{s name=price/column_discount_price}Price{/s}',

        buttonCopyDefaultDiscounts: '{s name=price/button_copy_default_discounts}Copy default discounts{/s}',

        unitWeeks: '{s name=price/unit_weeks}Weeks{/s}',
        unitMonths: '{s name=price/unit_months}Months{/s}',

        tabGross: '{s name=price/tabGross}Gross{/s}',
        tabNet: '{s name=price/tabNet}Net{/s}'
    },

    initComponent: function() {
        var me = this;

        me.store = me.priceStore;
        me.columns = me.getColumns();

        var title = me.snippets.tabNet;
        if (me.customerGroup.get('taxInput')) {
            title = me.snippets.tabGross;
        }

        me.title = Ext.String.format('[0] ([1])', me.customerGroup.get('name'), title);

        me.copyDefaultDiscountsButton = Ext.create('Ext.Button', {
            disabled: true,
            text: me.snippets.buttonCopyDefaultDiscounts,
            iconCls: 'sprite-document-copy',
            handler: function() {
                me.fireEvent('copyDefaultDiscounts', me.priceStore, me.customerGroup, me.customerGroupStore, this);
            }
        });

        me.tbar = {
            xtype: 'toolbar',
            ui: 'shopware-ui',
            cls: 'shopware-toolbar',
            dock: 'top',
            items: [ me.copyDefaultDiscountsButton ]
        };

        me.callParent(arguments);
    },

    /**
     * Creates the elements for the description field set.
     * @return Array Contains all Ext.form.Fields for the description field set
     */
    getColumns: function () {
        var me = this;

        return [
            {
                header: me.snippets.columnFrom,
                dataIndex: 'durationFrom',
                flex: 1,
                editor: {
                    xtype: 'numberfield',
                    minValue: 0,
                    decimalPrecision: 0
                },
                renderer: function(v) {
                    var unit = me.record.get('durationUnit');

                    if (unit === 'weeks') {
                        unit = me.snippets.unitWeeks;
                    } else {
                        unit = me.snippets.unitMonths;
                    }

                    return v + ' ' + unit;
                }
            }, {
                header: me.snippets.columnTo,
                xtype: 'numbercolumn',
                flex: 1,
                dataIndex: 'to',
                editor: {
                    xtype: 'numberfield',
                    decimalPrecision: 2,
                    minValue: 0
                },
                renderer: function(value, metaData, record, rowIndex, colIndex, store) {
                    var nextRecord = store.getAt(rowIndex + 1);

                    var unit = me.record.get('durationUnit');

                    if (unit === 'weeks') {
                        unit = me.snippets.unitWeeks;
                    } else {
                        unit = me.snippets.unitMonths;
                    }

                    if (nextRecord) {
                        return nextRecord.get('durationFrom') - 1 + ' ' + unit;
                    } else {
                        return '-';
                    }
                }
            }, {
                xtype: 'numbercolumn',
                flex: 1,
                header: me.snippets.columnDiscountPercent,
                dataIndex: 'discountPercent',
                editor: {
                    xtype: 'numberfield',
                    decimalPrecision: 2,
                    minValue: 0,
                    maxValue: 99
                },
                renderer: function(v) {
                    if (!Ext.isNumeric(v)) {
                        return '-';
                    }
                    return Ext.util.Format.number(v) + ' %';
                }
            }, {
                xtype: 'numbercolumn',
                flex: 1,
                header: me.snippets.columnDiscountAbsolute,
                dataIndex: 'discountAbsolute',
                editor: {
                    xtype: 'numberfield',
                    decimalPrecision: 2,
                    minValue: 0
                },
                renderer: function(v) {
                    if (!Ext.isNumeric(v)) {
                        return '-';
                    }
                    return Ext.util.Format.number(v) + ' €';
                }
            }, {
                xtype: 'numbercolumn',
                flex: 1,
                header: me.snippets.columnPrice,
                renderer: function(value, metaData, record) {
                    var price = me.basePrice;

                    if (record.get('discountAbsolute') > 0) {
                        price = me.basePrice - record.get('discountAbsolute');
                    } else if (record.get('discountPercent')) {
                        price = me.basePrice - (me.basePrice / 100 * record.get('discountPercent'));
                    }

                    return Ext.util.Format.number(price) + ' €';
                }
            }, {
                xtype: 'actioncolumn',
                width: 25,
                items: [
                    {
                        iconCls: 'sprite-minus-circle-frame',
                        action: 'delete',
                        tooltip: 'delete',
                        handler: function (view, rowIndex, colIndex, item, opts, record) {
                            var store = view.getStore();
                            store.remove(record);

                            if (store.count() === 0) {
                                me.copyDefaultDiscountsButton.enable();
                            }

                            me.reconfigure(store);
                            me.fireEvent('removePrice', record, view, rowIndex);
                        },

                        /**
                         * @param value
                         * @param metadata
                         * @param record
                         * @return string
                         * @param rowIdx
                         * @param colIdx
                         * @param store
                         */
                        getClass: function(value, metadata, record, rowIdx, colIdx, store) {
                            if (rowIdx === 0 && me.isDefault) {
                                return 'x-hidden';
                            }

                            if (rowIdx === 0 && store.count() > 1) {
                                return 'x-hidden';
                            }
                        }
                    }
                ]
            }
        ];
    }
});
// {/block}
