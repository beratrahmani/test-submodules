//{namespace name=backend/plugins/b2b_debtor_plugin}
Ext.define('Shopware.apps.b2bOffer.view.detail.decline.Window', {
    extend: 'Ext.window.Window',
    alias: 'widget.Offer-decline-window',
    width: 550,
    layout: 'fit',
    title : '{s name=Decline}Decline{/s}',

    initComponent: function () {
        var me = this;

        this.items =  Ext.create('Shopware.apps.b2bOffer.view.detail.decline.Detail', {
            offer: me.offer,
            parentWindow: me.parentWindow
        });

        this.callParent();
    }
});