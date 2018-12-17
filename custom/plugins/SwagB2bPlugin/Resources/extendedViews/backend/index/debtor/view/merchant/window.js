//{namespace name=backend/index/view/widgets}
//{block name="backend/index/view/merchant/window"}
//{$smarty.block.parent}
/** global: Ext */
Ext.define('Shopware.apps.Index.debtor.view.merchant.Window', {
    override: 'Shopware.apps.Index.view.merchant.Window',

    createFormPanel: function (record) {
        var me = this,
            formPanel = me.callParent();
        
        if (me.mode === 'allow') {
            formPanel.add({
                xtype: 'checkbox',
                fieldLabel: '{s name=b2b_debtorField namespace=backend/plugins/b2b_debtor_plugin}Debtor{/s}',
                name: 'b2b_is_debtor',
                inputValue: 1,
                uncheckedValue: 0
            });
        }

        return formPanel;
    }
});
//{/block}