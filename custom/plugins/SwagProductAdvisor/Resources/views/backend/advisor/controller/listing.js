//

//{namespace name="backend/advisor/main"}
//{block name="backend/advisor/controller/listing"}
Ext.define('Shopware.apps.Advisor.controller.Listing', {
    extend: 'Ext.app.Controller',
    mainWindow: null,

    refs: [
        { ref: 'listing', selector: 'AdvisorListingGrid' },
        { ref: 'listingAddButton', selector: 'AdvisorListingGrid button[name=add]' },
        { ref: 'details', selector: 'DetailsMain' }
    ],

    url: {
        deleteAdvisor: '{url controller=Advisor action=deleteAdvisor}',
        duplicateAdvisor: '{url controller=Advisor action=cloneAdvisorAjax}'
    },

    snippets: {
        duplicateTitle: '{s name="listing_create_copy_title"}Create copy?{/s}',
        duplicateMessage: '{s name="listing_create_copy_message"}Do you really want to create a copy from this Advisor?{/s}'
    },

    selectionResults: {
        sideBarMode: 'sidebar_mode',
        wizardMode: 'wizard_mode'
    },

    /**
     * @overwrite
     */
    init: function () {
        var me = this;

        me.mainWindow = me.getView('main.Listing').create({ }).show();

        me.control({
            'AdvisorListingGrid': {
                'listing-add-advisor': me.addAdvisor,
                'listing-duplicate-advisor': me.onDuplicateAdvisor
            },

            'DetailsMain': {
                'advisor_detail_main_after_destroy': me.afterDestroyDetailWindow
            }
        });

        me.callParent(arguments);

        Shopware.app.Application.on('advisor-save-successfully', function (controller, data, window, record) {
            record.reload({
                callback: function (result) {
                    me.recordReloadCallback(result, controller, data, window);
                }
            });
        });
    },

    /**
     * Load the listing after Saving the Advisor
     */
    afterDestroyDetailWindow: function () {
        var me = this;
        Ext.defer(function () {
            me.getListing().getStore().load();
        }, 400);
    },

    /**
     * @param { Shopware.apps.Advisor.model.Advisor } record
     */
    onDuplicateAdvisor: function (record) {
        var me = this;

        Ext.Msg.show({
            title: me.snippets.duplicateTitle,
            msg: me.snippets.duplicateMessage,
            buttons: Ext.Msg.YESNO,
            fn: function (btn, text) {
                if (btn == 'yes') {
                    me.callAjax(
                        me.url.duplicateAdvisor,
                        { id: record.get('id') },
                        function () {
                            me.getListing().getStore().load();
                        }
                    );

                }
            }
        });
    },

    /**
     * create the mode selectionWindow
     */
    addAdvisor: function () {
        var me = this;

        Ext.create('Shopware.apps.Advisor.view.main.mode.Selection', {
            modal: true,
            minimizable: false,
            maximizable: false,
            resizable: false
        }).show();
    },

    /**
     * @param { String } url
     * @param { * } params
     * @param  { function } callback
     */
    callAjax: function (url, params, callback) {
        Ext.Ajax.request({
            url: url,
            params: params,
            success: function (response) {
                var text = response.responseText;
                callback();
            }
        });
    },

    /**
     * After a successfully reload of the record we close and reopen the window.
     * 
     * @param { Ext.data.Model } result
     * @param { Enlight.app.Controller } controller
     * @param { object } data
     * @param { Ext.window.Window } window
     */
    recordReloadCallback: function (result, controller, data, window) {
        var me = this,
            // get the current tabIndex
            index = window.tabPanel.activeTab.index,
            top = window.y,
            left = window.x;

        // destroy the window and create a new one
        // this is a workaround for prevent the deep association displaying-bug.
        window.destroy(true);
        window = Ext.create('Shopware.apps.Advisor.view.details.Main', {
            record: result
        }).show().setPosition(left, top, false);
        // set the tabIndex
        window.tabPanel.setActiveTab(index);

        // reload the advisorListingStore to display the new Advisor
        me.getListing().getStore().load();
    }
});
//{/block}