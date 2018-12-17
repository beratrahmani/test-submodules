//{namespace name=backend/plugins/b2b_debtor_plugin}
Ext.define('Shopware.apps.b2bOffer.view.detail.event.Container', {
    extend: 'Ext.container.Container',
    alias: 'widget.Offer-detail-event-container',
    title: '{s name=History}History{/s}',
    layout: {
        type: 'vbox',
        align: 'stretch'
    },

    initItems: function () {
        var me = this,
            eventStore = Ext.create('Shopware.apps.b2bOffer.store.Event');

        me.callParent(arguments);

        eventStore.getProxy().extraParams['orderContextId'] = me.record.raw.orderContextId;

        eventStore.load();

        me.eventList = Ext.create('Shopware.apps.b2bOffer.view.detail.event.Event', {
            record: me.record,
            store: eventStore,
            flex: 1,
        });

        me.add(me.eventList);
    },
});