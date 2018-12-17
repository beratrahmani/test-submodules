// {namespace name=backend/plugins/swag_digital_publishing/editor}
// {block name="backend/swag_digital_publishing/view/editor/image_element_handler"}
Ext.define('Shopware.apps.SwagDigitalPublishing.view.editor.elements.ImageElementHandler', {

    extend: 'Shopware.apps.SwagDigitalPublishing.view.editor.elements.AbstractElementHandler',

    name: 'image',

    label: '{s name="imageElementLabel"}{/s}',

    iconCls: 'digpub-icon--image',

    snippets: {
        imageSettingsLabel: '{s name="imageSettingsLabel"}{/s}',
        orientationFieldLabel: '{s name="orientationFieldLabel"}{/s}',
        paddingLabel: '{s name="paddingLabel"}{/s}',
        cssClassLabel: '{s name="cssClassLabel"}{/s}',
        maxWidthLabel: '{s name="maxWidthLabel"}{/s}',
        maxHeightLabel: '{s name="maxHeightLabel"}{/s}',
        alternativeTextLabel: '{s name="alternativeTextLabel"}{/s}'
    },

    createFormItems: function(elementRecord, data) {
        var me = this;
        me.record = elementRecord;

        me.generalFieldset = Ext.create('Ext.form.FieldSet', {
            title: me.snippets.imageSettingsLabel,
            layout: 'anchor',
            defaults: {
                anchor: '100%',
                labelWidth: 100
            },
            items: [
                me.createMediaField(data),
                me.createTextField('alt', me.snippets.alternativeTextLabel, data['alt'] || '', true),
                me.createNumberField('maxWidth', me.snippets.maxWidthLabel, data['maxWidth'] || 100),
                me.createNumberField('maxHeight', me.snippets.maxHeightLabel, data['maxHeight'] || 100),
                me.createOrientationField(data),
                me.createPaddingField(data),
                me.createTextField('class', me.snippets.cssClassLabel, data['class'] || '', false)
            ]
        });

        return me.generalFieldset;
    },

    createMediaField: function (data) {
        var me = this;

        return Ext.create('Shopware.apps.SwagDigitalPublishing.view.editor.fields.MediaField', {
            name: 'mediaId',
            value: data['mediaId'] || null,
            listeners: {
                'selectMedia': Ext.bind(me.onChange, me)
            }
        });
    },

    createNumberField: function (name, label, value) {
        var me = this;

        return Ext.create('Ext.form.field.Number', {
            name: name,
            fieldLabel: label,
            value: value,
            allowDecimals: false,
            minValue: 0,
            listeners: {
                'change': Ext.bind(me.onChange, me)
            }
        });
    },

    /**
     * @param { string } name
     * @param { string } label
     * @param { string } value
     * @param { bool } translatable
     * @return { Ext.form.field.Text }
     */
    createTextField: function (name, label, value, translatable) {
        var me = this;

        return Ext.create('Ext.form.field.Text', {
            name: name,
            fieldLabel: label,
            value: value,
            translatable: translatable,
            listeners: {
                'change': Ext.bind(me.onChange, me)
            }
        });
    },

    /**
     * @param { object } data
     * @return { Shopware.apps.SwagDigitalPublishing.view.editor.fields.TextOrientationField }
     */
    createOrientationField: function (data) {
        var me = this,
            configuration = {
                anchor: '100%',
                fieldLabel: me.snippets.orientationFieldLabel,
                data: data,
                settings: [
                    { name: 'left', spriteClass: 'sprite-edit-alignment' },
                    { name: 'center', spriteClass: 'sprite-edit-alignment-center' },
                    { name: 'right', spriteClass: 'sprite-edit-alignment-right' }
                ],
                listeners: {
                    change: Ext.bind(me.onChange, me)
                }
            };

        return Ext.create('Shopware.apps.SwagDigitalPublishing.view.editor.fields.TextOrientationField', configuration);
    },

    /**
     * @param { object } data
     * @return { Shopware.apps.SwagDigitalPublishing.view.editor.fields.PaddingField }
     */
    createPaddingField: function (data) {
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
    }
});
// {/block}
