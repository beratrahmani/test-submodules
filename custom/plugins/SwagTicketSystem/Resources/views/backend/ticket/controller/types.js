//{namespace name=backend/ticket/main}
//{block name="backend/ticket/controller/types"}
Ext.define('Shopware.apps.Ticket.controller.Types', {

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
        { ref: 'typesPanel', selector: 'ticket-settings-types' },
        { ref: 'typesWindow', selector: 'ticket-types-window' }
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

            /** Ticket types settings */
            'ticket-settings-types': {
                addType: me.onAddType,
                editType: me.onEditType,
                deleteType: me.onDeleteType,
                deleteTypes: me.onDeleteTypes,
                selectionChange: me.onTypeSelectionChange,
                searchType: me.onSearchType,
                edit: me.onCellEditType
            },
            'ticket-types-window': {
                saveType: me.onSaveType
            }
        });
    },

    /**
     * Event listener method which will be triggered when
     * the user presses the "create new type"-button.
     *
     * The method opens the "add type" window.
     *
     * @public
     * @event click
     * @param [object] btn - Ext.button.Button
     * @param [object] view - Shopware.apps.Ticket.view.settings.Types
     * @return void
     */
    onAddType: function () {
        var me = this;

        me.getView('settings.types.Window').create();
    },

    /**
     * Event listener method which will be triggered when the user
     * clicks on the "save types" button.
     *
     * The method updates / creates a record, updates the record
     * with the changed form values and saves the record.
     *
     * @public
     * @event click
     * @return void
     */
    onSaveType: function () {
        var me = this,
            win = me.getTypesWindow(),
            formPnl = win.formPanel,
            form = formPnl.getForm(),
            record = formPnl.getRecord();

        if (!form.isValid()) {
            Shopware.Notification.createGrowlMessage('{s name=window_title}Ticket system{/s}', '{s name=error/types_fill_all_fields}Please fill out all required fields (marked red) to save the ticket type.{/s}');
            return false;
        }

        if (!record) {
            record = Ext.create('Shopware.apps.Ticket.model.Types');
        }

        form.updateRecord(record);
        record.save({
            callback: function (self, operation) {
                if (operation.success) {
                    Shopware.Notification.createGrowlMessage('{s name=window_title}Ticket system{/s}', '{s name=success/save_type}The type was successfully saved.{/s}');
                    win.destroy();
                } else {
                    Shopware.Notification.createGrowlMessage('{s name=window_title}Ticket system{/s}', '{s name=error/save_Type}The type could not be saved successfully.{/s}');
                }
                me.subApplication.typesStore.load();
            }
        });
    },

    /**
     * Event listener method which will be triggered when the user
     * clicks on the "edit" action column in the types grid.
     *
     * The methods opens the edit window of the type.
     *
     * @public
     * @event click
     * @param [object] view - Shopware.apps.Ticket.view.settings.Types
     * @param [object] record - Shopware.apps.Ticket.model.Types
     * @return void
     */
    onEditType: function (view, record) {
        var me = this;

        me.getView('settings.types.Window').create({
            record: record
        });
    },

    /**
     * Event listener method which will be triggered when the user
     * clicks on the "delete" action column in the types grid.
     *
     * The method deletes the record which is associated with the
     * clicked action column.
     *
     * @public
     * @event click
     * @param [object] view - Shopware.apps.Ticket.view.settings.Types
     * @param [object] record - Shopware.apps.Ticket.model.Types
     */
    onDeleteType: function (view, record) {
        var me = this, store = me.subApplication.typesStore;
        record.destroy({
            success: function () {
                store.load()
            }
        });
    },

    /**
     * Event listener method which will be triggered when
     * the user presses the "delete marked"-button.
     *
     * The method deletes the selected types.
     *
     * @public
     * @event click
     * @param [object] btn - Ext.button.Button
     * @param [object] view - Shopware.apps.Ticket.view.settings.Types
     * @return void
     */
    onDeleteTypes: function (btn, view) {
        var selModel = view.selModel,
            store = view.store,
            selected = selModel.getSelection();

        Ext.each(selected, function (item, index) {
            if (index + 1 === selected.length) {
                item.destroy({
                    success: function () {
                        store.load();
                    }
                });
                return;
            }

            item.destroy();
        });
    },

    /**
     * Event listener method which will be trigged when
     * the user changes the selection.
     *
     * The method simply locks / unlocks the delete button
     * based on the selection of the user.
     *
     * @public
     * @event selectionchange
     * @paran [array] selection - Array of records which are included in the user selection
     * @return void
     */
    onTypeSelectionChange: function (selection) {
        var me = this,
            panel = me.getTypesPanel();

        panel.deleteButton.setDisabled(!selection.length);
    },

    /**
     * Event listener method which will be trigged when
     * the user changes the value of the search field.
     *
     * Filters associcated store.
     *
     * @public
     * @event change
     * @param [object] field - Ext.form.field.Text
     * @param [string] newValue - the changed value
     * @return void
     */
    onSearchType: function (field, newValue) {
        var me = this, store = me.subApplication.typesStore;

        store.filters.clear();
        store.filter({ property: 'free', value: newValue });
    },

    /**
     * Event listener method which will be fired when the
     * user edits a cell with the cell editor.
     *
     * The method saves the record.
     *
     * @param [object] editor - Ext.grid.plugin.CellEditing
     * @param [object] event - Ext.EventImplObj
     */
    onCellEditType: function (editor, event) {
        event.record.save();
    }
});
//{/block}
