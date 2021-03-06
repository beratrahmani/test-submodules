//{block name="backend/customer/controller/detail"}
//{$smarty.block.parent}
/** global: Ext */
Ext.define('Shopware.apps.Customer.salesRepresentative.controller.Detail', {
    override: 'Shopware.apps.Customer.controller.Detail',

    onSaveCustomer: function (btn) {
        var me = this;

        Shopware.app.Application.on('customer-save-successfully', function (el, record) {
            /** global: Ext */
            Ext.Ajax.request({
                method: 'POST',
                url: '{url controller=AttributeData action=saveData}',
                params: {
                    _foreignKey: record.get('id'),
                    _table: 's_user_attributes',
                    __attribute_b2b_is_sales_representative: ~~el.getDetailWindow().baseFieldSet.salesRepresentative.getValue()
                }
            });
        });

        me.callParent(arguments);
    }
});
//{/block}
