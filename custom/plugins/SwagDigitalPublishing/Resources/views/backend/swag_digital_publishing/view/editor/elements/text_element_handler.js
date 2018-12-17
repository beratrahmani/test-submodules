// {namespace name=backend/plugins/swag_digital_publishing/editor}
// {block name="backend/swag_digital_publishing/view/editor/text_element_handler"}
Ext.define('Shopware.apps.SwagDigitalPublishing.view.editor.elements.TextElementHandler', {

    extend: 'Shopware.apps.SwagDigitalPublishing.view.editor.elements.AbstractElementHandler',

    name: 'text',

    label: '{s name="textElementLabel"}{/s}',

    iconCls: 'digpub-icon--text',

    snippets: {
        textSettingsLabel: '{s name="textSettingsLabel"}{/s}',
        textLabel: '{s name="textLabel"}{/s}',
        fontStyleLabel: '{s name="fontStyleLabel"}{/s}',
        semanticLabel: '{s name="semanticLabel"}{/s}',
        fontFamilyLabel: '{s name="fontFamilyLabel"}{/s}',
        lineHeightLabel: '{s name="lineHeightLabel"}{/s}',
        fontColorLabel: '{s name="fontColorLabel"}{/s}',
        orientationFieldLabel: '{s name="orientationFieldLabel"}{/s}',
        paddingLabel: '{s name="paddingLabel"}{/s}',
        cssClassLabel: '{s name="cssClassLabel"}{/s}'
    },

    createFormItems: function (elementRecord, data) {
        var me = this;

        me.record = elementRecord;

        me.generalFieldset = Ext.create('Ext.form.FieldSet', {
            title: me.snippets.textSettingsLabel,
            layout: 'anchor',
            defaults: {
                anchor: '100%',
                labelWidth: 100
            },
            items: [
                me.createTextField(data),
                me.createTextTypeField(data),
                me.createTextFontField(data),
                me.createTextFontSizeField(data),
                me.createTextLineHeightField(data),
                me.createTextColorField(data),
                me.createTextOrientationField(data),
                me.createTextFontStyleField(data),
                me.createTextShadowField(data),
                me.createTextPaddingField(data),
                me.createTextCssClassField(data)
            ]
        });

        return me.generalFieldset;
    },

    /**
     * @param { array } data
     * @returns { Ext.form.field.TextArea }
     */
    createTextField: function (data) {
        var me = this,
            configuration = {
                data: data,
                anchor: '100%',
                name: 'text',
                translatable: true,
                fieldLabel: me.snippets.textLabel,
                value: data['text'] || '',
                listeners: {
                    change: Ext.bind(me.onChange, me)
                }
            };

        return Ext.create('Ext.form.field.TextArea', configuration);
    },

    createTextTypeField: function (data) {
        var me = this,
            configuration = {
                anchor: '100%',
                fieldLabel: me.snippets.semanticLabel,
                name: 'type',
                value: data['type'] || 'h1',
                listeners: {
                    change: Ext.bind(me.onChange, me)
                }
            };

        return Ext.create('Shopware.apps.SwagDigitalPublishing.view.editor.fields.TextTypeField', configuration);
    },

    createTextFontField: function (data) {
        var me = this,
            configuration = {
                anchor: '100%',
                name: 'font',
                fieldLabel: me.snippets.fontFamilyLabel,
                value: data['font'] || 'Open Sans',
                listeners: {
                    change: Ext.bind(me.onChange, me)
                }
            };

        return Ext.create('Shopware.apps.SwagDigitalPublishing.view.editor.fields.TextFontSelectField', configuration);
    },

    createTextFontSizeField: function (data) {
        var me = this,
            configuration = {
                data: data,
                listeners: {
                    change: Ext.bind(me.onChange, me)
                }
            };

        return Ext.create('Shopware.apps.SwagDigitalPublishing.view.editor.fields.TextFontSizeField', configuration);
    },

    createTextLineHeightField: function (data) {
        var me = this,
            configuration = {
                anchor: '100%',
                name: 'lineHeight',
                fieldLabel: me.snippets.lineHeightLabel,
                value: data['lineHeight'] || 1,
                allowDecimals: true,
                minValue: 0,
                step: 0.1,
                listeners: {
                    change: Ext.bind(me.onChange, me)
                }
            };

        return Ext.create('Ext.form.field.Number', configuration);
    },

    createTextColorField: function (data) {
        var me = this,
            colorField,
            configuration = {
                anchor: '100%',
                name: 'fontcolor',
                fieldLabel: me.snippets.fontColorLabel
            };

        colorField = Ext.create('Shopware.form.field.ColorField', configuration);

        colorField.inputField.setValue(data['fontcolor'] || '#FFFFFF');
        colorField.inputField.on('change', Ext.bind(me.onChange, me));

        return colorField;
    },

    createTextOrientationField: function (data) {
        var me = this,
            configuration = {
                anchor: '100%',
                fieldLabel: me.snippets.orientationFieldLabel,
                data: data,
                listeners: {
                    change: Ext.bind(me.onChange, me)
                }
            };

        return Ext.create('Shopware.apps.SwagDigitalPublishing.view.editor.fields.TextOrientationField', configuration);
    },

    createTextFontStyleField: function (data) {
        var me = this,
            configuration = {
                fieldLabel: me.snippets.fontStyleLabel,
                anchor: '100%',
                data: data,
                listeners: {
                    change: Ext.bind(me.onChange, me)
                }
            };

        return Ext.create('Shopware.apps.SwagDigitalPublishing.view.editor.fields.TextStyleField', configuration);
    },

    createTextShadowField: function (data) {
        var me = this,
            configuration = {
                anchor: '100%',
                data: data,
                listeners: {
                    change: Ext.bind(me.onChange, me)
                }
            };

        return Ext.create('Shopware.apps.SwagDigitalPublishing.view.editor.fields.ShadowField', configuration);
    },

    createTextPaddingField: function (data) {
        var me = this,
            configuration = {
                anchor: '100%',
                fieldLabel: me.snippets.paddingLabel,
                data: data,
                listeners: {
                    change: Ext.bind(me.onChange, me)
                }
            };

        return Ext.create('Shopware.apps.SwagDigitalPublishing.view.editor.fields.PaddingField', configuration);
    },

    createTextCssClassField: function (data) {
        var me = this,
            configuration = {
                anchor: '100%',
                fieldLabel: me.snippets.cssClassLabel,
                name: 'class',
                value: data['class'],
                listeners: {
                    change: Ext.bind(me.onChange, me)
                }
            };

        return Ext.create('Ext.form.field.Text', configuration);
    }
});
// {/block}
