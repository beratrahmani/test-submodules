//{block name="backend/swag_promotion/controller/main"}
Ext.define('Shopware.apps.SwagPromotion.controller.Main', {
    extend: 'Enlight.app.Controller',

    init: function () {
        var me = this;

        me.mainWindow = me.getView('list.Window').create({}).show();
    }
});
//{/block}
