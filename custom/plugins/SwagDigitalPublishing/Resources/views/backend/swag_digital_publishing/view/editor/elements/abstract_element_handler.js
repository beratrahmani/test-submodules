// {namespace name=backend/plugins/swag_digital_publishing/editor}
// {block name="backend/swag_digital_publishing/view/editor/abstract_element_handler"}
Ext.define('Shopware.apps.SwagDigitalPublishing.view.editor.elements.AbstractElementHandler', {

    /**
     * The system name of the element.
     */
    name: null,

    /**
     * The readable name of the element.
     */
    label: null,

    /**
     * The CSS class for the icon of the element.
     */
    iconCls: null,

    /**
     * Object holding all snippets used by the element.
     */
    snippets: {},

    /**
     * Returns the name of the element.
     *
     * @returns string
     */
    getName: function() {
        return this.name;
    },

    /**
     * Returns the label of the element.
     *
     * @returns string
     */
    getLabel: function() {
        return this.label;
    },

    /**
     * Returns the icon class of the element.
     *
     * @returns string
     */
    getIconCls: function() {
        return this.iconCls;
    },

    /**
     * Constructor method to build the settings panel for the element.
     *
     * @param editor
     * @param record
     * @param callback
     */
    createSettings: function(editor, record, callback) {
        var me = this;

        me.editor = editor;

        me.formPanel = me.createFormPanel(record);
        me.formPanel.add(me.createFormItems(record, me.getElementData(record)));

        Ext.callback(callback, me, [ me.formPanel ]);

        me.onFormInit(record, me.getElementData(record));
    },

    /**
     * Creates and returns the settings panel.
     *
     * @returns { Ext.form.Panel }
     */
    createFormPanel: function(record) {
        return Ext.create('Ext.form.Panel', {
            border: false,
            layout: 'anchor',
            bodyPadding: 10,
            overflowY: 'auto',
            defaults: {
                anchor: '100%',
                labelWidth: 100
            },
            plugins: [{
                ptype: 'translation',
                pluginId: 'translation',
                translationType: 'contentBannerElement',
                translationMerge: false,
                translationKey: record.get('id'),
                // add overwrite of this method because we need to adjust the Globe-Image.
                // if you add more translatable fields in this form, please check that you use this setting.
                getGlobeElementStyle: function () {
                    return 'top: 6px; right: 6px; z-index:1;';
                },
                // before Shopware versions 5.2
                getFieldType: function () {
                    return 'textfield';
                }
            }]
        });
    },

    onFormInit: function(elementRecord, data) {},

    /**
     * Creates and returns all form fields for the settings panel.
     *
     * @param elementRecord
     * @param data
     */
    createFormItems: function(elementRecord, data) {},

    /**
     * Returns the decoded payload of the element.
     *
     * @param elementRecord
     * @returns { * }
     */
    getElementData: function(elementRecord) {
        var payload = elementRecord.get('payload');

        return (payload !== null && payload.length) ? Ext.JSON.decode(elementRecord.get('payload')) : {};
    },

    /**
     * Updates the element record from the fields of the settings form.
     * This method has to be triggered on the change event of each field.
     *
     * @param formPanel
     * @param elementRecord
     */
    updateElementRecord: function(formPanel, elementRecord) {
        var me = this,
            dataString = formPanel.getForm().getValues(true),
            data = me.normalizeData(formPanel.getForm().getFieldValues());

        if (dataString.length) {
            elementRecord.set('payload', Ext.JSON.encode(data));
        }

        me.editor.updatePreview();
    },

    /**
     * @param { object } data
     */
    normalizeData: function (data) {
        var orientation = data['orientation'];

        Ext.Array.each(orientation, function (item) {
            if (item !== false) {
                data['orientation'] = item;
                return false;
            }
        });

        return data;
    },

    onChange: function () {
        var me = this;

        me.updateElementRecord(me.formPanel, me.record);
    }
});
// {/block}
