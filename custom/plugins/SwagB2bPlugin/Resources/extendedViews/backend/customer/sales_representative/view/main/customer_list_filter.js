// {namespace name=backend/customer/view/main}
// {block name="backend/customer/view/main/customer_list_filter"}
// {$smarty.block.parent}
/** global: Ext */
Ext.define('Shopware.apps.Customer.debtor.view.main.CustomerListFilter', {
    override: 'Shopware.apps.Customer.view.main.CustomerListFilter',

    configure: function () {
        var me = this,
            elements = me.callParent(arguments);

        elements['fields']['b2bIsSalesRepresentative'] = {
            fieldLabel: '{s namespace=backend/plugins/b2b_debtor_plugin name=b2bIsSalesRepresentativeFilter}Sales representative{/s}'
        };

        return elements;
    }
});
// {/block}