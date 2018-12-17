Ext.define('Shopware.apps.b2bOffer.model.Positions', {
    extend: 'Shopware.data.Model',
    fields: [
        { name: 'id', type: 'int' },
        { name: 'referenceNumber', type: 'string' },
        { name: 'name', type: 'string'},
        { name: 'quantity', type: 'int', defaultValue: 1 },
        { name: 'minPurchase', type: 'int', defaultValue: 1 },
        { name: 'maxPurchase', type: 'int', defaultValue: null },
        { name: 'purchaseStep', type: 'int', defaultValue: 1 },
        { name: 'amountNet', type: 'float' },
        { name: 'amount', type: 'float' },
        { name: 'discountAmountNet', type: 'float' },
        { name: 'discountAmount', type: 'float' },
    ],

    configure: function() {
      return {
        controller: 'B2bOffer'
      };
    },
});
