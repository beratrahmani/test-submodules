//{namespace name=backend/ticket/main}
//{block name="backend/ticket/controller/locale"}
Ext.define('Shopware.apps.Ticket.controller.Locale', {

    /**
     * The parent class that this class extends.
     * @string
     */
    extend: 'Ext.app.Controller',

    /**
     * Array of configs to build up references to views on page
     * @array
     */
    refs: [
        { ref: 'localePanel', selector: 'ticket-settings-locale' },
        { ref: 'localeWindow', selector: 'ticket-settings-add-locale-window' }
    ],

    /**
     * A template method that is called when your application boots.
     * It is called before the Application's launch function is executed
     * so gives a hook point to run any code before your Viewport is created.
     *
     * @return void
     */
    init: function () {
        var me = this;

        me.control({

            /** Submission settings */
            'ticket-settings-locale': {
                addLocale: me.onAddLocale,
                deleteLocale: me.onDeleteLocale,
                deleteLocales: me.onDeleteLocales
            },
            'ticket-settings-add-locale-window': {
                saveLocale: me.onSaveLocale
            }
        });
    },

    /**
     * Opens the "add locale" window.
     *
     * @public
     * @event click
     * @return void
     */
    onAddLocale: function () {
        var me = this;

        me.subApplication.unusedLocaleStore = me.subApplication.getStore('UnusedLocale');

        me.getView('settings.AddLocale').create({
            localeStore: me.subApplication.localeStore,
            unusedLocaleStore: me.subApplication.unusedLocaleStore
        });
    },

    /**
     * Event listener method which will be triggered when the user
     * clicks the "add shop specific submission" button.
     *
     * The method validates the form panel and sends an ajax request which duplicates
     * the submissions.
     *
     * @public
     * @event click
     * @return [boolean]
     */
    onSaveLocale: function () {
        var me = this,
            win = me.getLocaleWindow(),
            store = me.subApplication.localeStore,
            formPnl = win.formPanel,
            form = formPnl.getForm(),
            values = form.getValues();

        if (!form.isValid()) {
            Shopware.Notification.createGrowlMessage('{s name=window_title}Ticket system{/s}', '{s name=locale/error/forms_fill_all_fields}Please fill out all required fields (marked red) to save the form.{/s}');
            return false;
        }

        Ext.Ajax.request({
            url: '{url action="duplicateMails"}',
            params: values,
            callback: function () {
                store.load();
                win.destroy();
                me.subApplication.submissionStore.load();
            }
        });
    },

    /**
     * Event listener method which will be triggered when the user
     * clicks on the "delete submission" button.
     *
     * @public
     * @event click
     * @param [object] btn - Ext.button.Button
     * @param [object] view - Shopware.apps.Ticket.view.settings.Locale
     * @return [boolean]
     */
    onDeleteLocale: function (scope, record) {
        var me = this,
            store = me.subApplication.localeStore,
            grid = me.getLocalePanel(),
            selModel = grid.getSelectionModel(),
            selection = selModel.getSelection(),
            shopId = 0;

        record = record || undefined;

        // If we don't have a record, terminate it over the selection model of the grid.
        if (!record) {
            if (!selection) {
                return false;
            }
            record = selection[0];
        }

        Ext.MessageBox.confirm(
            '{s name=settings/locale_delete_title}Delete submission{/s}',
            '{s name=settings/locale_delete_msg}Deleting the shop specific templates will delete all related templates. Do you want to delete the shop specific templates anyway?{/s}',
            function (response) {
                if (response !== 'yes') {
                    return;
                }

                shopId = record.get('id');

                Ext.Ajax.request({
                    url: '{url action="deleteMailSubmissionByShopId"}',
                    params: {
                        shopId: ~~(1 * shopId)
                    },
                    callback: function () {
                        store.load();
                        me.subApplication.submissionStore.load();
                    }
                });
            }
        );
    },

    /**
     * Event listener method which will be triggered when
     * the user presses the "delete marked"-button.
     *
     * The method deletes the selected locales.
     *
     * @param { Ext.button.Button } btn
     * @param { Shopware.apps.Ticket.view.settings.Types } view
     */
    onDeleteLocales: function (btn, view) {
        var me = this,
            selModel = view.selModel,
            selected = selModel.getSelection();

        Ext.each(selected, function (item) {
            me.onDeleteLocale(me, item);
        });
    }
});
//{/block}
