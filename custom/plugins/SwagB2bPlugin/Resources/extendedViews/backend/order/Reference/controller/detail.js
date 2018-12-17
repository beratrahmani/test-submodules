//{block name="backend/order/controller/detail"}
//{$smarty.block.parent}
/** global: Ext */
Ext.define('Shopware.apps.Order.Reference.controller.Detail', {
    override: 'Shopware.apps.Order.controller.Detail',

    onSaveOverview: function (record) {
        var me = this,
            form = me.getDetailWindow().items.items[0].items.items[0].getForm(),
            orderReferenceField = form.findField('orderReference'),
            requestDeliveryDateField = form.findField('requestedDeliveryDate'),
            params = {};

        if (orderReferenceField.hidden && requestDeliveryDateField.hidden) {
            me.callParent(arguments);
            return;
        }

        if (orderReferenceField.getValue().trim().length !== 0) {
            params['orderReference'] = orderReferenceField.getValue().trim();
        }

        if (requestDeliveryDateField.getValue().trim().length !== 0) {
            params['requestedDeliveryDate'] = requestDeliveryDateField.getValue().trim();
        }

        params['orderNumber'] = record.data.number;
        
        Ext.Ajax.request({
            method: 'POST',
            url: '{url controller=B2bOrder action=saveOrderContextBackendData}',
            params: params
        });

        me.callParent(arguments);
    }
});
//{/block}