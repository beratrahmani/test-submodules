
//{namespace name=backend/ticket/main}
//{block name="backend/ticket/controller/forms"}
Ext.define('Shopware.apps.Ticket.controller.Forms', {

    /**
     * The parent class that this class extends.
     * @string
     */
    extend:'Ext.app.Controller',

    /**
     * Array of configs to build up references to views on page
     * @array
     */
    refs: [
        { ref: 'formsPanel', selector: 'ticket-settings-forms' }
    ],

    /**
     * A template method that is called when your application boots.
     * It is called before the Application's launch function is executed
     * so gives a hook point to run any code before your Viewport is created.
     *
     * @return void
     */
    init:function () {
        var me = this;

        me.control({
            'ticket-settings-forms': {
                saveMapping: me.onSaveMapping
            },
            'ticket-settings-forms grid': {
                selectionchange: me.onSelectionChange
            }
        });
    },

    /**
     * Event listener method which will be triggered when
     * the user selects an entry in the navigation panel
     * on the left side of the module.
     *
     * The method unlocks the form panel and sets the store
     * for the combo boxes.
     *
     * @public
     * @event selectionchange
     * @param [object] selModel - Ext.selection.SelectionModel
     * @param [object] record - Shopware.apps.Ticket.model.Forms
     * @return boolean
     */
    onSelectionChange: function(selModel, record) {
        if(!record[0]) {
            return false;
        }
        record = record[0];
        var fields = record.getFormFieldsStore,
            me = this,
            formsPanel = me.getFormsPanel(),
            mappingFields = formsPanel.mappingFields,
            formPnl = formsPanel.formPanel,
            mappingStore = record.getFormMappingStore,
            mappingRecord = mappingStore.first();

        if(formPnl.isDisabled()) {
            formPnl.setDisabled(false);
        }

        formPnl.loadRecord(record);
        Ext.each(mappingFields, function(item) {
            item.bindStore(fields);

            if(mappingRecord.data[item.getName()]) {
                item.setValue(mappingRecord.data[item.getName()]);
            } else {
                item.reset();
            }
        });
    },

    /**
     * Event listener methid which triggers when the user clicks
     * on the button "save mapping".
     *
     * The method saves the form and the associated mapping through
     * two calls.
     *
     * @public
     * @event click
     * @return [boolean]
     */
    onSaveMapping: function() {
        var me = this,
            submissionPnl = me.getFormsPanel(),
            formPnl = submissionPnl.formPanel,
            mappingFields = submissionPnl.mappingFields,
            form = formPnl.getForm(),
            record = formPnl.getRecord();

        if(!form.isValid()) {
            Shopware.Notification.createGrowlMessage('{s name=window_title}Ticket system{/s}', '{s name=error/forms_fill_all_fields}Please fill out all required fields (marked red) to save the form mapping.{/s}');
            return false;
        }

        form.updateRecord(record);
        record.save();

        var params = {};
        Ext.each(mappingFields, function(item) {
            params[item.getName()] = item.getValue();
        });
        params.formId = record.get('id');

        Ext.Ajax.request({
            url: '{url controller=Ticket action=saveMapping}',
            params: params,
            callback: function(records, operation) {
                me.subApplication.formsStore.load();
                if(!operation) {
                    Shopware.Notification.createGrowlMessage('{s name=window_title}Ticket system{/s}', '{s name=error/save_mapping}The mapping could not be saved successfully.{/s}');
                    return false;
                } else {
                    Shopware.Notification.createGrowlMessage('{s name=window_title}Ticket system{/s}', '{s name=success/save_mapping}The mapping was saved successfully.{/s}');
                }
            }
        });
    }
});
//{/block}
