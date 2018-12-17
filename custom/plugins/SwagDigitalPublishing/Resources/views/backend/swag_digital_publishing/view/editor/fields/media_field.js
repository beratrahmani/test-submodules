//{namespace name=backend/plugins/swag_digital_publishing/editor}
//{block name="backend/swag_digital_publishing/view/editor/mediaField"}
Ext.define('Shopware.apps.SwagDigitalPublishing.view.editor.fields.MediaField', {

    extend: 'Shopware.form.field.Media',

    layout: {
        type: 'vbox',
        align: 'stretch'
    },

    height: 190,

    onSelectMedia: function() {
        var me = this;

        me.callParent(arguments);
        me.fireEvent('selectMedia');
    },

    createPreviewContainer: function() {
        var me = this;

        me.callParent(arguments);

        me.previewContainer.style = "background: #fff; text-align: center;";

        return me.previewContainer;
    },

    createPreview: function() {
        var me = this;

        me.callParent(arguments);

        me.preview.margin = '10 5';

        return me.preview;
    }
});
//{/block}