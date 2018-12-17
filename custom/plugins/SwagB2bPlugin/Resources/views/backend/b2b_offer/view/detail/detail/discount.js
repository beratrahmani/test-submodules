//{namespace name=backend/plugins/b2b_debtor_plugin}
Ext.define('Shopware.apps.b2bOffer.view.detail.detail.Discount', {
    extend: 'Ext.form.Panel',
    alias: 'widget.Offer-detail-positions-discount',
    id: 'b2bOffer-view--detail-discount',
    layout: {
        type: 'hbox',
        align: 'stretch',
    },
    border: 0,
    editMode: [
        'offer_status_declined_admin',
        'offer_status_accepted_user'
    ],
    items: [
        {
            xtype: 'numberfield',
            name: 'discount',
            fieldLabel: '{s name=Discount}Discount{/s} in {b2b_currency_symbol}',
            labelStyle: 'text-align: center;',
            minValue: 0,
            flex: 1,
            listeners : {
                change: function (that, newValue, oldValue, opts) {
                    this.ownerCt.fireEvent('changeDiscount', this.ownerCt.record, newValue, this);
                }
            }
        }
    ],

    initComponent: function () {
        var me = this;

        Ext.each(me.items, function (item) {
            if (item.name === 'offer_id') {
                item.value = me.record.get('id');
            }
            if (item.name === 'discount') {
                item.value = me.record.get('discountValueNet');
                

                if (me.editMode.indexOf(me.record.data.status) < 0) {
                    item.cls = 'x-item-disabled';
                    item.readOnly = true;
                } else {
                    item.cls = null;
                    item.readOnly = false;
                }
            }
        });

        return me.callParent(arguments);
    }
});