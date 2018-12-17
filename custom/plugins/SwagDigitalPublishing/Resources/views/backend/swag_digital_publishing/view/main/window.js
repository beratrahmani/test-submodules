//{namespace name=backend/plugins/swag_digital_publishing/main}
//{block name="backend/swag_digital_publishing/view/main/window"}
Ext.define('Shopware.apps.SwagDigitalPublishing.view.main.Window', {

    extend: 'Enlight.app.Window',

    alias: 'widget.publishing-main-window',

    cls: Ext.baseCSSPrefix + 'swag-publishing-window',

    layout: {
        type: 'fit'
    },

    width: 1200,
    height: '95%',

    title: '{s name="moduleWindowTitle"}{/s}',

    initComponent: function () {
        var me = this;

        me.callParent(arguments);
    }
});
//{/block}