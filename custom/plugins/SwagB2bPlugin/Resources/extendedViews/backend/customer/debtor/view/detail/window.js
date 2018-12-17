//{block name="backend/customer/view/detail/window"}
//{$smarty.block.parent}
/** global: Ext */
Ext.define('Shopware.apps.Customer.debtor.view.detail.Window', {
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
                
                me.baseFieldSet.debtor.setValue(response.data['__attribute_b2b_is_debtor']);

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

        var debtor = Number(response.data['__attribute_b2b_is_debtor']);

        if (debtor === 0 || isNaN(debtor)) {
            return tabs;
        }

        tabs.push(me.createDebtorTab());

        return tabs;
    },

    createDebtorTab: function() {
        var me = this;

        me.debtorEmotionStore = Ext.create('Shopware.apps.Customer.debtor.store.Base');
        me.debtorEmotionStore.getProxy().extraParams['debtor_id'] = me.record.get('id');

        me.debtorEmotionTab = Ext.create('Shopware.apps.Customer.debtor.view.detail.Grid', {
            store: me.debtorEmotionStore
        });

        me.debtorEmotionTab.getStore().load();

        me.debtorEmotionTab.on('edit', function(editor, e) {
            Ext.Ajax.request({
                url: '{url module=backend controller=b2bdebtor action=updateUser}',
                method: 'POST',
                params: e.record.data
            });
        });

        return me.debtorEmotionTab;
    }
});
//{/block}