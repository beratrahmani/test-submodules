//{namespace name=backend/plugins/b2b_debtor_plugin}
//{block name="backend/customer/view/detail/base"}
//{$smarty.block.parent}
/** global: Ext */
Ext.define('Shopware.apps.Customer.debtor.view.detail.Base', {
    override: 'Shopware.apps.Customer.view.detail.Base',

    createBaseFormLeft: function() {
        var me = this,
            elements = me.callParent(arguments);

        me.debtor = Ext.create('Ext.form.field.Checkbox', {
            name: 'b2b_is_debtor',
            boxLabel: '{s name=b2b_debtorBox}Mark the account as a debtor{/s}',
            inputValue: 1,
            uncheckedValue: 0,
            fieldLabel: '{s name=b2b_debtorField}Debtor{/s}',
            anchor:'95%',
            labelWidth:155,
            minWidth:250
        });

        elements.push(me.debtor);

        return elements;
    }

});
//{/block}