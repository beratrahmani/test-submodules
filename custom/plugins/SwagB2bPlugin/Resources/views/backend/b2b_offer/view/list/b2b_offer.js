//{namespace name=backend/plugins/b2b_debtor_plugin}
Ext.define('Shopware.apps.b2bOffer.view.list.B2bOffer', {
    extend: 'Shopware.grid.Panel',
    alias: 'widget.offer-listing-grid',
    region: 'center',

    offerStatus: {
        'offer_status_open': '{s name=offer_status_open}Open{/s}',
        'offer_status_accepted_user': '{s name=offer_status_accepted_user}Sent to the admin by user{/s}',
        'offer_status_accepted_admin': '{s name=offer_status_accepted_admin}Sent to the user by admin{/s}',
        'offer_status_declined_user': '{s name=offer_status_declined_user}Declined by user{/s}',
        'offer_status_declined_admin': '{s name=offer_status_declined_admin}Declined by admin{/s}',
        'offer_status_expired': '{s name=offer_status_expired}Expired{/s}',
        'offer_status_accepted_both': '{s name=offer_status_accepted_both}Accepted by both{/s}',
        'offer_status_converted': '{s name=offer_status_converted}Converted{/s}',
    },

    notEditMode: [
        'offer_status_open',
    ],

    notDeleteMode: [
        'offer_status_open',
        'offer_status_accepted_user',
        'offer_status_declined_admin',
        'offer_status_expired'
    ],

    sorting: {
        'id': 'id',
        'debtorEmail': 'debtor_email',
        'createdAt': 'created_at',
        'changedStatusAt': 'changed_status_at',
        'expiredAt': 'expired_at',
        'discountAmountNet': 'discount_amount_net',
        'listPositionCount': 'line_item_reference_count'
    },

    configure: function () {
        return {
            rowEditing: false,
            displayProgressOnSingleDelete: false,
            toolbar: false,
            detailWindow: 'Shopware.apps.b2bOffer.view.detail.Window',
            columns: {
                debtorEmail: {
                    header: '{s name=OfferGridDebtorEmail}Debtor Email{/s}'
                },
                debtorCompany: {
                    header: '{s name=DebtorCompany}Company{/s}',
                    minWidth: 100
                },
                status : {
                    header: '{s name=Status}Status{/s}',
                    renderer: this.offerStatusRenderer,
                    minWidth: 175
                },
                createdAt: {
                    header: '{s name=Created}Creation date{/s}'
                },
                changedStatusAt: {
                    header: '{s name=Changed}Change date{/s}'
                },
                expiredAt: {
                    header: '{s name=Expired}Expiry date{/s}'
                },
                discountAmountNet: {
                    header: '{s name=DiscountAmountNet}Discount amount without tax{/s}',
                    renderer: this.priceRenderer
                },
                listPositionCount: {
                    header: '{s name=OrderItemQuantity}Order item quantity{/s}',
                }
            }
        };
    },

    createColumns: function () {
        var me = this,
            columns = me.callParent();
        
        me.addSortNames(columns);

        columns.unshift({ xtype: 'gridcolumn', name: 'id', dataIndex: 'id', width: 20, header: 'id' });

        return columns;
    },

    createActionColumnItems: function () {
        var me = this,
            items = me.callParent();

        items.unshift(me.createDebtorColumn());
        
        return items;
    },

    createDebtorColumn: function () {
        var me = this;

        return {
            iconCls: 'sprite-user',
            tooltip: '{s name=OpenDebtor}Open debtor{/s}',
            handler: Ext.bind(me._onDebtor, me),
        };
    },

    createDeleteColumn: function () {
        var me = this, column;

        column = {
            action: 'delete',
            iconCls: 'sprite-minus-circle-frame',
            handler: Ext.bind(me._onDelete, me),
            tooltip: '{s name=DeleteOffer}Delete offer{/s}',
            getClass: function(v, meta, rec) {
                if (me.notDeleteMode.indexOf(rec.data.status) > 0) {
                    return;
                }

                return 'x-hide-display';
            },
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
            getClass: function(v, meta, rec) {
                if (me.notEditMode.indexOf(rec.data.status) <= 0) {
                    return;
                }

                return 'x-hide-display';
            },
            tooltip: '{s name=EditOffer}Edit offer{/s}',
        };

        me.fireEvent(me.eventAlias + '-edit-action-column-created', me, column);

        return column;
    },

    _onDebtor: function (view, rowIndex, colIndex, item, opts, record) {
        var me = this;
        me.fireEvent('debtorLink', record);
    },

    priceRenderer: function (value) {
        if (value === Ext.undefined) {
            return value;
        }
        return Ext.util.Format.currency(value) + " {b2b_currency_symbol}";
    },

    offerStatusRenderer: function (value) {
        if (value === Ext.undefined) {
            return value;
        }

        return this.offerStatus[value];
    },

    addSortNames: function (columns) {
        var me = this;

        Ext.each(columns, function (columns) {
            if (!me.sorting[columns.dataIndex]) {
                columns.sortable = false;
                return;
            }

            columns.getSortParam = function () {
                return me.sorting[columns.dataIndex];
            }
        });
    },

    createSelectionModel: function () {
        return [];
    }
});
