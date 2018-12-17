//{namespace name=backend/plugins/swag_digital_publishing/editor}
//{block name="backend/swag_digital_publishing/view/editor/settings/banner"}
Ext.define('Shopware.apps.SwagDigitalPublishing.view.editor.settings.Banner', {

    extend: 'Ext.form.Panel',

    alias: 'widget.publishing-editor-settings-banner',

    cls: Ext.baseCSSPrefix + 'swag-publishing-settings-banner shopware-form',

    border: false,

    layout: 'anchor',

    bodyPadding: 10,

    overflowY: 'auto',

    defaults: {
        anchor : '100%',
        labelWidth: 80
    },

    snippets: {
        nameFieldLabel: '{s name="nameFieldLabel"}{/s}',
        backgroundFieldLabel: '{s name="backgroundFieldLabel"}{/s}',
        backgroundModeLabel: '{s name="backgroundModeLabel"}{/s}',
        orientationFieldLabel: '{s name="orientationFieldLabel"}{/s}',
        colorFieldLabel: '{s name="colorFieldLabel"}{/s}',
        imageFieldLabel: '{s name="imageFieldLabel"}{/s}',
        generalSettingsLabel: '{s name="generalSettingsLabel"}{/s}',
        backgroundModeCover: '{s name="backgroundModeCover"}{/s}',
        backgroundModeRepeat: '{s name="backgroundModeRepeat"}{/s}'
    },

    initComponent: function() {
        var me = this;

        me.nodeId = 'contentBanner' + me.record.getId();

        me.items = me.createFormFields();

        me.callParent(arguments);

        me.getForm().loadRecord(me.record);
    },

    createFormFields: function() {
        var me = this;

        me.generalFieldset = me.createFieldset(me.snippets.generalSettingsLabel);
        me.imageFieldset = me.createFieldset(me.snippets.backgroundFieldLabel).hide();
        me.colorFieldset = me.createFieldset(me.snippets.backgroundFieldLabel).hide();

        me.nameField = Ext.create('Ext.form.field.Text', {
            name: 'name',
            fieldLabel: me.snippets.nameFieldLabel,
            allowBlank: false,
            listeners: {
                scope: me,
                change: function(field, newValue) {
                    me.updateRecord();
                    me.editor.setTitle(newValue);
                }
            }
        });

        me.typeField = Ext.create('Ext.form.field.ComboBox', {
            fieldLabel: me.snippets.backgroundFieldLabel,
            name: 'bgType',
            displayField: 'name',
            valueField: 'value',
            allowBlank: false,
            forceSelection: true,
            autoSelect: true,
            store: me.createBgTypeStore(),
            listeners: {
                change: function(field, newValue) {
                    me.showFieldset(newValue);
                    me.updateRecord();
                }
            }
        });

        me.mediaField = Ext.create('Shopware.apps.SwagDigitalPublishing.view.editor.fields.MediaField', {
            name: 'mediaId',
            value: me.record.get('mediaId') || null,
            listeners: { scope: me, 'selectMedia': Ext.bind(me.updateRecord, me) }
        });

        me.modeField = Ext.create('Ext.form.field.ComboBox', {
            fieldLabel: me.snippets.backgroundModeLabel,
            name: 'bgMode',
            displayField: 'name',
            valueField: 'value',
            allowBlank: false,
            forceSelection: true,
            autoSelect: true,
            editable: false,
            margin: '10 0 0 0',
            store: me.createBgModeStore(),
            listeners: {
                scope: me,
                change: function(field, newValue) {
                    me.orientationField[(newValue === 'cover') ? 'show' : 'hide']();
                    me.updateRecord();
                }
            }
        });

        me.orientationField = Ext.create('Ext.form.RadioGroup', {
            fieldLabel: me.snippets.orientationFieldLabel,
            columns: 3,
            anchor: 'none',
            margin: '20 0 0 0',
            defaults: {
                margin: 0
            },
            listeners: { scope: me, change: Ext.bind(me.updateRecord, me) },
            items: [
                { name: 'bgOrientation', inputValue: 'top left' },
                { name: 'bgOrientation', inputValue: 'top center' },
                { name: 'bgOrientation', inputValue: 'top right' },
                { name: 'bgOrientation', inputValue: 'center left' },
                { name: 'bgOrientation', inputValue: 'center center' },
                { name: 'bgOrientation', inputValue: 'center right' },
                { name: 'bgOrientation', inputValue: 'bottom left' },
                { name: 'bgOrientation', inputValue: 'bottom center' },
                { name: 'bgOrientation', inputValue: 'bottom right' }
            ]
        });

        me.colorField = Ext.create('Shopware.form.field.ColorField', {
            fieldLabel: me.snippets.colorFieldLabel,
            name: 'bgColor'
        });
        me.colorField.inputField.on('change', Ext.bind(me.updateRecord, me));

        me.generalFieldset.add([
            me.nameField,
            me.typeField
        ]);

        me.imageFieldset.add([
            me.mediaField,
            me.modeField,
            me.orientationField
        ]);

        me.colorFieldset.add([
            me.colorField
        ]);

        return [
            me.generalFieldset,
            me.imageFieldset,
            me.colorFieldset
        ];
    },

    createFieldset: function(title) {
        return Ext.create('Ext.form.FieldSet', {
            title: title,
            layout: 'anchor',
            defaults: {
                anchor : '100%',
                labelWidth: 80
            }
        })
    },

    showFieldset: function(type) {
        var me = this;

        me.imageFieldset[(type === 'image') ? 'show' : 'hide']();
        me.colorFieldset[(type === 'color') ? 'show' : 'hide']();
    },

    createBgTypeStore: function() {
        var me = this;

        me.typeStore = Ext.create('Ext.data.Store', {
            fields: ['value', 'name'],
            data : [
                { 'value': 'color', 'name': me.snippets.colorFieldLabel },
                { 'value': 'image', 'name': me.snippets.imageFieldLabel }
            ]
        });

        return me.typeStore;
    },

    createBgModeStore: function() {
        var me = this;

        me.modeStore = Ext.create('Ext.data.Store', {
            fields: ['value', 'name'],
            data : [
                { 'value': 'cover', 'name': me.snippets.backgroundModeCover },
                { 'value': 'repeat', 'name': me.snippets.backgroundModeRepeat }
            ]
        });

        return me.modeStore;
    },

    updateRecord: function() {
        var me = this;

        me.editor.updateRecord(me, me.record);
    }
});
//{/block}