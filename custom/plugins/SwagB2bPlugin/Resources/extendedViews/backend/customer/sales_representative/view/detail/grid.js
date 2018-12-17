//{namespace name=backend/plugins/b2b_debtor_plugin}
//{block name="backend/customer/sales_representative/view/detail/grid"}

Ext.define('Shopware.apps.Customer.salesRepresentative.view.detail.Grid', {
    extend: 'Ext.grid.Panel',
    title: '{s name=b2b_SalesRepresentative_tab}Sales representative clients{/s}',
    stateful: true,
    collapsible: true,
    multiSelect: true,
    stateId: 'stateGrid',
    columns: [
        {
            header: '{s name=Name}Name{/s}',
            flex: 1,
            dataIndex: 'name'
        },
        {
            header: '{s name=Email}Email{/s}',
            flex: 1,
            dataIndex: 'email'
        }
    ],
    listeners: {
        afterrender: function () {
            var me = this;
            me.getStore().load({
                callback: function () {
                    var sm = me.getSelectionModel(),
                        activeRecords = [];
                    me.getStore().each(function(record){
                        if (record.data.client === true) {
                            activeRecords.push(record);
                        }
                    });
                    sm.select(activeRecords, false, false);
                }
            });
        }
    }
});
//{/block}