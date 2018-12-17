
//{block name="backend/customer/view/detail/window"}
//{$smarty.block.parent}
//{namespace name="backend/customer/view/main"}
Ext.define('Shopware.apps.Customer.view.detail.TicketSystemWindow', {

    override: 'Shopware.apps.Customer.view.detail.Window',

    /**
     * @Override
     * Creates the tab panel which displays the tickets of a customer.
     *
     * @return Ext.tab.Panel
     */
    getTabs: function() {
        var me = this, result;
        result = me.callParent(arguments);
        if ( me.record.get('id') ) {
            result.push(me.createTicketsTab());
        }
        me.subApplication.getController('TicketSystem');
        return result;
    },

    createTicketsTab:function () {

        var me = this,
            ticketsGridStore = Ext.create('Shopware.apps.Customer.store.ticket_system.List'),
            ticketEmployeeStore = Ext.create('Shopware.apps.Customer.store.ticket_system.Employee');
            ticketsGridStore.getProxy().extraParams = { customerID:me.record.data.id };

        me.ticketsGrid = Ext.create('Shopware.apps.Customer.view.ticket_system.List', {
            flex: 1,
            gridStore: ticketsGridStore.load(),
            employeeStore:ticketEmployeeStore.load()
        });

        return Ext.create('Ext.container.Container', {
            layout: {
                type: 'vbox',
                align : 'stretch'
            },
            defaults: { flex: 1 },
            title: '{s name=window/tab_customer_tickets_tab_title}Tickets{/s}',
            items: [ me.ticketsGrid ]
        });
    }

});
//{/block}