
//{namespace name=backend/ticket/main}
//{block name="backend/ticket/view/ticket/attachment"}
Ext.define('Shopware.apps.Ticket.view.ticket.Attachment', {
    
    extend: 'Enlight.app.Window',
    alias: 'widget.ticket-ticket-attachment',
    border: false,
    layout: {
        type: 'fit',
        align: 'stretch'
    },
    autoShow: true,
    autoScroll: true,
    maximizable: false,
    minimizable: false,
    title: '{s name=edit_window/attacment/title}Answer attachments{/s}',
    
    initComponent: function() {
        var me = this;

        me.items = [
            me.createGridContainer()
        ];

        me.callParent(arguments);
    },
    
    /**
     * Create grid panel for ticket support history attachments
     * 
     * @returns [object] Ext.grid.Panel
     */
    createGridContainer: function() {
        var me = this;
        
        return Ext.create('Ext.grid.Panel', {
            border: false,
            columns: me.getGridColumn(),
            store: me.attachmentStore,
            dockedItems: me.getPagingBar()
        });
    },
    
    /**
     * Creates the column model for the ticket support history attachment grid.
     *
     * @public
     * @return [array] generated columns
     */
    getGridColumn: function() {
        var me = this;
        
        return [{
                header: '{s name=edit_window/attachment/filename}Attachment{/s}',
                dataIndex: 'attachment',
                flex: 3
            }, {
                header: '{s name=edit_window/attachment/location}Upload location{/s}',
                dataIndex: 'location',
                flex: 2
            }, {
                xtype: 'actioncolumn',
                header: 'action',
                flex: 1,
                items: me.getActionColumn()
            }];
    },
    
    /**
     * Creates the items of the action column
     *
     * @return Array action column items
     */
    getActionColumn: function() {
        var me = this;
        
        return [{
            iconCls: 'sprite-drive-download',
            tooltip: '{s name=edit_window/attachment/action_column/download_attachments}Download attachments{/s}',
            handler: function (view, rowIndex, colIndex, item, event, record) {
                var url = '{url action=downloadAttachment}?hash=' + record.get('hash');
                window.open(url, '_blank');
            } 
        }, {
            iconCls: 'sprite-minus-circle-frame',
            tooltip: '{s name=edit_window/attachment/action_column/delete_attachments}Delete attachments{/s}',
            handler: function (view, rowIndex, colIndex, item, event, record) {
                Ext.MessageBox.confirm('{s name=window_title}Ticket system{/s}', '{s name=edit_window/attachment/delete_confirm}Are you sure to delete the selected attachment in the list?{/s}', function(button) {
                    if(button != 'yes') {
                        return false;
                    }
                    me.fireEvent('deleteAttachment', view, record);
                });
            }
        }];
    },
    
    /**
     * Returns the paging toolbar which
     * is located under the grid.
     *
     * @public
     * @return [object] Ext.toolbar.Paging
     */
    getPagingBar: function () {
        var me = this;

        return Ext.create('Ext.toolbar.Paging', {
            store: me.attachmentStore,
            dock: 'bottom',
            displayInfo: true
        });
    }
    
});
//{/block}