// {namespace name=backend/plugins/swag_digital_publishing/editor}
// {block name="backend/swag_digital_publishing/view/editor/button_element_handler"}
Ext.define('Shopware.apps.SwagDigitalPublishing.view.editor.elements.ButtonElementHandler', {

    extend: 'Shopware.apps.SwagDigitalPublishing.view.editor.elements.AbstractElementHandler',

    name: 'button',

    label: '{s name="buttonElementLabel"}{/s}',

    iconCls: 'digpub-icon--button',

    snippets: {
        buttonSettingsLabel: '{s name="buttonSettingsLabel"}{/s}',
        linkFieldLabel: '{s name="linkFieldLabel"}{/s}',
        buttonTextLabel: '{s name="buttonTextLabel"}{/s}',
        typeLabel: '{s name="typeLabel"}{/s}',
        targetLabel: '{s name="targetLabel"}{/s}',
        paddingLabel: '{s name="paddingLabel"}{/s}',
        orientationFieldLabel: '{s name="orientationFieldLabel"}{/s}',
        cssClassLabel: '{s name="cssClassLabel"}{/s}',
        typeStandardLabel: '{s name="typeStandardLabel"}{/s}',
        typeSecondaryLabel: '{s name="typeSecondaryLabel"}{/s}',
        typePrimaryLabel: '{s name="typePrimaryLabel"}{/s}',
        targetSelfLabel: '{s name="targetSelfLabel"}{/s}',
        targetBlankLabel: '{s name="targetBlankLabel"}{/s}',
        linkHelpText: '{s name="linkHelpText"}{/s}',
        widthLabel: '{s name="widthFieldLabel"}{/s}',
        heightLabel: '{s name="heightFieldLabel"}{/s}',
        fontSizeLabel: '{s name="fontSizeLabel"}{/s}',
        labelAutoSize: '{s name="labelAutoSize"}{/s}'
    },

    /**
     * @param { Ext.data.Model }elementRecord
     * @param { object } data
     */
    onFormInit: function(elementRecord, data) {
        var me = this;
        me.disableSizeFields(data['autoSize'] == null || data['autoSize']);
    },

    /**
     * @param { Ext.data.Model }elementRecord
     * @param { object } data
     */
    createFormItems: function(elementRecord, data) {
        var me = this;

        me.record = elementRecord;

        me.generalFieldset = Ext.create('Ext.form.FieldSet', {
            title: me.snippets.buttonSettingsLabel,
            layout: 'anchor',
            defaults: {
                anchor: '100%',
                labelWidth: 100
            },
            items: [
                me.createTextField('text', me.snippets.buttonTextLabel, true, data['text'] || ''),
                me.createTypeCombo(data),
                me.createTargetCombo(data),
                me.createLinkField(data),
                me.createOrientationField(data),
                me.createPaddingField(data),
                me.createAutoSizeCheckBox(data),
                me.createNumberField('width', me.snippets.widthLabel, data['width'] || 200),
                me.createNumberField('height', me.snippets.heightLabel, data['height'] || 38),
                me.createNumberField('fontsize', me.snippets.fontSizeLabel, data['fontsize'] || 38),
                me.createTextField('class', me.snippets.cssClassLabel, false, data['class'] || '')
            ]
        });

        return me.generalFieldset;
    },

    /**
     * @param { bool } disabled
     */
    disableSizeFields: function(disabled) {
        var me = this,
            form = me.formPanel.getForm(),
            fields = ['height', 'width', 'fontsize'];

        Ext.each(fields, function(fieldName) {
            var sizeField = form.findField(fieldName);

            if (sizeField !== null) {
                // A little workaround, since the "setDisabled" method sets all values to 0 but we want to keep them.
                // Therefore we need to "fake" the disabled state and use the read-only flag instead
                sizeField.setReadOnly(disabled);

                // style as "disabled"
                if (disabled) {
                    sizeField.setFieldStyle('background: #DEDEDE');
                } else {
                    sizeField.setFieldStyle('background: white');
                }
            }
        });
    },

    /**
     * @param { Ext.form.field.Checkbox } field
     * @param { bool } newValue
     */
    onAutoSizeChange: function(field, newValue) {
        var me = this;

        me.disableSizeFields(newValue);
        me.updateElementRecord(me.formPanel, me.record);
    },

    /**
     * @return { Ext.data.Store }
     */
    createTypeStore: function() {
        var me = this;

        return Ext.create('Ext.data.Store', {
            fields: ['type', 'label'],
            data: [
                { 'type': 'standard', 'label': me.snippets.typeStandardLabel },
                { 'type': 'is--secondary', 'label': me.snippets.typeSecondaryLabel },
                { 'type': 'is--primary', 'label': me.snippets.typePrimaryLabel }
            ]
        });
    },

    /**
     * @return { Ext.data.Store }
     */
    createTargetStore: function() {
        var me = this;

        return Ext.create('Ext.data.Store', {
            fields: ['target', 'label'],
            data: [
                { 'target': '_self', 'label': me.snippets.targetSelfLabel },
                { 'target': '_blank', 'label': me.snippets.targetBlankLabel }
            ]
        });
    },

    /**
     * @param { object } data
     * @return { Shopware.form.field.ArticleSearch }
     */
    createLinkField: function (data) {
        var me = this;

        me.linkField = Ext.create('Shopware.form.field.ArticleSearch', {
            fieldLabel: me.snippets.linkFieldLabel,
            hiddenFieldName: 'link-search',
            searchFieldName: 'link',
            articleStore: Ext.create('Shopware.store.Article'),
            returnValue: 'number',
            hiddenReturnValue: 'number',
            formFieldConfig: {
                translatable: true
            },
            anchor: '100%',
            multiSelect: false,
            getValue: function() {
                return this.getSearchField().getValue();
            },
            setValue: function(value) {
                this.getSearchField().setValue(value);
            }
        });

        me.linkField.searchField.helpText = me.snippets.linkHelpText;
        me.linkField.setValue(data['link']);
        me.linkField.getSearchField().on(
            'change', Ext.bind(me.updateElementRecord, me, [ me.formPanel, me.record ])
        );

        return me.linkField;
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
    },

    /**
     * @param { object } data
     * @return { Ext.form.field.ComboBox }
     */
    createTargetCombo: function (data) {
        var me = this;

        return Ext.create('Ext.form.field.ComboBox', {
            name: 'target',
            fieldLabel: me.snippets.targetLabel,
            valueField: 'target',
            displayField: 'label',
            value: data['target'] || '_self',
            allowBlank: false,
            editable: false,
            forceSelection: true,
            autoSelect: true,
            store: me.createTargetStore(),
            listeners: {
                change: Ext.bind(me.onChange, me)
            }
        });
    },

    /**
     * @param { object } data
     * @return { Ext.form.field.ComboBox }
     */
    createTypeCombo: function (data) {
        var me = this;

        return Ext.create('Ext.form.field.ComboBox', {
            name: 'type',
            fieldLabel: me.snippets.typeLabel,
            valueField: 'type',
            displayField: 'label',
            value: data['type'] || 'standard',
            allowBlank: false,
            editable: false,
            forceSelection: true,
            autoSelect: true,
            store: me.createTypeStore(),
            listeners: {
                change: Ext.bind(me.onChange, me)
            }
        });
    },

    /**
     * @param { string } name
     * @param { string } label
     * @param { int } value
     * @return { Ext.form.field.Number }
     */
    createNumberField: function (name, label, value) {
        var me = this;

        return Ext.create('Ext.form.field.Number', {
            name: name,
            fieldLabel: label,
            value: value,
            listeners: {
                change: Ext.bind(me.onChange, me)
            }
        });
    },

    /**
     * @param { string } name
     * @param { string } label
     * @param { bool } translatable
     * @param { string } value
     * @return { Ext.form.field.Text }
     */
    createTextField: function (name, label, translatable, value) {
        var me = this;

        return Ext.create('Ext.form.field.Text', {
            anchor: '100%',
            name: name,
            fieldLabel: label,
            translatable: translatable,
            value: value,
            listeners: {
                change: Ext.bind(me.onChange, me)
            }
        });
    },

    /**
     * @param { object } data
     * @return { Ext.form.field.Checkbox }
     */
    createAutoSizeCheckBox: function (data) {
        var me = this;

        return Ext.create('Ext.form.field.Checkbox', {
            name: 'autoSize',
            boxLabel: me.snippets.labelAutoSize,
            value: data['autoSize'] == null || data['autoSize'],
            checked: data['autoSize'] == null || data['autoSize'],
            listeners: {
                change: Ext.bind(me.onAutoSizeChange, me)
            }
        });
    }
});
// {/block}
