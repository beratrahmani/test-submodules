// {namespace name=backend/plugins/swag_digital_publishing/editor}
// {block name="backend/swag_digital_publishing/view/editor/textTypeField"}
Ext.define('Shopware.apps.SwagDigitalPublishing.view.editor.fields.TextTypeField', {
    extend: 'Ext.form.field.ComboBox',

    valueField: 'type',
    displayField: 'label',

    allowBlank: false,
    editable: true,
    autoSelect: true,

    snippets: {
        labelHeadline1: '{s name="labelHeadline1"}{/s}',
        labelHeadline2: '{s name="labelHeadline2"}{/s}',
        labelHeadline3: '{s name="labelHeadline3"}{/s}',
        labelParagraph: '{s name="labelParagraph"}{/s}',
        labelBlockquote: '{s name="labelBlockquote"}{/s}'
    },

    initComponent: function () {
        var me = this;

        me.createTypeStore();

        me.callParent(arguments);
    },

    /**
     * @returns { Ext.data.Store }
     */
    createTypeStore: function () {
        var me = this;

        me.store = Ext.create('Ext.data.Store', {
            fields: ['type', 'label'],
            data: [
                { 'type': 'h1', 'label': me.snippets.labelHeadline1 },
                { 'type': 'h2', 'label': me.snippets.labelHeadline2 },
                { 'type': 'h3', 'label': me.snippets.labelHeadline3 },
                { 'type': 'p', 'label': me.snippets.labelParagraph },
                { 'type': 'blockquote', 'label': me.snippets.labelBlockquote }
            ]
        });
    }
});
// {/block}
