//{namespace name=backend/plugins/b2b_debtor_plugin}
Ext.define('Shopware.apps.b2bOffer.view.detail.CommentList', {
    extend: 'Ext.panel.Panel',
    alias: 'widget.Offer-detail-window-additional',
    autoScroll: true,
    id: 'comment-panel',
    cls: Ext.baseCSSPrefix + 'more-info',
    snippets: {
        title: '{s name=Comments}Comments{/s}',
        noComments: '{s name=NoComments}No comments{/s}',
    },

    initComponent: function () {
        var me = this;
        me.title = me.snippets.title;

        me.store = Ext.create('Shopware.apps.b2bOffer.store.Comment');
        me.store.getProxy().extraParams['orderContextId'] = me.record.raw.orderContextId;
        me.store.load();

        me.items = me.createCommentView();

        me.dockedItems = [{
            xtype: 'toolbar',
            dock: 'bottom',
            items: me.createCommentContainer()
        }];

        me.callParent(arguments);
    },

    createCommentContainer: function () {
        var me = this;

        me.textLog = Ext.create('Ext.form.field.TextArea', {
            emptyText: '{s name=Comment}Comment{/s}',
            name: 'comment',
            allowBlank: false,
            rows: 1,
            grow: true,
            growMin: 24,
            growMax: 100,
            growAppend: '',
        });

        me.addCommentBtn = Ext.create('Ext.Button', {
            xtype: 'button',
            text: '{s name=Save}Save{/s}',
            cls: 'primary',
            height: '100%',
            flex: 1,
            style: 'float: right;',
            handler: function () {
                var form = this.up('form').getForm();

                me.fireEvent('addComment', form, me.store, function () {
                    document.getElementById('comment-panel').children[1].scrollTop =
                        document.getElementById('comment-panel').children[1].children[0].clientHeight;
                });
            },
        });

        return Ext.create('Ext.form.Panel', {
            layout: 'hbox',
            width: '100%',
            url: '{url controller=b2bofferlog action=comment}',
            border: false,
            items: [me.textLog, me.addCommentBtn,
                {
                    xtype: 'hiddenfield',
                    name: 'orderContextId',
                    value: me.record.raw.orderContextId,
                }
                ]
        });
    },

    createCommentView: function() {
        var me = this;

        return Ext.create('Ext.view.View', {
            store: me.store,
            region: "center",
            itemSelector: 'event',
            docked: 'bottom',
            listeners: {
                viewready: function () {
                    document.getElementById('comment-panel').children[1].scrollTop =
                        document.getElementById('comment-panel').children[1].children[0].clientHeight;
                }
            },
            minHeight: '100%',
            style: 'border-bottom: 1px solid rgb(164, 181, 192)',
            height: '100%',
            emptyText: '<p style="text-align: center;">' + me.snippets.noComments + '</p>',
            deferEmptyText: false,
            tpl: new Ext.XTemplate(
                '<ul>',
                '<tpl for=".">',
                '<tpl if="this.isComment(logType) == true">',
                //{include file="backend/b2b_offer/logTypes/Comment.js"}
                '<tpl else>',
                {literal}
            '<p>{logType}</p>',
        {/literal}
            '</tpl>',
                '</tpl>',
                '</ul>',
                {
                    isComment: function(type) {
                        return type == 'Shopware\\B2B\\AuditLog\\Framework\\AuditLogValueOrderCommentEntity';
                    },
                    isEmpty: function(value) {
                        return value == '';
                    },
                    isBackend: function(isbacked) {
                        return isbacked;
                    },
                    getDate: function (date) {
                        return Ext.Date.format(new Date(date['date']), 'd.m.y H:i');
                    },
                    isSameType: function (actual, next) {
                        if(!next) {
                            return false;
                        }

                        if(actual.authorHash !== next.authorHash) {
                           return false;
                        }

                        if(actual.eventDate.date.split(" ")[0] !== next.eventDate.date.split(" ")[0]) {
                            return false;
                        }

                        return true;
                    }
                }
            ),
         });
    },
});