//{namespace name=backend/plugins/swag_digital_publishing/main}
//{block name="backend/swag_digital_publishing/view/main/container"}
Ext.define('Shopware.apps.SwagDigitalPublishing.view.main.Container', {

    extend: 'Ext.tab.Panel',

    alias: 'widget.publishing-main-container',

    cls: Ext.baseCSSPrefix + 'swag-publishing-container',

    initComponent: function() {
        var me = this;

        me.items = [
            me.createOverviewTab()
        ];

        me.callParent(arguments);
    },

    createOverviewTab: function() {
        var me = this;

        me.overviewTab = Ext.create('Shopware.apps.SwagDigitalPublishing.view.main.Listing', {
            store: me.bannerStore
        });

        return me.overviewTab;
    }
});
//{/block}