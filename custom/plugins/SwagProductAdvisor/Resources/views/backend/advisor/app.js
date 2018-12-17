/**
 * Shopware Premium Plugins
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this plugin can be used under
 * a proprietary license as set forth in our Terms and Conditions,
 * section 2.1.2.2 (Conditions of Usage).
 *
 * The text of our proprietary license additionally can be found at and
 * in the LICENSE file you have received along with this plugin.
 *
 * This plugin is distributed in the hope that it will be useful,
 * with LIMITED WARRANTY AND LIABILITY as set forth in our
 * Terms and Conditions, sections 9 (Warranty) and 10 (Liability).
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the plugin does not imply a trademark license.
 * Therefore any rights, title and interest in our trademarks
 * remain entirely with us.
 */

//{namespace name="backend/advisor/main"}
//{block name="backend/advisor/view/advisor"}
Ext.define('Shopware.apps.Advisor', {
    extend: 'Enlight.app.SubApplication',
    name: 'Shopware.apps.Advisor',
    bulkLoad: true,

    loadPath: '{url action=load}',

    controllers: [
        'Listing'
    ],

    models: [
        'Advisor',
        'Stream',
        'Question',
        'Answer',
        'Product'
    ],

    views: [
        'main.Listing',
        'main.Grid',

        'details.Stream',

        'details.Main',
        'details.Advisor',
        'details.ResultConfig',

        'details.questions.Window',
        'details.questions.Question',
        'details.questions.AbstractQuestion',
        'details.questions.Attribute',
        'details.questions.Manufacturer'
    ],

    stores: [
        'ListingStore',
        'StreamPreview'

    ],

    launch: function () {
        var me = this,
            mainController = me.getController('Listing');

        return mainController.mainWindow;
    }
});
//{/block}