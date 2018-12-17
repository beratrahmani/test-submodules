//{namespace name=backend/plugins/b2b_debtor_plugin}
//{block name="backend/order/view/detail/overview"}
//{$smarty.block.parent}
/** global: Ext */
Ext.define('Shopware.apps.Order.Reference.view.detail.Overview', {
    override: 'Shopware.apps.Order.view.detail.Overview',

    initComponent: function () {
        var me = this;
        me.callParent();

        Ext.Ajax.request({
            method: 'POST',
            url: '{url controller=B2bOrder action=fetchOrderContextBackendData}',
            params: {
                orderNumber: me.record.get('number')
            },
            success: function(responseData) {
                var response = Ext.JSON.decode(responseData.responseText),
                    orderReferenceField = me.editForm.getForm().findField('orderReference'),
                    requestedDeliveryField = me.editForm.getForm().findField('requestedDeliveryDate');

                if (!response.orderContext) {
                    orderReferenceField.hide();
                    requestedDeliveryField.hide();

                    return;
                }

                orderReferenceField.setValue(response.orderContext['orderReference']);
                requestedDeliveryField.setValue(response.orderContext['requestedDeliveryDate']);
            }
        });
    },

    createEditElements: function() {
        var me = this,
            elements = me.callParent(arguments);

        elements.push({
            xtype: 'textfield',
            name: 'orderReference',
            fieldLabel: '{s name=b2b_OrderReference}Order reference{/s}'
        }, {
            xtype: 'textfield',
            name: 'requestedDeliveryDate',
            fieldLabel: '{s name=b2b_RequestedDeliveryDate}Requested delivery date{/s}'
        });

        return elements;
    }
});
//{/block}