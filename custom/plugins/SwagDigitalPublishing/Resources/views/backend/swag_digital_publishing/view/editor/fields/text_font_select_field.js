// {namespace name=backend/plugins/swag_digital_publishing/editor}
// {block name="backend/swag_digital_publishing/view/editor/textFontSelectField"}
Ext.define('Shopware.apps.SwagDigitalPublishing.view.editor.fields.TextFontSelectField', {
    extend: 'Ext.form.field.ComboBox',

    helpText: '{s name="fontHelpText"}{/s}',

    valueField: 'font',

    displayField: 'label',

    editable: true,

    autoSelect: true,

    initComponent: function () {
        var me = this;

        me.createFontStore();
        me.createTemplate();

        me.callParent(arguments);
    },

    createTemplate: function () {
        var me = this;

        me.tpl = Ext.create('Ext.XTemplate',
            '<tpl for=".">',
            '   <div class="x-boundlist-item" style="font-size: 14px; font-family: {ldelim}font{rdelim}">{ldelim}label{rdelim}</div>',
            '</tpl>'
        );
    },

    /**
     * @returns { Ext.data.Store }
     */
    createFontStore: function () {
        var me = this;

        me.store = Ext.create('Ext.data.Store', {
            fields: ['font', 'label'],
            data: [
                { 'font': 'Arial', 'label': 'Arial'},
                { 'font': 'Arial Black', 'label': 'Arial Black'},
                { 'font': 'Arial Narrow', 'label': 'Arial Narrow'},
                { 'font': 'Open Sans', 'label': 'Open Sans'},
                { 'font': 'Trebuchet MS', 'label': 'Trebuchet MS'},
                { 'font': 'Verdana', 'label': 'Verdana'},
                { 'font': 'Tahoma', 'label': 'Tahoma'},
                { 'font': 'Georgia', 'label': 'Georgia'},
                { 'font': 'Palatino', 'label': 'Palatino'},
                { 'font': 'Garamond', 'label': 'Garamond'},
                { 'font': 'Century Gothic', 'label': 'Century Gothic'},
                { 'font': 'Lucida Bright', 'label': 'Lucida Bright'},
                { 'font': 'Book Antiqua', 'label': 'Book Antiqua'},
                { 'font': 'Times New Roman', 'label': 'Times New Roman'},
                { 'font': 'Lucida Sans Typewriter', 'label': 'Lucida Sans Typewriter'},
                { 'font': 'Courier New', 'label': 'Courier New'}
            ]
        });
    }
});
// {/block}
