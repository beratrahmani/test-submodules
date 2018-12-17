
//{namespace name="backend/customer/view/main"}
//{block name="backend/ticket/controller/list"}
Ext.define('Shopware.apps.Customer.controller.TicketSystem', {

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
        { ref: 'ticketsGrid', selector: 'customer-ticket_system-list' }
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
            'customer-ticket_system-list textfield[name=searchfield]':{
                change:me.onSearchTicket
            },
            'customer-ticket_system-list':{
                'openTicket': me.onOpenTicket,
                'deleteTicket': me.onDeleteTicket,
                'searchTicket':me.onSearchTicket
            }
        });
    },

    /**
     * Event listener method which will be triggered when
     * the user clicks the edit icon in the grid.
     *
     * Opens the customer module with the selected customer.
     *
     * @param [object] view - Shopware.apps.Ticket.view.list.Overview
     * @param [object] record - Shopware.apps.Ticket.model.List
     * @return void
     */
    onOpenTicket: function(view, record) {
        var id = record.get('id');

        Shopware.app.Application.addSubApplication({
            name: 'Shopware.apps.Ticket',
            action: 'detail',
            params: {
               ticketId: id,
               customerRecord:record
            }
        });
    },

    /**
     * Event listener method which is fired when the user
     * insert a search string into the text field which is placed
     * on top of the tickets grid.
     * @param [object] field - Ext.field.Text which is displayed on the top of the tickets grid
     * @return boolean
     */
    onSearchTicket:function (field, newValue) {
        var me = this,
            grid = me.getTicketsGrid(),
            store = grid.getStore();
        store.filters.clear();
        store.filter({ property: 'free', value: newValue });
    },
    /**
     * Event listener method which will be triggered when the user
     * clicks on the "delete" action column in the tickets grid.
     *
     * The method deletes the record which is associated with the
     * clicked action column.
     *
     * @public
     * @event click
     * @param [object] view - Shopware.apps.Ticket.view.list.Overview
     * @param [object] record - Shopware.apps.Ticket.model.List
     */
    onDeleteTicket: function(view, record) {
        var me = this,
            grid = me.getTicketsGrid(),
            store = grid.getStore();

        record.destroy({
            success: function() {
                Shopware.Notification.createGrowlMessage('{s name=delete_growl_window_title}Ticket system{/s}', '{s name=growl/success/delete_ticket_message}Die Einträge werden gelöscht.{/s}');
                store.reload();
            }
        });
    }

});
//{/block}
