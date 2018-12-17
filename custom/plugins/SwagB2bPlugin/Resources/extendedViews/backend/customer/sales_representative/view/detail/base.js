//{namespace name=backend/plugins/b2b_debtor_plugin}
//{block name="backend/customer/view/detail/base"}
//{$smarty.block.parent}
/** global: Ext */
Ext.define('Shopware.apps.Customer.salesRepresentative.view.detail.Base', {
    override: 'Shopware.apps.Customer.view.detail.Base',

    createBaseFormLeft: function() {
        var me = this,
            elements = me.callParent(arguments);

        me.salesRepresentative = Ext.create('Ext.form.field.Checkbox', {
            name: 'b2b_is_sales_representative',
            boxLabel: '{s name=b2b_salesRepresentativeBox}Mark the account as a sales Representative{/s}',
            inputValue: 1,
            uncheckedValue: 0,
            fieldLabel: '{s name=b2b_salesRepresentativeField}Sales representative{/s}',
            anchor:'95%',
            labelWidth:155,
            minWidth:250
        });

        elements.push(me.salesRepresentative);

        return elements;
    }

});
//{/block}