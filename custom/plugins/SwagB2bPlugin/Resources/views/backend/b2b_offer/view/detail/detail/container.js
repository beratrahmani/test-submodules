//{namespace name=backend/plugins/b2b_debtor_plugin}
Ext.define('Shopware.apps.b2bOffer.view.detail.detail.Container', {
    extend: 'Ext.container.Container',
    alias: 'widget.Offer-detail-positions-container',
    title: '{s name=Offer}Offer{/s}',
    layout: {
        type: 'vbox',
        align: 'stretch'
    },

    initItems: function () {
        var me = this,
            positionStore = Ext.create('Shopware.apps.b2bOffer.store.Positions');

        me.callParent(arguments);

        positionStore.getProxy().extraParams['offer_id'] = me.record.get('id');

        positionStore.load();

        me.positions = Ext.create('Shopware.apps.b2bOffer.view.detail.detail.Positions', {
            flex: 2,
            record: me.record,
            store: positionStore
        });

        me.discount = Ext.create('Shopware.apps.b2bOffer.view.detail.detail.Discount', {
            record: me.record
        });

        me.offer = Ext.create('Shopware.apps.b2bOffer.view.detail.detail.B2bOffer', {
            record: me.record
        });

        me.add(me.offer);

        me.add(me.positions);

        me.add(me.discount);
    }
});