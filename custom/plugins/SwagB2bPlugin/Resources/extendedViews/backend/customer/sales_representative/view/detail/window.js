//{block name="backend/customer/view/detail/window"}
//{$smarty.block.parent}
/** global: Ext */
Ext.define('Shopware.apps.Customer.salesRepresentative.view.detail.Window', {
    override: 'Shopware.apps.Customer.view.detail.Window',

    setStores: function() {
        var me = this,
            elements = me.callParent(arguments);

        if(!me.record.get('id')) {
            return elements;
        }

        return Ext.Ajax.request({
            url: '{url controller=AttributeData action=loadData}',
            params: {
                _foreignKey: me.record.get('id'),
                _table: 's_user_attributes'
            },
            success: function(responseData) {
                var response = Ext.JSON.decode(responseData.responseText);

                me.baseFieldSet.salesRepresentative.setValue(response.data['__attribute_b2b_is_sales_representative']);

                return elements;
            }
        });
    },

    getTabs: function() {
        var me = this,
            tabs = me.callParent(arguments);
        
        if(!me.record.get('id')) {
            return tabs;
        }
        
        var ajaxResponse = Ext.Ajax.request({
            url: '{url controller=AttributeData action=loadData}',
            params: {
                _foreignKey: me.record.get('id'),
                _table: 's_user_attributes'
            },
            async: false
        });

        var response = Ext.JSON.decode(ajaxResponse.responseText);

        var salesRepresentative = Number(response.data['__attribute_b2b_is_sales_representative']);

        if (salesRepresentative === 0 || isNaN(salesRepresentative)) {
            return tabs;
        }

        tabs.push(me.createSalesRepresentativeTab());

        return tabs;
    },

    createSalesRepresentativeTab: function() {
        var me = this,
            selModel = Ext.create('Ext.selection.CheckboxModel');

        me.salesRepresentatitveStore = Ext.create('Shopware.apps.Customer.salesRepresentative.store.Base');
        me.salesRepresentatitveStore.getProxy().extraParams['sales_representative_id'] = me.record.get('id');

        me.salesRepresentatitveTab = Ext.create('Shopware.apps.Customer.salesRepresentative.view.detail.Grid', {
            store: me.salesRepresentatitveStore,
            selModel: selModel,
            dockedItems: [{
                xtype: 'toolbar',
                dock: 'bottom',
                cls: 'shopware-toolbar x-toolbar-shopware-ui',
                ui: 'footer',
                layout: {
                    pack: 'center'
                },
                items: [{
                    xtype: 'button',
                    minWidth: 80,
                    text: '{s namespace=backend/plugins/b2b_debtor_plugin name=Save}Save{/s}',
                    cls: 'primary',
                    handler: function () {
                        me.saveData(me.salesRepresentatitveTab.getSelectionModel(), me.record.get('id'));
                    }
                }]
            }]
        });

        return me.salesRepresentatitveTab;
    },

    saveData: function (model, id) {
        var clients = [],
            selection = model.getSelection();

        for(var i = 0; i < selection.length; i++) {
            clients.push(selection[i].internalId);
        }

        Ext.Ajax.request({
            url: '{url module=backend controller=b2bsalesrepresentative action=saveClients}',
            params: {
                clients: Ext.JSON.encode(clients),
                sales_representative_id: id
            },
            success: function() {
                Shopware.Notification.createGrowlMessage(
                    '',
                    '{s name=saveClientsSuccessMessage namespace=backend/plugins/b2b_debtor_plugin}Clients successfully saved{/s}'
                )
            }
        });
    }
});
//{/block}