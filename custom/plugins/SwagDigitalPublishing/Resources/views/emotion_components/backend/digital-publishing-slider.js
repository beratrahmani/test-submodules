//{namespace name=backend/plugins/swag_digital_publishing/emotion}
//{block name="emotion_components/backend/digital_publishing_slider"}
Ext.define('Shopware.apps.Emotion.view.components.DigitalPublishingSlider', {

    extend: 'Shopware.apps.Emotion.view.components.Base',

    alias: 'widget.emotion-digital-publishing-slider',

    layout: 'anchor',

    defaults: {
        anchor: '100%',
        labelWidth: 200
    },

    snippets: {
        selectButtonLabel: '{s name="selectButtonLabel"}{/s}',
        selectionLabel: '{s name="selectionLabel"}{/s}',
        sortingDragText: '{s name="sortingDragText"}{/s}',
        nameColumnLabel: '{s name="nameColumnLabel" namespace="backend/plugins/swag_digital_publishing/main"}{/s}',
        deleteTooltip: '{s name="deleteTooltip" namespace="backend/plugins/swag_digital_publishing/main"}{/s}',
        editLabel: '{s name="editButtonLabel"}{/s}',
        show_arrows: {
            fieldLabel: '{s name="showArrowsLabel"}{/s}'
        },
        show_navigation: {
            fieldLabel: '{s name="showNavigationLabel"}{/s}'
        },
        auto_slide: {
            fieldLabel: '{s name="autoSlideLabel"}{/s}'
        },
        slide_interval: {
            fieldLabel: '{s name="slideIntervalLabel"}{/s}'
        },
        animation_speed: {
            fieldLabel: '{s name="animationSpeedLabel"}{/s}'
        }
    },

    initComponent: function () {
        var me = this;

        me.callParent(arguments);

        me.payloadField = me.getFieldByName('digital_publishing_slider_payload');
        me.previewField = me.getFieldByName('digital_publishing_slider_preview_data');

        me.selectionStore = me.createSelectionStore();

        me.elementFieldset.items.each(function (item) {
            item.labelWidth = 180;
        });

        me.createElements();
    },

    createElements: function () {
        var me = this;

        me.elementFieldset.add([{
            xtype: 'container',
            layout: 'hbox',
            items: [
                me.createButton()
            ]
        }, {
            xtype: 'gridpanel',
            title: me.snippets.selectionLabel,
            store: me.selectionStore,
            layout: 'fit',
            height: 360,
            border: false,
            columns: [{
                text: me.snippets.nameColumnLabel,
                dataIndex: 'name',
                sortable: true,
                flex: 1
            }, {
                xtype: 'actioncolumn',
                width: 60,
                items: [{
                    iconCls: 'sprite-pencil',
                    tooltip: me.snippets.editLabel,
                    handler: function (view, rowIndex, colIndex, item, opts, record) {
                        var id = record.get('id');
                        me.editBanner(id);
                    }
                }, {
                    iconCls: 'sprite-minus-circle-frame',
                    tooltip: me.snippets.deleteTooltip,
                    handler: function (view, rowIndex, colIndex, item, opts, record) {
                        me.selectionStore.remove(record);
                        me.updateFields();
                    }
                }]
            }],
            viewConfig: {
                plugins: {
                    ptype: 'gridviewdragdrop',
                    dragText: me.snippets.sortingDragText
                },
                listeners: {
                    scope: me,
                    drop: function () {
                        me.updateFields();
                    }
                }
            }
        }]);
    },

    createButton: function () {
        var me = this;

        me.selectButton = Ext.create('Ext.Button', {
            text: me.snippets.selectButtonLabel,
            cls: 'primary',
            layout: 'anchor',
            width: 200,
            margin: '20 0',
            handler: function () {
                me.openSelection();
            }
        });

        return me.selectButton;
    },

    createSelectionStore: function () {
        var me = this,
            payload = me.payloadField.getValue(),
            data = [];

        if (payload && payload.length) {
            data = Ext.JSON.decode(payload);
        }

        return Ext.create('Ext.data.Store', {
            fields: ['id', 'name'],
            data: data
        });
    },

    openSelection: function () {
        var me = this;

        Shopware.app.Application.addSubApplication({
            name: 'Shopware.apps.SwagDigitalPublishing',
            mode: 'selection',
            multiSelect: true,
            callbackScope: me,
            selectionCallback: function (selection) {
                if (selection.length) {
                    Ext.each(selection, function (item) {
                        if (me.selectionStore.getById(item.get('id')) === null) {
                            me.selectionStore.add(item);
                        }
                    });

                    me.updateFields();
                }
            }
        });
    },

    editBanner: function (bannerId) {
        if (!bannerId) {
            return;
        }
        bannerId = parseInt(bannerId, 10);

        Shopware.app.Application.addSubApplication({
            name: 'Shopware.apps.SwagDigitalPublishing',
            mode: 'edit',
            bannerId: bannerId
        });
    },

    loadPreviewData: function (bannerId) {
        var me = this,
            id = bannerId || me.bannerId;

        if (!id) {
            return false;
        }

        Ext.Ajax.request({
            url: '{url controller="SwagContentBanner" action="detail"}',
            method: 'GET',
            params: {
                id: id
            },
            success: function (responseObject) {
                var response = Ext.decode(responseObject.responseText);

                if (!response.success || !response.data) {
                    return false;
                }

                me.previewBanner = response.data;

                me.previewField.setValue(
                    Ext.encode(me.previewBanner)
                );
            }
        });
    },

    updateFields: function () {
        var me = this,
            payload = [];

        me.selectionStore.each(function (record) {
            payload.push(record.getData());
        });

        if (payload[0]) {
            me.loadPreviewData(payload[0]['id']);
        } else {
            me.previewField.setValue('');
        }

        me.payloadField.setValue(Ext.JSON.encode(payload));
    },

    getFieldByName: function (name) {
        return this.getForm().findField(name);
    }
});
//{/block}