
//{namespace name="backend/swag_promotion/snippets"}
//{block name="backend/swag_promotion/view/list//list"}
Ext.define('Shopware.apps.SwagPromotion.view.list.List', {
    extend: 'Shopware.grid.Panel',
    alias: 'widget.swag-promotion-listing-grid',
    region: 'center',

    configure: function () {
        return {
            detailWindow: 'Shopware.apps.SwagPromotion.view.detail.Window',
            rowEditing: true,
            columns: {
                name: {
                    header: '{s name=listColumnsName}Name{/s}',
                    flex: 3,
                    editor: undefined
                },

                type: {
                    header: '{s name=listColumnsType}Promotion mode{/s}',
                    renderer: function (value) {
                        var me = this;
                        return me.translateType(value)
                    },
                    flex: 3,
                    editor: undefined
                },

                active: {
                    header: '{s name=listColumnsActive}Active{/s}',
                    flex: 1
                },

                amount: {
                    header: '{s name=listColumnsAmount}Amount{/s}',
                    renderer: function (value, style, row) {
                        var me = this;
                        return me.addPostFix(value, row)
                    },
                    flex: 2
                },

                priority: {
                    header: '{s name=listColumnsPriority}Priority{/s}',
                    flex: 1,
                    editor: undefined
                },

                stopProcessing: {
                    header: '{s name=listColumnsStopProcessing}Stop processing{/s}',
                    tooltip: "{s name=discountSettingsFieldLabelStopProcessing}Exclude promotions with lower priority{/s}",
                    flex: 1,
                    editor: undefined
                },

                validFrom: {
                    header: '{s name=activeFrom}active from{/s}',
                    flex: 2,
                    format: 'Y-m-d',
                    editor: undefined
                },

                validTo: {
                    header: '{s name=activeTo}active to{/s}',
                    flex: 2,
                    format: 'Y-m-d',
                    editor: undefined
                },

                orders: {
                    header: '{s name=listOrders}Orders{/s}',
                    flex: 2,
                    editor: undefined
                }
            }
        };
    },

    /**
     * @Override
     */
    createActionColumnItems: function () {
        var me = this,
            items = me.callParent(arguments);

        items.push(me.createDuplicateColumn());

        return items;
    },

    /**
     * @Override
     */
    createPlugins: function () {
        var me = this, items = [], item;

        if (me.getConfig('rowEditing')) {
            me.rowEditor = Ext.create('Ext.grid.plugin.RowEditing', {
                clicksToEdit: 2,
                listeners: {
                    edit: function (editor, context, eOpts) {
                        var data = context.record.data;
                        me.saveRowEditData(me, data);
                    }
                }
            });
            items.push(me.rowEditor)
        }

        return items;
    },

    /**
     * Set some default values for new promotions
     *
     * @Override
     */
    createNewRecord: function () {
        var me = this,
            record = me.callParent(arguments);

        record.set('name', '{s name="newPromotionPlaceholder"}My new promotion{/s}');
        record.getRuleStore = Ext.create('Ext.data.Store', {
            model: 'Shopware.apps.SwagPromotion.model.Rules',
            data: [
                {
                    stackMode: 'detail',
                    type: 'basket.absolute'
                }
            ]
        });

        return record;
    },

    saveRowEditData: function (me, data) {
        var url = '{url controller=SwagPromotion action=saveRowEditingData module=backend}';
        me.ajaxCall(me, url, data);
    },

    translateType: function (value) {
        switch (value) {
            case 'basket.absolute':
                return '{s name="basketAbsoluteDiscount"}Cart: Absolute discount{/s}';
            case 'basket.percentage':
                return '{s name="basketPercentDiscount"}Cart: Percentage discount{/s}';
            case 'basket.shippingfree':
                return '{s name="discountSettingsFieldLabelShippingFree"}Shipping free{/s}';
            case 'product.absolute':
                return '{s name="productAbsoluteDiscount"}Products: Absolute discount{/s}';
            case 'product.percentage':
                return '{s name="productPercentDiscount"}Products: Percentage discount{/s}';
            case 'product.buyxgetyfree':
                return '{s name="buyxgetyfree"}Buy X get Y for free{/s}';
            case 'product.freegoods':
                return '{s name="freeGoodsDiscount"}Free goods{/s}';
        }
    },

    addPostFix: function (value, row) {
        var type = row.data.type;
        switch (type) {
            case 'basket.percentage':
            case 'product.percentage':
                return value + ' %';
            case 'product.freegoods':
                return '';
            default:
                return value;
        }
    },

    createDeleteColumn: function () {
        var me = this, column;

        column = {
            action: 'delete',
            iconCls: 'sprite-minus-circle-frame',
            handler: function (view, rowIndex, colIndex, item, opts, record) {
                me.createMessageBox(me, record, "delete");
            }
        };

        me.fireEvent(me.eventAlias + '-delete-action-column-created', me, column);

        return column;
    },

    createDuplicateColumn: function () {
        var me = this;

        return {
            action: 'duplicatePromotion',
            iconCls: 'sprite-duplicate-article',
            handler: function (view, rowIndex, colIndex, item, opts, record) {
                me.createMessageBox(me, record, "duplicate");
            }
        };
    },

    createMessageBox: function (me, record, column) {
        var url = '',
            msg = '',
            title = '';

        if (column == "duplicate") {
            url = "{url controller=SwagPromotion action=duplicateRow module=backend}";
            msg = '{s name="duplicate"}Do you really want to create a Clone of this entry?{/s}';
            title = '{s name="duplicateTitle"}Create clone{/s}';
        } else {
            url = "{url controller=SwagPromotion action=deleteRow module=backend}";
            msg = '{s name="deleteRow"}Do you really want to delete this entry?{/s}';
            title = '{s name="deleteRowTitle"}Delete promotion{/s}';
        }

        Ext.Msg.show({
            title: title,
            msg: msg,
            buttons: Ext.Msg.YESNO,
            icon: Ext.Msg.QUESTION,
            fn: function (btn) {
                if (btn == 'yes') {
                    me.ajaxCall(me, url, { id: record.data.id });
                }
            }
        });
    },

    ajaxCall: function (me, url, transportData) {
        Ext.Ajax.request({
            url: url,
            params: {
                transportData: Ext.JSON.encode(transportData)
            },
            success: function (response) {
                var result = Ext.JSON.decode(response.responseText);
                if (result.success) {
                    me.store.reload();
                }
            }
        });

    }
});
//{/block}
