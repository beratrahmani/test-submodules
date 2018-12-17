
//{namespace name=backend/ticket/main}
Ext.define('Shopware.apps.Ticket.view.view.widget.List', {

    /**
     * Extend the base widget view
     */
    extend: 'Shopware.apps.Index.view.widgets.Base',

    /**
     * Set alias so the widget can be identified per widget name
     */
    alias: 'widget.swag-ticket-system',

    /**
     * Set the south handle so the widget height can be resized.
     */
    resizable: {
        //handles: 's e se'
        handles: 's'
    },

    /**
     * Minimum / Default height of the widget
     */
    minHeight: 300,

    /**
     * current userID
     */
    employeeOd: 0,

    /**
     * Initializes the widget.
     * Creates the Grid for showing the latest tickets.
     * Adds a refresh button to the header to manually refresh the grid.
     * #
     *
     * @public
     * @return void
     */
    initComponent: function () {
        var me = this;

        me.employeeId = '{$ticket_backendUserId}';

        me.gridStore = Ext.create('Shopware.apps.Ticket.store.WidgetList');
        me.statusStore = Ext.create('Shopware.apps.Ticket.store.Status');
        me.employeeStore = Ext.create('Shopware.apps.Ticket.store.Employee').load(function() {
            me.statusStore.load(function() {
                me.gridStore.load();
            });
        });

        me.items = [
            me.createGrid()
        ];

        me.tools = [{
            type: 'refresh',
            scope: me,
            handler: me.refreshView
        }];

        me.callParent(arguments);
    },

    /**
     * Refresh the account store if its available
     */
    refreshView: function() {
        var me = this;

        if(!me.gridStore) {
            return;
        }

        me.gridStore.reload();
    },

    createGrid: function () {
        var me = this;

        return Ext.create('Ext.grid.Panel', {
            border: 0,
            columns: me.createColumns(),
            store: me.gridStore,
            test: 'test'
        });
    },

    /**
     * Creates the column model for the component.
     *
     * @public
     * @return Array computed columns
     */
    createColumns: function() {
        var me = this;

        return [{
            dataIndex: 'id',
            width: 12,
            header: '#',
            renderer: me.gridColorRenderer,
            scope: me
        }, {
            xtype: 'datecolumn',
            dataIndex: 'receipt',
            header: '{s name=overview/columns/receipt}Created{/s}',
            format: 'd.m.Y H:i:s',
            renderer: me.dateRenderer,
            width: 112,
            scope: me
        }, {
            dataIndex: 'subject',
            header: '{s name=settings/submission/form/subject}Subject{/s}',
            renderer: me.defaultColumnRenderer,
            width: 112,
            scope: me
        }, {
            dataIndex: 'statusId',
            header: '{s name=overview/columns/status}Status{/s}',
            flex: 1,
            renderer: me.stateRenderer,
            scope: me
        }, {
            dataIndex: 'employeeId',
            header: '{s name=overview/columns/employee}Employee{/s}',
            flex: 1,
            renderer: me.employeeRenderer,
            scope: me
        }, {
            xtype: 'actioncolumn',
            header: '{s name=overview/columns/actions}Action(s){/s}',
            flex:1,
            items: me.getActionColumnItems(),
            scope: me
        }];
    },

    /**
     * Creates the items of the action column
     *
     * @return Array action column items
     */
    getActionColumnItems: function() {
        var me = this,
            actionColumnData = [];

        actionColumnData.push({
            iconCls: 'sprite-pencil',
            tooltip: '{s name=overview/columns/edit_tip}Edit ticket{/s}',
            handler: function(view, rowIdx, colIdx, item, e, record) {
                me.onEditTicket(view, record, record.get('albumId'), me.employeeStore, me.userID, rowIdx, colIdx, item, e);
            }
        });

        return actionColumnData;
    },

    dateRenderer: function(value, meta, record){
        var isRead = record.get('isRead');
        var date = Ext.Date.format(value, 'd.m.Y H:i:s');
        return isRead === 0 ? '<strong>' + date + '</strong>' : date;
    },

    subReadRenderer:function(value, record){
        var isRead = record.get('isRead');
        return isRead === 0 ? '<strong>' + value + '</strong>' : value;
    },

    defaultColumnRenderer: function(value, meta, record) {
        var me = this;
        return me.subReadRenderer(value, record);
    },

    /**
     * Renderer which requests the employee name for the employee
     * store to render the full name of the employee.
     *
     * @public
     * @param [string] value - Value of the column
     * @param [object] meta - Meta object of the table row
     * @param [object] record - Shopware.apps.Ticket.model.List
     * @return [string] employee name
     */
    employeeRenderer: function(value, meta, record) {
        var me = this;
        var store = me.employeeStore,
            employee = store.getById(~~(1 * value));
        if(!employee) {
            return me.subReadRenderer(value, record);
        }
        return me.subReadRenderer(employee.get('name'), record);
    },

    stateRenderer: function(value, meta, record) {
        var me = this;
        var sstore = me.statusStore,
            status = sstore.getById(~~(1 * value));
        if(!status) {
            return me.subReadRenderer(value, record);
        }
        return me.subReadRenderer(status.get('description'), record);
    },

    /**
     * Renders a div box if the specific grid color.
     *
     * @param [string] value - Value of the column
     * @param [object] meta - Meta object of the table row
     * @param [object] record - Shopware.apps.Ticket.model.List
     * @return [string] formatted color + the id of the ticket
     */
    gridColorRenderer: function(value, meta, record) {
        var me = this;
        var color = record.get('ticketTypeColor');
        if(!color) {
            return me.subReadRenderer('#' + value, record);
        }

        return '<div style="width:14px;height:14px;background-color: '+ color +';display:inline-block;vertical-align: middle;"></div>' + me.subReadRenderer(value, record);
    },

    onSelectionChange: function(selection) {
        var me = this,
            grid = me.getOverviewGrid(),
            info = me.getTicketInfo(),
            btn = grid.deleteButton;

        btn.setDisabled(!selection.length);

        if(!selection.length) {
            return false;
        }
        var record = selection[0];
        info.dataView.update(record.data);
    },

    onEditTicket: function(view, record, albumId, employeeStore, userId) {
        var me = this;
        var isRead = record.get('isRead');
        var id = record.get('id');
        if(isRead === 0) {
            me.updateIsRead(id);
        }

        Shopware.app.Application.addSubApplication({
            name: 'Shopware.apps.Ticket',
            action: 'detail',
            params: {
                ticketId: id,
                customerRecord: record,
                employeeId: me.employeeId
            }
        });
    },

    updateIsRead: function(id)
    {
        var me = this;
        var store = me.gridStore;
        Ext.Ajax.request({
            url: '{url controller="Ticket" action="updateIsRead"}',
            params: { 'id':id },
            success: function() {
                store.load();
            }
        })
    }
});