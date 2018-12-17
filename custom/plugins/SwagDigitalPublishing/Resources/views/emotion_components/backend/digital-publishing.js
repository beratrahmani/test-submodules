// {namespace name=backend/plugins/swag_digital_publishing/emotion}
// {block name="emotion_components/backend/digital_publishing"}
Ext.define('Shopware.apps.Emotion.view.components.DigitalPublishing', {

    extend: 'Shopware.apps.Emotion.view.components.Base',

    alias: 'widget.emotion-digital-publishing',

    layout: 'anchor',

    snippets: {
        settingsLabel: '{s name="settingsLabel"}{/s}',
        selectButtonLabel: '{s name="selectButtonLabel"}{/s}',
        editButtonLabel: '{s name="editButtonLabel"}{/s}',
        previewLabel: '{s name="previewLabel"}{/s}',
        selectionLabel: '{s name="selectionLabel"}{/s}'
    },

    initComponent: function() {
        var me = this;

        me.callParent(arguments);

        me.bannerIdField = me.getFieldByName('digital_publishing_banner_id');
        me.bannerId =  me.bannerIdField.getValue();

        me.bannerDataField = me.getFieldByName('digital_publishing_banner_data');

        // Hide basic fieldset to use custom options.
        me.elementFieldset.hide();

        me.createElements();
        me.createPreview();

        if (me.bannerId) {
            me.loadBannerData(me.bannerId);
            me.editButton.setVisible(true);
        }
    },

    createElements: function() {
        var me = this;

        me.bannerFieldset = Ext.create('Ext.form.FieldSet', {
            title: me.snippets.settingsLabel,
            layout: 'anchor',
            items: [{
                xtype: 'container',
                layout: 'hbox',
                items: [
                    {
                        xtype: 'container',
                        layout: 'anchor',
                        defaults: {
                            anchor: '100%'
                        },
                        width: 200,
                        padding: '10 0',
                        items: [
                            me.createSelectButton(),
                            me.createEditButton()
                        ]
                    },
                    me.createDisplayField()
                ]
            }]
        });

        me.add(me.bannerFieldset);
    },

    createSelectButton: function() {
        var me = this;

        me.selectButton = Ext.create('Ext.Button', {
            text: me.snippets.selectButtonLabel,
            cls: 'primary',
            layout: 'anchor',
            margin: 0,
            handler: function() {
                me.openSelection();
            }
        });

        return me.selectButton;
    },

    createEditButton: function() {
        var me = this;

        me.editButton = Ext.create('Ext.Button', {
            text: me.snippets.editButtonLabel,
            cls: 'secondary',
            layout: 'anchor',
            margin: '5 0 0',
            hidden: true,
            handler: function() {
                me.editBanner();
            }
        });

        return me.editButton;
    },

    createDisplayField: function() {
        var me = this;

        me.displayField = Ext.create('Ext.container.Container', {
            html: '',
            margin: '25 0 0 40',
            style: {
                'font-size': '14px'
            },
            hidden: true
        });

        return me.displayField;
    },

    createPreview: function() {
        var me = this;

        me.previewContainer = Ext.create('Ext.form.FieldSet', {
            title: me.snippets.previewLabel,
            hidden: true,
            items: [{
                xtype: 'container',
                width: '100%',
                height: 440,
                border: false,
                html: '<iframe id="emotionPreviewFrame" style="background: #fff;" frameborder="0" scrolling="none" width="100%" height="100%"></iframe>'
            }]
        });

        me.add(me.previewContainer);
    },

    openSelection: function() {
        var me = this;

        Shopware.app.Application.addSubApplication({
            name: 'Shopware.apps.SwagDigitalPublishing',
            mode: 'selection',
            callbackScope: me,
            selectionCallback: function(selection) {
                if (selection.length) {
                    me.bannerId = selection[0].get('id');
                    me.bannerIdField.setValue(me.bannerId);
                    me.loadBannerData(me.bannerId);
                    me.editButton.setVisible(true);
                }
            }
        });
    },

    editBanner: function() {
        var me = this;

        Shopware.app.Application.addSubApplication({
            name: 'Shopware.apps.SwagDigitalPublishing',
            mode: 'edit',
            bannerId: me.bannerId,
            callbackScope: me,
            saveCallback: function (record) {
                var bannerId = record.get('id');

                if (bannerId !== parseInt(me.bannerId, 10)) {
                    return false;
                }

                me.loadBannerData(bannerId);
            }
        });
    },

    loadBannerData: function(bannerId) {
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
            success: function(responseObject) {
                var response = Ext.decode(responseObject.responseText);

                if (!response.success || !response.data) {
                    return false;
                }

                me.contentBanner = response.data;

                me.bannerDataField.setValue(
                    Ext.encode(me.contentBanner)
                );

                me.showDetails(me.contentBanner);
                me.loadPreview(me.contentBanner);
            }
        });
    },

    loadPreview: function(data) {
        var me = this;

        Ext.each(data['layers'], function(layer) {
            Ext.each(layer['elements'], function(element) {
                if (element['payload'] && element['payload'].length) {
                    var payload = Ext.JSON.decode(element['payload']) || { };
                    Ext.merge(element, payload);
                }
            });
        });

        Ext.Ajax.request({
            url: '{url module="backend" controller="SwagDigitalPublishing" action="preview"}',
            method: 'POST',
            params: {
                'banner': Ext.JSON.encode(data)
            },
            success: function(response) {
                var previewFrame = document.getElementById('emotionPreviewFrame');

                previewFrame.contentWindow.document.open();
                previewFrame.contentWindow.document.write(response.responseText);
                previewFrame.contentWindow.document.close();

                me.previewContainer.show();
            }
        });
    },

    showDetails: function() {
        var me = this;

        me.displayField.getEl().setHTML('<b>' + me.snippets.selectionLabel + '</b> ' + me.contentBanner['name']);
        me.displayField.show();
    },

    getFieldByName: function(name) {
        return this.getForm().findField(name);
    }
});
// {/block}