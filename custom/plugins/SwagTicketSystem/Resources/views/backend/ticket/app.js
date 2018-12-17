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

//{block name="backend/ticket/app"}
Ext.define('Shopware.apps.Ticket', {

    /**
     * Extends from our special controller, which handles the
     * sub-application behavior and the event bus
     * @string
     */
    extend:'Enlight.app.SubApplication',

    /**
     * Sets the loading path for the sub-application.
     *
     * Note that you'll need a "loadAction" in your
     * controller (server-side)
     * @string
     */
    loadPath:'{url action=load}',

    /**
     * Enable bulk loading
     * @boolean
     */
    bulkLoad: true,

    /**
     * Requires controllers for sub-application
     * @array
     */
    controllers: [ 'Main', 'List', 'Submission', 'Types', 'Forms', 'Locale' ],

    /**
     * Required stores for controller
     * @array
     */
    stores: [ 'List', 'Employee', 'Status', 'Submission', 'Types', 'StatusCombo', 'Forms', 'Customer', 'Locale', 'UnusedLocale', 'History' ],

    /**
     * Required models for controller
     * @array
     */
    models: [ 'List', 'Employee', 'Status', 'Submission', 'Types', 'Forms', 'FormField', 'Mapping', 'Customer', 'Locale', 'History' ],

    /**
     * Required views for controller
     *
     * @array
     */
    views: [
        'main.Window', 'list.Overview', 'list.TicketInfo', 'settings.Submission', 'settings.Types', 'settings.types.Window',
        'settings.Forms', 'ticket.NewWindow', 'settings.Locale', 'settings.AddLocale', 'ticket.EditWindow', 'ticket.Attachment'
    ],

    /**
     * Returns the main application window for this is expected
     * by the Enlight.app.SubApplication class.
     * The class sets a new event listener on the "destroy" event of
     * the main application window to perform the destroying of the
     * whole sub application when the user closes the main application window.
     *
     * This method will be called when all dependencies are solved and
     * all member controllers, models, views and stores are initialized.
     *
     * @private
     * @return [object] mainWindow - the main application window based on Enlight.app.Window
     */
    launch:function () {
        var me = this,
            mainController = me.getController('Main');

        return mainController.mainWindow
    }
});
//{/block}

