
//{block name="backend/ticket_system/view/ticket_system/list"}
//{namespace name="backend/customer/view/main"}
Ext.define('Shopware.apps.Customer.view.ticket_system.List', {

    /**
     * The listing component is an extension of the Ext.grid.Panel.
     */
    extend: 'Ext.grid.Panel',

    /**
     * The initComponent template method is an important initialization step for a Component.
     * It is intended to be implemented by each subclass of Ext.Component to provide any needed constructor logic.
     * The initComponent method of the class being created is called first,
     * with each initComponent method up the hierarchy to Ext.Component being called thereafter.
     * This makes it easy to implement and, if needed, override the constructor logic of the Component at any step in the hierarchy.
     * The initComponent method must contain a call to callParent in order to ensure that the parent class' initComponent method is also called.
     *
     * @return void
     */

    /**
     * The view needs to be scrollable
     * @string
     */
    autoScroll:true,
    /**
     * Set css class for this component
     * @string
     */
    cls: Ext.baseCSSPrefix + 'order-list',


    alias:'widget.customer-ticket_system-list',

    /**
     * Contains all snippets for the view component
     * @object
     */
    snippets:{
        search:'{s name=toolbar/search_empty_text}Search...{/s}'
    },

    /**
     * Initialize the Shopware.apps.Customer.view.ticket_system.List and defines the necessary
     * default configuration
     * @return void
     */
    initComponent:function () {
        var me = this;
        me.store = me.gridStore;
        me.columns = me.getColumns();
        me.toolbar = me.getToolbar();
        me.pagingbar = me.getPagingBar();
        me.dockedItems = [ me.toolbar, me.pagingbar ];
        me.callParent(arguments);
    },

    /**
     * Registers the events for the grid
     * @return void
     */
    registerEvents:function () {
        this.addEvents(
            /**
             * Event will be fired when the user clicks the grid action
             * column to answer the ticket
             *
             * @event generatePassword
             * @param [object] record - Associated store record
             */
            'openTicket',

            /**
             * Event will be fired when the user insert a search string into the text field
             *
             * @event generatePassword
             * @param [string] value - inserted value
             * @param [object] grid - grid store
             */
            'searchTicket'

        );
    },

    /**
     * Creates the paging toolbar for the tickets grid to allow
     * and store paging. The paging toolbar uses the same store as the Grid
     *
     * @return [Ext.toolbar.Paging] - The paging toolbar for the tickets grid
     */
    getPagingBar:function () {
        var me = this;

        return Ext.create('Ext.toolbar.Paging', {
            store:me.gridStore,
            dock:'bottom',
            displayInfo:true
        });
    },

    /**
     * Creates the grid columns
     *
     * @return [array] grid columns
     */
    getColumns:function () {
        var me = this;

        return [{
            dataIndex: 'id',
            header: '#',
            width: 60,
            renderer: me.gridColorRenderer
        }, {
            xtype: 'datecolumn',
            dataIndex: 'receipt',
            header: '{s name=list/columns/receipt}Erstellt am{/s}',
            flex: 1
        }, {
            xtype: 'datecolumn',
            dataIndex: 'lastContact',
            header: '{s name=list/columns/last_contact}Letzter Kontakt{/s}',
            flex: 1
        }, {
            dataIndex: 'ticketTypeName',
            header: '{s name=list/columns/ticket_type}Typ{/s}',
            flex: 1
        }, {
            dataIndex: 'isoCode',
            header: '{s name=list/columns/iso}ISO{/s}',
            width: 50
        }, {
            dataIndex: 'statusId',
            header: '{s name=list/columns/status}Status{/s}',
            flex: 1,
            renderer: function(value, metaData, record) {
                        return record.get("status");
             }

        }, {
            dataIndex: 'contact',
            header: '{s name=list/columns/customer}Kunde{/s}',
            flex: 1,
            renderer: me.emailRenderer
        }, {
            dataIndex: 'company',
            header: '{s name=list/columns/company}Firma{/s}',
            flex: 1
        }, {
            dataIndex: 'employeeId',
            header: '{s name=list/columns/employee}Mitarbeiter{/s}',
            flex: 1,
            renderer: me.employeeRenderer,
            editor: {
                xtype: 'combobox',
                store: me.employeeStore,
                valueField: 'id',
                displayField: 'name'
            }
        }, {
            xtype: 'actioncolumn',
            header: '{s name=list/columns/actions}Aktion(en){/s}',
            width: 80,
            items: me.getActionColumnItems()
        }];
    },

    /**
     * Creates the grid toolbar with search button
     *
     * @return [Ext.toolbar.Toolbar] grid toolbar
     */
    getToolbar:function () {
        var me = this;
        return Ext.create('Ext.toolbar.Toolbar', {
            dock:'top',
            ui: 'shopware-ui',

            items:[
                '->', {
                    xtype: 'textfield',
                    name: 'search',
                    cls: 'searchfield',
                    emptyText: '{s name=toolbar/search/empty_text}Suchen...{/s}',
                    listeners: {
                        scope: me,
                        buffer: 500,
                        change: function(field, newValue, oldValue) {
                            me.fireEvent('searchTicket', field, newValue, oldValue, me);
                        }
                    }
                },
                { xtype:'tbspacer', width:6 }
            ]
        });
    },

    getActionColumnItems: function() {
        var me = this,
            actionColumnData = [];

        /*{if {acl_is_allowed privilege=update}}*/
        actionColumnData.push({
            iconCls: 'sprite-pencil',
            tooltip: '{s name=overview/columns/edit_tip}Ticket editieren{/s}',
            handler: function(view, rowIdx, colIdx, item, e, record) {
                me.fireEvent('openTicket', view, record, rowIdx, colIdx, item, e);
            }
        });
        /*{/if}*/

        /*{if {acl_is_allowed privilege=delete}}*/
        actionColumnData.push({
            iconCls: 'sprite-minus-circle',
            tooltip: '{s name=overview/columns/delete_tip}Ticket löschen{/s}',
            handler: function(view, rowIdx, colIdx, item, e, record) {
                Ext.MessageBox.confirm('{s name=window_title}Ticket system{/s}', '{s name=overview/button/delete_confirm}Are you sure to delete the selected ticket(s) in the list?{/s}', function(button) {
                    if(button != 'yes') {
                        return false;
                    }
                    me.fireEvent('deleteTicket', view, record, rowIdx, colIdx, item, e);
                });
            }
        });
        /*{/if}*/
        return actionColumnData;
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
            return value;
        }
        return employee.get('name');
    }

});
//{/block}