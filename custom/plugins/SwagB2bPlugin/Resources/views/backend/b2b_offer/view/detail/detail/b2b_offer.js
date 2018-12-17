//{namespace name=backend/plugins/b2b_debtor_plugin}
Ext.define('Shopware.apps.b2bOffer.view.detail.detail.B2bOffer', {
    extend: 'Shopware.model.Container',
    alias: 'widget.b2bOffer-detail-container',
    padding: 20,
    id: 'b2bOffer-view--detail',

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

    priceRenderer: function (value) {
        if (value === Ext.undefined) {
            return value;
        }

        return Ext.util.Format.currency(value) + " {b2b_currency_symbol}";
    },

    createItems: function () {
        var me = this,
            items = me.callParent();
        
        items.forEach(function (item) {
            if (item.id === 'offer-modal-debtor') {
                item.add({
                    xtype: 'container',
                    items: Ext.create('Ext.Button', {
                        text: '{s name=OpenDebtor}Open debtor{/s}',
                        style: 'position: absolute; bottom: 0; right: 0; margin: 0 10px 10px 0;',
                        handler: function () {
                            me.fireEvent('debtorLink', me.record);
                        },
                    })
                })
            }

            if (item.id === 'offer-modal-detail') {
                item.add({
                    xtype: 'container',
                    items: Ext.create('Ext.Button', {
                        text: '{s name=Save}Save{/s}',
                        style: 'position: absolute; bottom: 0; right: 0; margin: 0 10px 10px 0;',
                        handler: function () {
                            me.saveExpiredDate();
                        }
                    })
                })
            }
        });

        return Ext.create('Ext.container.Container', {
            layout: {
                type: 'hbox',
                align: 'stretch'
            },
            items: items,
        });
    },

    saveExpiredDate: function () {
        var me = this,
            form = me.up('form').getForm(),
            dateField = form.findField('expiredAtDate'),
            timeField = form.findField('expiredAtTime'),
            dateString = Ext.Date.format(dateField.getValue(), 'Y-m-d'),
            timeString = Ext.Date.format(timeField.getValue(), 'H:i');

        if (!dateString) {
            me.fireEvent(
                'saveExpiredDate',
                null,
                me.record.get('id'),
                dateField,
                timeField,
                this
            );
            return;
        }

        if (!timeString) {
            timeString = '00:00';
        }

        me.fireEvent(
            'saveExpiredDate',
            dateString + ' ' + timeString + ':00',
            me.record.get('id'),
            dateField,
            timeField,
            this
        );
    },

    configure: function () {
        var me = this;

        return {
            splitFields: false,
            fieldSets: [
                {
                    title: '{s name=DebtorCompany}Company{/s}',
                    flex: 1,
                    style: 'position: relative;',
                    id: 'offer-modal-debtor',
                    margin: '0 5',
                    fields: {
                        'debtorCompany': {
                            fieldLabel: '{s name=DebtorCompany}Company{/s}',
                            labelWidth: '50%',
                            xtype: 'displayfield',
                        },
                        'debtorEmail': {
                            fieldLabel: '{s name=Email}Email{/s}',
                            labelWidth: '50%',
                            xtype: 'displayfield',
                        },
                        'debtorNumber': {
                            fieldLabel: '{s name=Number}Number{/s}',
                            labelWidth: '50%',
                            xtype: 'displayfield',
                        }
                    }
                },
                {
                    title: '{s name="Offer"}Offer{/s}',
                    flex: 1,
                    id: 'offer-modal-detail',
                    style: 'position: relative;',
                    margin: '0 5',
                    fields: {
                        'createdAt': {
                            fieldLabel: '{s name=Created}Creation date{/s}',
                            labelWidth: '50%',
                            xtype: 'displayfield',
                            renderer: Ext.util.Format.dateRenderer('d.m.Y')
                        },
                        'changedStatusAt': {
                            fieldLabel: '{s name=Changed}Change date{/s}',
                            labelWidth: '50%',
                            xtype: 'displayfield',
                            renderer: Ext.util.Format.dateRenderer('d.m.Y')
                        },
                        'status': {
                            fieldLabel: '{s name=Status}Status{/s}',
                            labelWidth: '50%',
                            xtype: 'displayfield',
                            renderer: function (value) {
                                return me.offerStatus[value];
                            }
                        },
                        'expiredAtDate': {
                            fieldLabel: '{s name=Expired}Expiry date{/s}',
                            style: 'float:left;',
                            labelStyle: 'text-align: left;',
                            labelWidth: '50%',
                            labelAlign: 'left',
                        },
                        'expiredAtTime': {
                            xtype: 'timefield',
                            format: 'H:i',
                            style: 'float:right; width: 50%;',
                            labelStyle: 'display:none;',
                            labelWidth: '50%',
                            labelAlign: 'left',
                        },
                        'listAmountNet': {
                            fieldLabel: "{s name=OrderAmountNet}Order amount without tax{/s}",
                            labelWidth: '50%',
                            xtype: 'displayfield',
                            fieldStyle: 'margin-top: 5px',
                            renderer: me.priceRenderer
                        },
                        'summaryDiscountAmountNet': {
                            fieldLabel: "{s name=DiscountAmountNet}Discount amount without tax{/s}",
                            labelWidth: '50%',
                            xtype: 'displayfield',
                            fieldStyle: 'margin-top: 5px',
                            renderer: me.priceRenderer
                        },
                        'listPositionCount': {
                            fieldLabel: '{s name=OrderItemQuantity}Order item quantity{/s}',
                            labelWidth: '50%',
                            xtype: 'displayfield',
                            fieldStyle: 'margin-top: 5px',
                        }
                    }
                }
            ]
        };
    }
});