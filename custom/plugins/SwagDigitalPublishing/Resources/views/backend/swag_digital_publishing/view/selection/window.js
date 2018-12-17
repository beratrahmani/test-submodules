//{namespace name=backend/plugins/swag_digital_publishing/main}
//{block name="backend/swag_digital_publishing/view/selection/window"}
Ext.define('Shopware.apps.SwagDigitalPublishing.view.selection.Window', {

    extend: 'Enlight.app.Window',

    alias: 'widget.publishing-selection-window',

    cls: Ext.baseCSSPrefix + 'swag-publishing-selection-window',

    layout: {
        type: 'fit'
    },

    width: 600,
    height: 400,

    maxWidth: 600,
    maxHeight: 400,

    minimizable: false,
    maximizable: false,
    resizable: false,
    forceToFront: true,

    title: '{s name="moduleWindowTitle"}{/s}',

    initComponent: function () {
        var me = this;

        me.callParent(arguments);
    }
});
//{/block}