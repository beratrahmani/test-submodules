//{namespace name=backend/plugins/swag_digital_publishing/editor}
//{block name="backend/swag_digital_publishing/view/editor/settings/layer"}
Ext.define('Shopware.apps.SwagDigitalPublishing.view.editor.settings.Layer', {

    extend: 'Ext.form.Panel',

    alias: 'widget.publishing-editor-settings-layer',

    cls: Ext.baseCSSPrefix + 'swag-publishing-settings-layer shopware-form',

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
        positionFieldLabel: '{s name="positionFieldLabel"}{/s}',
        widthFieldLabel: '{s name="widthFieldLabel"}{/s}',
        heightFieldLabel: '{s name="heightFieldLabel"}{/s}',
        marginFieldLabel: '{s name="marginFieldLabel"}{/s}',
        borderRadiusFieldLabel: '{s name="borderRadiusFieldLabel"}{/s}',
        layerSettingsLabel: '{s name="layerSettingsLabel"}{/s}',
        linkFieldLabel: '{s name="linkFieldLabel"}{/s}',
        sizeHelpText: '{s name="sizeHelpText"}{/s}',
        linkHelpText: '{s name="linkHelpText"}{/s}',
        linkLayerHelpText: '{s name="linkLayerHelpText"}{/s}'
    },

    /**
     * init this component
     */
    initComponent: function() {
        var me = this;

        me.plugins = me.createPlugins();

        me.nodeId = 'layer' + me.record.getId();

        me.items = me.createFormFields();

        me.callParent(arguments);

        me.getForm().loadRecord(me.record);
    },

    /**
     * Create the translation plugin
     * @returns { * }
     */
    createPlugins: function () {
        var me = this;

        if(!me.record.get('id')) {
            return [];
        }

        return [{
            ptype: 'translation',
            pluginId: 'translation',
            translationType: 'digipubLink',
            translationMerge: false,
            translationKey: me.record.get('id'),
            // add overwrite of this method because we need to adjust the Globe-Image.
            // if you add more translatable fields in this form, please check that you use this setting.
            getGlobeElementStyle: function () {
                return 'top: 6px; right: 6px; z-index:1;';
            },
            // before Shopware versions 5.2
            getFieldType: function () {
                return 'textfield';
            }
        }];
    },
    
    /**
     * @returns { *[] }
     */
    createFormFields: function() {
        var me = this;

        me.generalFieldset = Ext.create('Ext.form.FieldSet', {
            title: me.snippets.layerSettingsLabel,
            layout: 'anchor',
            defaults: {
                anchor : '100%',
                labelWidth: 80
            }
        });

        me.nameField = Ext.create('Ext.form.field.Text', {
            name: 'label',
            fieldLabel: me.snippets.nameFieldLabel,
            allowBlank: false,
            listeners: {
                change: Ext.bind(me.updateRecord, me)
            }
        });

        me.widthField = Ext.create('Ext.form.field.Text', {
            name: 'width',
            fieldLabel: me.snippets.widthFieldLabel,
            helpText: me.snippets.sizeHelpText,
            listeners: {
                change: Ext.bind(me.updateRecord, me)
            }
        });

        me.heightField = Ext.create('Ext.form.field.Text', {
            name: 'height',
            fieldLabel: me.snippets.heightFieldLabel,
            helpText: me.snippets.sizeHelpText,
            listeners: {
                change: Ext.bind(me.updateRecord, me)
            }
        });

        me.marginFields = Ext.create('Ext.container.Container', {
            items: [{
                xtype: 'container',
                layout: 'hbox',
                margin: '20 0 10 172',
                items: [{
                    xtype: 'numberfield',
                    name: 'marginTop',
                    allowDecimals: false,
                    minValue: 0,
                    width: 60,
                    listeners: {
                        change: Ext.bind(me.onMarginChange, me)
                    }
                }]
            }, {
                xtype: 'container',
                layout: 'hbox',
                margin: '0 0 10 0',
                cls: 'icon-selection',
                items: [{
                    xtype: 'numberfield',
                    name: 'marginLeft',
                    fieldLabel: me.snippets.marginFieldLabel,
                    allowDecimals: false,
                    minValue: 0,
                    width: 165,
                    listeners: {
                        change: Ext.bind(me.onMarginChange, me)
                    }
                }, {
                    xtype: 'checkbox',
                    name: 'marginChain',
                    boxLabel: '<span class="sprite-chain" style="background-position: -155px -155px !important;"></span>',
                    width: 50,
                    margin: '0 10'
                }, {
                    xtype: 'numberfield',
                    name: 'marginRight',
                    allowDecimals: false,
                    minValue: 0,
                    width: 60,
                    listeners: {
                        change: Ext.bind(me.onMarginChange, me)
                    }
                }]
            }, {
                xtype: 'container',
                layout: 'hbox',
                margin: '0 0 20 172',
                items: [{
                    xtype: 'numberfield',
                    name: 'marginBottom',
                    allowDecimals: false,
                    minValue: 0,
                    width: 60,
                    listeners: {
                        change: Ext.bind(me.onMarginChange, me)
                    }
                }]
            }]
        });

        me.radiusField = Ext.create('Ext.form.field.Number', {
            name: 'borderRadius',
            fieldLabel: me.snippets.borderRadiusFieldLabel,
            allowDecimals: false,
            minValue: 0,
            listeners: {
                change: Ext.bind(me.updateRecord, me)
            }
        });

        me.orientationField = Ext.create('Ext.form.RadioGroup', {
            fieldLabel: me.snippets.positionFieldLabel,
            columns: 3,
            anchor: 'none',
            margin: '10 0 10 0',
            defaults: {
                margin: 0
            },
            listeners: {
                change: Ext.bind(me.updateRecord, me)
            },
            items: [
                { name: 'orientation', inputValue: 'top left' },
                { name: 'orientation', inputValue: 'top center' },
                { name: 'orientation', inputValue: 'top right' },
                { name: 'orientation', inputValue: 'center left' },
                { name: 'orientation', inputValue: 'center center' },
                { name: 'orientation', inputValue: 'center right' },
                { name: 'orientation', inputValue: 'bottom left' },
                { name: 'orientation', inputValue: 'bottom center' },
                { name: 'orientation', inputValue: 'bottom right' }
            ]
        });

        me.colorField = Ext.create('Shopware.form.field.ColorField', {
            fieldLabel: me.snippets.backgroundFieldLabel,
            name: 'bgColor'
        });
        me.colorField.inputField.on('change', Ext.bind(me.updateRecord, me));

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
            anchor:'100%',
            multiSelect: false,
            getValue: function() {
                return this.getSearchField().getValue();
            },
            setValue: function(value) {
                this.getSearchField().setValue(value);
            }
        });

        me.linkField.searchField.helpText = me.snippets.linkHelpText + ' ' + me.snippets.linkLayerHelpText;
        me.linkField.setValue(me.record.get('link'));
        me.linkField.getSearchField().on('change', Ext.bind(me.updateRecord, me));

        me.generalFieldset.add([
            me.nameField,
            me.orientationField,
            me.widthField,
            me.heightField,
            me.marginFields,
            me.radiusField,
            me.colorField,
            me.linkField
        ]);

        return [
            me.generalFieldset
        ]
    },

    /**
     * @param field
     * @param newValue
     */
    onMarginChange: function(field, newValue) {
        var me = this,
            form = me.getForm(),
            fields = ['marginTop', 'marginLeft', 'marginRight', 'marginBottom'],
            chaining = form.findField('marginChain').getValue();

        if (chaining) {
            Ext.each(fields, function(fieldName) {
                if (fieldName !== field.getName()) {
                    var marginField = form.findField(fieldName);

                    marginField.suspendEvents();
                    marginField.setValue(newValue);
                    marginField.resumeEvents();
                }
            });
        }

        me.updateRecord();
    },

    updateRecord: function() {
        var me = this;

        me.editor.updateRecord(me, me.record);
    }
});
//{/block}