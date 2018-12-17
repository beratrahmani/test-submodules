//{namespace name=backend/plugins/b2b_debtor_plugin}
Ext.define("Shopware.apps.b2bOffer.view.detail.detail.Positions", {
    extend: 'Shopware.grid.Panel',
    alias: 'widget.offer-detail-positions-list',
    id: 'b2bOffer-view--detail-positions',

    editMode: [
        'offer_status_declined_admin',
        'offer_status_accepted_user'
    ],

    configure: function () {
        return {
            'addButton': true,
            'deleteButton': false,
            'rowEditing': true,
            'displayProgressOnSingleDelete': false,
            'detailWindow': 'Shopware.apps.b2bOffer.view.detail.Window',
            'pagingbar': false,
            'searchField': false
        };
    },

    createAddButton: function () {
        var me = this;

        if (me.editMode.indexOf(me.record.data.status) < 0) {
            return;
        }

        return me.callParent(arguments);
    },

    initComponent: function () {
        var me = this;

        var parent = me.callParent(arguments);

        me.traceGridEvents();

        return parent;
    },

    createSelectionModel: function () {
        return [];
    },

    priceRenderer: function (value) {
        if (value === Ext.undefined) {
            return value;
        }
        return Ext.util.Format.currency(value) +  " {b2b_currency_symbol}";
    },

    createColumns: function () {
        var me = this;
        me.articleNumberSearch = me.createArticleSearch('number', 'name', 'articleNumber');
        me.articleNameSearch = me.createArticleSearch('name', 'number', 'name');

        var column = [
                {
                    header: '{s name=ReferenceNumber}ReferenceNumber{/s}',
                    flex: 1,
                    dataIndex: 'referenceNumber',
                    editor: me.articleNumberSearch,
                    getSortParam: function () {
                        return 'reference_number';
                    }
                },
                {
                    header:  '{s name=ProductName}Product name{/s}',
                    dataIndex: 'name',
                    editor: me.articleNameSearch,
                    getSortParam: function () {
                        return 'reference_name';
                    }
                },
                {
                    header: '{s name=Quantity}Quantity{/s}',
                    flex: 1,
                    dataIndex: 'quantity',
                    getSortParam: function () {
                        return 'quantity';
                    },
                    editor: {
                        xtype: 'numberfield',
                        allowBlank: false,
                        minValue: 1
                    }
                },
                {
                    header: '{s name=AmountNet}Amount without tax{/s}',
                    flex: 1,
                    dataIndex: 'amountNet',
                    getSortParam: function () {
                        return 'amount_net';
                    },
                    renderer: me.priceRenderer,
                },
                {
                    header: '{s name=DiscountPriceNet}Discount price without tax{/s}',
                    flex: 1,
                    dataIndex: 'discountAmountNet',
                    getSortParam: function () {
                        return 'discount_amount_net';
                    },
                    renderer: me.priceRenderer,
                    editor: {
                        xtype: 'numberfield',
                        allowBlank: false,
                        minValue: 0
                    }
                },
                {
                    header: '{s name=TotalAmountNet}Total amount without tax{/s}',
                    flex: 1,
                    getSortParam: function () {
                        return 'discount_amount_net';
                    },
                    renderer: function (a, table,record) {
                        var amount = Math.round(record.data.discountAmountNet * record.data.quantity * 100) / 100;

                        return Ext.util.Format.currency(amount) +  " {b2b_currency_symbol}"
                    },
                }
            ];


        if (me.editMode.indexOf(me.record.data.status) > 0) {
            column.push(me.createActionColumn());
        }

        return column;
    },

    traceGridEvents: function () {
        var me = this;

        //register listener on the before edit event to set the article name and number manually into the row editor.
        me.rowEditor.on('beforeedit', function(editor, e) {
            var columns = editor.editor.items.items;
            columns[0].setValue(e.record.get('referenceNumber'));
            columns[1].setValue(e.record.get('name'));

            if(e.record.data.maxPurchase) {
                columns[2].setMaxValue(e.record.data.maxPurchase);
            } else {
                columns[2].setMaxValue(null);
            }

            columns[2].setMinValue(e.record.data.minPurchase);
            columns[2].step = e.record.data.purchaseStep;
        });

        me.on('canceledit', function() {
            me.articleNumberSearch.getDropDownMenu().hide();
            me.articleNameSearch.getDropDownMenu().hide();
        }, me);

        me.rowEditor.on('canceledit', function(grid, eOpts) {
           me.onCancelEdit(grid, eOpts);
        });

         me.articleNumberSearch.on('valueselect', function(field, value, hiddenValue, record) {
             me.onArticleSelect(me.rowEditor, value, record);
         });

         me.articleNameSearch.on('valueselect', function(field, value, hiddenValue, record) {
             me.onArticleSelect(me.rowEditor, value, record);
         });

        me.rowEditor.on('edit' ,function (that, e) {
            me.fireEvent('savePosition', e, me);
        });

        me.rowEditor.on('validateedit' ,function (editor, event, opts) {
            if(!event.newValues.name) {

                return false;
            }
            if(!event.newValues.referenceNumber) {

                return false;
            }

            if(!event.newValues.quantity) {

                return false;
            }

            if(!me.fireEvent('validateQuantity', event, me)) {

                return false;
            }

            return true;
        });
    },

     onArticleSelect: function(editor, value, record) {
         var me = this,
             columns = editor.editor.items.items,
             updateButton = editor.editor.floatingButtons.items.items[0];

         me.fireEvent('selectedArticle', record.get('number'), columns[2].value, function (reference) {
             columns[2].setValue(reference['quantity']);
             columns[2].setMinValue(reference['minPurchase']);
             columns[2].setMaxValue(reference['maxPurchase']);
             columns[2].step = reference['purchaseStep'];
             columns[3].setValue(reference['amountNet']);
             columns[4].setValue(reference['amountNet']);
             me.store.getAt(me.store.getCount() - 1).set('amountNet', reference['amountNet']);
         });

         columns[0].setValue(record.get('number'));
         columns[1].setValue(record.get('name'));
     },

    _onDelete: function (view, rowIndex, colIndex, item, opts, record) {
        var me = this;

        me.fireEvent('deletePosition', me, rowIndex, record, me);
    },

    _onEdit: function (view, rowIndex, colIndex, item, opts, record) {
        var me = this,
            editor = me.plugins[0];

        editor.startEdit(record, 0);
    },

    createDeleteColumn: function () {
        var me = this, column;

        column = {
            action: 'delete',
            iconCls: 'sprite-minus-circle-frame',
            handler: Ext.bind(me._onDelete, me),
            tooltip: '{s name=DeletePosition}Delete position{/s}',
        };

        me.fireEvent(me.eventAlias + '-delete-action-column-created', me, column);

        return column;
    },

    createEditColumn: function () {
        var me = this, column;

        column = {
            action: 'edit',
            iconCls: 'sprite-pencil',
            handler: Ext.bind(me._onEdit, me),
            tooltip: '{s name=EditPosition}Edit position{/s}',
        };

        me.fireEvent(me.eventAlias + '-edit-action-column-created', me, column);

        return column;
    },

    onAddItem: function() {
        var me = this;

        me.rowEditor.cancelEdit();

        var position = Ext.create('Shopware.apps.b2bOffer.model.Positions', {
            offerId: me.record.get('id'),
            quantity: 1,
            statusId: 0
        });

        me.store.add(position);
        me.rowEditor.startEdit(position, 0);
    },

    onCancelEdit: function(grid, eOpts) {
        var record = eOpts.record,
            store = eOpts.store;

        if (!(record instanceof Ext.data.Model) || !(store instanceof Ext.data.Store)) {
            return;
        }

        if (!record.get('referenceNumber')) {
            store.remove(record);
        }
    },

    createArticleSearch: function(returnValue, hiddenReturnValue, name ) {
        return Ext.create('Shopware.form.field.ArticleSearch', {
            name: name,
            returnValue: returnValue,
            hiddenReturnValue: hiddenReturnValue,
            articleStore: Ext.create('Shopware.apps.Base.store.Variant'),
            allowBlank: false,
            getValue: function() {
                return this.getSearchField().getValue();
            },
            setValue: function(value) {
                this.getSearchField().setValue(value);
            }
        });
    }
});
