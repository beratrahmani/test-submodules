//{namespace name=backend/plugins/b2b_debtor_plugin}
Ext.define("Shopware.apps.b2bOffer.view.detail.event.Event", {
    extend: 'Ext.panel.Panel',
    alias: 'widget.Offer-detail-event-event',
    overflowY: 'auto',
    mixins: ['Shopware.model.Helper'],
    id: 'b2bOffer-detail--event--event',
    snippets: {
        emptyText: '{s name=NoEvents}No events{/s}',
    },

    pagingItemText: '{s name="ItemText"}items{/s}',

    initComponent: function() {
        var me = this;

        me.eventAlias = me.getEventAlias('Shopware.apps.b2bOffer.store.Event');

        me.dockedItems = me.createDockedItems();

        me.items = me.createView();

        me.callParent(arguments);
    },

    createView: function() {
        var me = this;

        return Ext.create('Ext.view.View', {
            store: me.store,
            region: "center",
            tpl: new Ext.XTemplate(
                '<ul style="margin: 20px;">',
                    '<tpl for=".">',
                        '<tpl if="this.isAdd(logType) == true">',
                        //{include file="backend/b2b_offer/logTypes/ItemAdded.js"}
                        '<tpl elseif="this.isPrice(logType) == true">',
                        //{include file="backend/b2b_offer/logTypes/LineItemPriceChange.js"}
                        '<tpl elseif="this.isQuantity(logType) == true">',
                        //{include file="backend/b2b_offer/logTypes/LineItemQuantityChange.js"}
                        '<tpl elseif="this.isStatus(logType) == true">',
                        //{include file="backend/b2b_offer/logTypes/StatusChanged.js"}
                        '<tpl elseif="this.isRemove(logType) == true">',
                        //{include file="backend/b2b_offer/logTypes/LineItemRemove.js"}
                        '<tpl elseif="this.isComment(logType) == true">',
                        //{include file="backend/b2b_offer/logTypes/Comment.js"}
                        '<tpl elseif="this.isExpiredDate(logType) == true">',
                        //{include file="backend/b2b_offer/logTypes/OfferDateAdded.js"}
                        '<tpl elseif="this.isDiscount(logType) == true">',
                        //{include file="backend/b2b_offer/logTypes/OfferDiscount.js"}
                        '<tpl elseif="this.isLineItemComment(logType) == true">',
                        //{include file="backend/b2b_offer/logTypes/LineItemComment.js"}
                        '<tpl else>',
                        {literal}
                        '<p>{logType}</p>',
                        {/literal}
                        '</tpl>',
                    '</tpl>',
                '</ul>',
                {
                    isAdd: function(type) {
                        return type == 'Shopware\\B2B\\AuditLog\\Framework\\AuditLogValueLineItemAddEntity';
                    },
                    isPrice: function(type) {
                        return type == 'Shopware\\B2B\\Offer\\Framework\\AuditLogValueLineItemPriceEntity';
                    },
                    isQuantity: function(type) {
                        return type == 'Shopware\\B2B\\AuditLog\\Framework\\AuditLogValueLineItemQuantityEntity';
                    },
                    isStatus: function(type) {
                        return type == 'Shopware\\B2B\\Offer\\Framework\\AuditLogValueOfferDiffEntity';
                    },
                    isRemove: function(type) {
                        return type == 'Shopware\\B2B\\AuditLog\\Framework\\AuditLogValueLineItemRemoveEntity';
                    },
                    isComment: function(type) {
                        return type == 'Shopware\\B2B\\AuditLog\\Framework\\AuditLogValueOrderCommentEntity';
                    },
                    isExpiredDate: function (type) {
                        return type == 'Shopware\\B2B\\Offer\\Framework\\AuditLogExpirationDate';
                    },
                    isDiscount: function (type) {
                        return type == 'Shopware\\B2B\\Offer\\Framework\\AuditLogDiscountEntity';
                    },
                    isLineItemComment: function (type) {
                        return type == 'Shopware\\B2B\\AuditLog\\Framework\\AuditLogValueLineItemCommentEntity';
                    },
                    isEmpty: function(value) {
                        return value == '';
                    },
                    isBackend: function(isbacked) {
                        return isbacked;
                    },
                    getDate: function (date) {
                        return Ext.Date.format(new Date(date['date']), 'd.m.y H:i:s');
                    },
                    getStatus: function (status) {
                        switch(status) {
                            case 'offer_status_open':
                                return '{s name=offer_status_open}Open{/s}';
                                break;
                            case 'offer_status_accepted_user':
                                return '{s name=offer_status_accepted_user}Sent to the admin by user{/s}';
                                break;
                            case 'offer_status_accepted_admin':
                                return '{s name=offer_status_accepted_admin}Sent to the user by admin{/s}';
                                break;
                            case 'offer_status_declined_user':
                                return '{s name=offer_status_declined_user}Declined by user{/s}';
                                break;
                            case 'offer_status_declined_admin':
                                return '{s name=offer_status_declined_admin}Declined by admin{/s}';
                                break;
                            case 'offer_status_expired':
                                return '{s name=offer_status_expired}Expired{/s}';
                                break;
                            case 'offer_status_accepted_both':
                                return '{s name=offer_status_accepted_both}Accepted by both{/s}';
                                break;
                            default:
                                return status
                        }
                    }
                }
            ),
            itemSelector: 'event',
            emptyText: '<p style="text-align: center;">' + me.snippets.emptyText + '</p>',
            deferEmptyText: false,
        });
    },

    createDockedItems: function() {
        var me = this,
            items = [];

        items.push(me.createPagingToolbar());

        return items;
    },

    createPageSizeCombo: function() {
        var me = this, value = 20;

        if (me.store) {
            value = me.store.pageSize;
        }

        me.pageSizeCombo = Ext.create('Ext.form.field.ComboBox', {
            fieldLabel: me.pageSizeLabel,
            labelWidth: 110,
            cls: 'page-size-combo',
            queryMode: 'local',
            value: value,
            width: 220,
            store: Ext.create('Ext.data.Store', {
                fields: [ 'value', 'name' ],
                data: me.createPageSizes()
            }),
            displayField: 'name',
            valueField: 'value',
            listeners: {
                select: function (combo, records) {
                    me.store.pageSize = combo.getValue();
                    me.store.currentPage = 1;
                    me.store.load();
                }
            }
        });

        me.fireEvent(me.eventAlias + '-page-size-combo-created', me, me.pageSizeCombo);

        return me.pageSizeCombo;
    },

    createPagingToolbar: function() {
        var me = this;

        me.pagingbar = Ext.create('Ext.toolbar.Paging', {
            store: me.store,
            dock: 'bottom',
            displayInfo: true,
        });

        var pageSizeCombo = me.createPageSizeCombo();
        me.pagingbar.add(pageSizeCombo, { xtype: 'tbspacer', width: 6 });


        me.fireEvent(me.eventAlias + '-paging-bar-created', me, me.pagingbar);

        return me.pagingbar;
    },

    createPageSizes: function() {
        var me = this, data = [];

        me.fireEvent(me.eventAlias + '-before-create-page-sizes', me, data);

        for (var i = 1; i <= 10; i++) {
            var count = i * 20;
            data.push({ value: count, name: count + ' ' + me.pagingItemText });
        }

        me.fireEvent(me.eventAlias + '-after-create-page-sizes', me, data);

        return data;
    },
});