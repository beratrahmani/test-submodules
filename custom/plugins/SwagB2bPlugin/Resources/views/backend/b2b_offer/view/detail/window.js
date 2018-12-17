//{namespace name=backend/plugins/b2b_debtor_plugin}
Ext.define('Shopware.apps.b2bOffer.view.detail.Window', {
    extend: 'Shopware.window.Detail',
    alias: 'widget.Offer-detail-window',
    height: 750,
    width: 1100,
    title: '{s name=Offer}Offer{/s}',
    layout: 'fit',

    editMode: [
        'offer_status_declined_admin',
        'offer_status_accepted_user'
    ],

    createFormPanel: function () {
        var me = this;

        me.tabPanel = me.createTabPanel();

        me.commandList = Ext.create('Shopware.apps.b2bOffer.view.detail.CommentList', {
            collapseDirection: 'right',
            collapseMode: 'placeholder',
            region: 'east',
            width: 280,
            minSize: 280,
            maxSize: 325,
            collapsible: true,
            record: me.record
        });

        me.formPanel = Ext.create('Ext.form.Panel', {
            items: [me.tabPanel, me.commandList],
            defaults: {
                cls: 'shopware-form'
            },
            layout: {
                type: 'border',
            }
        });

        return me.formPanel;
    },

    createDockedItems: function () {
    },


    createToolbarItems: function () {
        var me = this,
            items = [];

        me.fireEvent(me.getEventName('before-create-toolbar-items'), me, items);

        items.push(me.createCancelButton());
        items.push({ xtype: 'tbfill' });

        if (me.editMode.indexOf(me.record.data.status) >= 0) {
            items.push(me.createDeclineButton());
            items.push(me.createAcceptButton());
            items.push(me.createSendNewOfferButton());
        }

        me.fireEvent(me.getEventName('after-create-toolbar-items'), me, items);

        return items;
    },

    createDeclineButton: function () {
        var me = this;

        me.declineButton = Ext.create('Ext.button.Button', {
            cls: 'secondary right',
            name: 'decline-button',
            text: '{s name="DeclineButton"}Decline Button{/s}',
            handler: function () {
                me.onDecline();
            }
        });

        if (!me.record.get('acceptedByUserAt')) {
            me.declineButton.hidden = true;
        }

        return me.declineButton;
    },

    createAcceptButton: function () {
        var me = this;

        me.acceptButton = Ext.create('Ext.button.Button', {
            cls: 'primary right',
            name: 'accept-button',
            text: '{s name="AcceptButton"}Accept Button{/s}',
            handler: function () {
                me.onAccept();
            }
        });

        if (!me.record.get('acceptedByUserAt')) {
            me.acceptButton.hidden = true;
        }

        return me.acceptButton;
    },

    createSendNewOfferButton: function () {
        var me = this;

        me.sendOfferButton = Ext.create('Ext.button.Button', {
            cls: 'primary right',
            name: 'accept-button',
            text: '{s name="SendOffer"}SendOffer Button{/s}',
            handler: function () {
                me.onAccept();
            }
        });

        if (me.record.get('acceptedByUserAt')) {
            me.sendOfferButton.hidden = true;
        }

        return me.sendOfferButton;
    },

    onDecline: function () {
        var me = this;

        var window = Ext.create('Shopware.apps.b2bOffer.view.detail.decline.Window', {
            offer: me.record,
            hasOwnController: true,
            parentWindow: this,
        });

        window.show();
    },

    onAccept: function () {
        var me = this;

        var window = Ext.create('Shopware.apps.b2bOffer.view.detail.accept.Window', {
            offer: me.record,
            hasOwnController: true,
            parentWindow: this,
        });

        window.show();
    },

    /**
     * Creates the tab panel for the detail page.
     * @return Ext.tab.Panel
     */
    createTabPanel: function () {
        var me = this;

        me.detailForm =
            Ext.create('Shopware.apps.b2bOffer.view.detail.detail.Container', {
                record: me.record
            });

        me.event = Ext.create('Shopware.apps.b2bOffer.view.detail.event.Container', {
            record: me.record
        });

        return Ext.create('Ext.tab.Panel', {
            region: 'center',
            items: [
                me.detailForm,
                me.event
            ],
            dockedItems: [{
                xtype: 'toolbar',
                dock: 'bottom',
                items: me.createToolbarItems(),
            }]
        });
    },

    createToolbar: function () {
        var me = this;

        me.toolbar = Ext.create('Ext.toolbar.Toolbar', {
            items: me.createToolbarItems(),
            dock: 'bottom'
        });
        return me.toolbar;
    },
});