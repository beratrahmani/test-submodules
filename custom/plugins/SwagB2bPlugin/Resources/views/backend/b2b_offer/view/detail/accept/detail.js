//{namespace name=backend/plugins/b2b_debtor_plugin}
Ext.define('Shopware.apps.b2bOffer.view.detail.accept.Detail', {
    extend: 'Ext.form.Panel',
    alias: 'widget.offer-detail-accept-detail',
    url: '{url controller=b2bofferlog action=comment}',
    bodyStyle: {
        background: "#ebedef",
    },
    layout: {
        type: 'vbox',
        align: 'stretch'
    },
    items: [
        {
            xtype: 'textarea',
            name: 'comment',
            flex: 10,
            height: 400,
            emptyText: '{s name=Comment}Comment{/s}',
            padding: '15 15 10 15',
            grow: true,
        },
        {
            xtype: 'hiddenfield',
            name: 'orderContextId'
        },
        {
            xytpe: 'Ext.toolbar.Toolbar',
            dock: 'bottom',
            bodyStyle: {
                background: "#ebedef",
            },
            items: [{
                xtype: 'button',
                margin: 2,
                text: '{s name=Save}Save{/s}',
                flex: 1,
                cls: 'primary',
                style: 'float: right;',
                dock: 'bottom',
                handler: function () {
                    var me = this.up('form'),
                        form = me.getForm(),
                        window = this.up('window');

                    if(me.items.items[0].value) {
                        form.submit();
                    }

                    window.close();

                    me.fireEvent('acceptOffer', me.offer, me.parentWindow);
                },
            }],
        }
    ],

    initComponent: function () {
        var me = this;

        Ext.each(me.items, function (item) {
            if (item.name === 'orderContextId') {
                item.value = me.offer.raw.orderContextId;
            }
        });

        return me.callParent(arguments);
    }
});