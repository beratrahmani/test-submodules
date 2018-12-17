//

//{namespace name="backend/advisor/main"}
//{block name="backend/advisor/view/details/main"}
Ext.define('Shopware.apps.Advisor.view.details.Main', {
    extend: 'Shopware.window.Detail',
    alias: 'widget.DetailsMain',
    title: '{s name="advisor_title"}Shopping Advisor{/s}',

    layout: 'fit',
    width: '60%',
    height: '85%',

    // Preventing to open more than one detail window at the same time
    modal: true,
    minimizable: false,

    snippets: {
        streamSelection: '{s name="tabs_title_stream_selection"}Stream selection{/s}',
        resultConfig: '{s name="tabs_title_result_config"}Result configuration{/s}',
        warningTitle: '{s name="main_save_warning_title"}Attention{/s}',
        warning: '<p>{s name="main_save_warning"}You have your changes have NOT stored. Changes will be lost. <br />Do you want this still save?{/s}',
        btnSave: '{s name="btn_save_text"}Save{/s}',
        btndoNotSave: '{s name="btn_do_not_save_text"}Don\'t save{/s}',
        btnCancel: '{s name="btn_cancel_text"}Cancel{/s}',
        invalidFormTitle: '{s name="growl_message_form_title"}Missing information{/s}',
        invalidFormMessage: '{s name="growl_message_form_message"}The following fields must be filled:{/s}',
    },

    initComponent: function() {
        var me = this;

        me.callParent(arguments);

        me.requiredFieldNames = [];
        me.requiredFieldNamesLabels= {};
    },

    /**
     * @overwrite
     *
     * At this point we must replace the handler of the "Close" toolButton,
     * so that we achieve a consistent behavior when closing the window.
     */
    afterRender: function () {
        var me = this,
            queryResult;

        me.callParent(arguments);

        me.checker = Ext.create('Shopware.apps.Advisor.view.components.IsSavedChecker');
        me.checker.setAdvisor(me.record);

        queryResult = me.query('tool');

        Ext.Array.each(queryResult, function (headerElement) {
            if (headerElement.type == 'close') {
                headerElement.handler = function () {
                    me.onCancel();
                }
            }
        });

        me.getRequiredFields();
    },

    /**
     * Collect all fields that can not be empty to validate this later.
     */
    getRequiredFields: function () {
        var me = this;

        Ext.Array.each(me.formPanel.getForm().getFields().items, function (field) {
            if (!field.allowBlank) {
                me.requiredFieldNames.push(field.getName());
                me.requiredFieldNamesLabels[field.getName()] = field.getFieldLabel();
            }
        });
    },

    /**
     * @returns { { associations: string[] } }
     */
    configure: function () {
        return {
            associations: ['questions'],
            translationKey: 'advisorBasic'
        }
    },

    /**
     * Check that the form is valid..
     * Else Show necessary fields in growl Message
     *
     * @overwrite
     */
    onSave: function () {
        var me = this,
            message = me.snippets.invalidFormMessage + '<br />',
            streamSelection = me.formPanel.down('productstreamselection').getValue();

        if (!me.formPanel.getForm().isValid() || Ext.isEmpty(streamSelection)) {
            message += me.getEmptyFields();

            Shopware.Notification.createGrowlMessage(
                me.snippets.invalidFormTitle,
                message
            );
            return;
        }

        me.callParent(arguments);
    },

    /**
     * check all fields who are required. If one is empty, add the name to the invalidFormMessage.
     */
    getEmptyFields: function () {
        var me = this,
            messageAdd = '';

        Ext.Array.each(me.requiredFieldNames, function (field) {
            field = me.formPanel.down('[name=' + field + ']');
            if (!field.isValid() || (field.getName() === 'streamId' && Ext.isEmpty(field.getValue()))) {
                messageAdd += '<b>' + me.requiredFieldNamesLabels[field.getName()] + '</b><br />';
            }
        });

        return messageAdd;
    },

    /**
     * @overwrite
     */
    onCancel: function () {
        var me = this;

        me.destroy();
    },

    /**
     * @overwrite
     *
     * on close we check the ProductAdvisor for unsaved changes.
     * If are there unsaved changes we show a message about this changes
     * and the user can choose what is todo.
     *
     * @param { boolean= } forceDestroy
     */
    destroy: function (forceDestroy) {
        var me = this;

        forceDestroy = forceDestroy || false;

        if (!me.checker.isSaved(me.record, me.formPanel) && !forceDestroy) {
            me.showUnsavedWarning();
        } else {
            me.fireEvent('advisor_detail_main_before_destroy');
            me.requiredFieldNames = [];
            me.requiredFieldNamesLabels = {};
            me.callParent(arguments);
            me.fireEvent('advisor_detail_main_after_destroy');
        }
    },

    /**
     * Shows a message that the current
     * state of the advisor has not saved.
     */
    showUnsavedWarning: function () {
        var me = this,
            messageBox = me.createMessageBox();

        messageBox.show({ buttons: messageBox.YESNOCANCEL });
    },

    /**
     * @returns { Shopware.apps.Advisor.view.details.ui.MessageBox }
     */
    createMessageBox: function () {
        var me = this;

        return Ext.create('Shopware.apps.Advisor.view.details.ui.MessageBox', {
            title: me.snippets.warningTitle,
            message: me.snippets.warning,
            yesButtonText: me.snippets.btnSave,
            noButtonText: me.snippets.btndoNotSave,
            callBack: Ext.bind(me.messageBoxCallback, me)
        });
    },

    /**
     * the callback function for the messageBox.
     *
     * @param { string } btn
     */
    messageBoxCallback: function (btn) {
        var me = this;

        if (btn == 'no') {
            me.destroy(true);
        }

        if (btn == 'yes') {
            if (me.formPanel.getForm().isValid()) {
                me.onSave();
                me.destroy(true);
            } else {
                me.onSave();
            }
        }
    },

    /**
     * @overwrite
     *
     * @param { string } type
     * @param { Ext.data.Model } model
     * @param { Ext.data.Store } store
     * @param { * } association
     * @param { Ext.data.Model } baseRecord
     *
     * @returns { * }
     */
    createAssociationComponent: function (type, model, store, association, baseRecord) {
        var me = this,
            item = me.callParent(arguments);

        item.advisor = baseRecord;

        return item;
    },

    /**
     * @overwrite
     *
     * @returns { * }
     */
    createTabItems: function () {
        var me = this,
            items = me.callParent(arguments);

        // add Stream selection on position "1" of the "0" based array
        Ext.Array.insert(items, 1, [me.createStreamConfiguration()]);

        // push resultConfiguration to the end of the array
        items.push(me.createResultConfiguration());

        items.forEach(function (item, index) {
            item.index = index;
        });

        return items;
    },

    /**
     * @returns { Shopware.apps.Advisor.view.details.Stream | * }
     */
    createStreamConfiguration: function () {
        var me = this;

        me.streamConfiguration = Ext.create('Shopware.apps.Advisor.view.details.Stream', {
            record: me.record
        });

        me.streamConfiguration.title = me.snippets.streamSelection;

        return me.streamConfiguration;
    },

    /**
     * @returns { Shopware.apps.Advisor.view.details.ResultConfig | * }
     */
    createResultConfiguration: function () {
        var me = this;

        me.resultConfiguration = Ext.create('Shopware.apps.Advisor.view.details.ResultConfig', {
            record: me.record
        });

        me.resultConfiguration.title = me.snippets.resultConfig;

        return me.resultConfiguration;
    },

    /**
     * Re-initializes the translation-plugin when changing the tab.
     */
    onTabChange: function () {
        var me = this;

        me.callParent(arguments);

        me.initTranslationPluginForAdds();
    },

    /**
     * Re-initializes the translation fields.
     */
    initTranslationPluginForAdds: function () {
        var me = this,
            formPanel = me.formPanel;

        if (!formPanel.translationPlugin) {
            return;
        }

        formPanel.translationPlugin.initTranslationFields(formPanel);
    }
});
//{/block}