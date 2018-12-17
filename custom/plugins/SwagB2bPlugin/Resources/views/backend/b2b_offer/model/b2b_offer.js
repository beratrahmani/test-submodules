Ext.define('Shopware.apps.b2bOffer.model.B2bOffer', {
    extend: 'Shopware.data.Model',

    configure: function () {
        return {
            controller: 'B2bOffer',
            detail: 'Shopware.apps.b2bOffer.view.detail.detail.B2bOffer'
        };
    },

    fields: [
        { name: 'id', type: 'int', useNull: true },
        { name: 'status', type: 'string' },
        { name: 'debtorEmail', type: 'string' },
        { name: 'debtorCompany', type: 'string' },
        { name: 'createdAt', type: 'date', dateFormat: 'd.m.Y H:i' },
        { name: 'acceptedByUserAt', type: 'date', dateFormat: 'd.m.Y H:i' },
        { name: 'changedByUserAt', type: 'date', dateFormat: 'd.m.Y H:i' },
        { name: 'changedByAdminAt', type: 'date', dateFormat: 'd.m.Y H:i' },
        { name: 'declinedByAdminAt', type: 'date', dateFormat: 'd.m.Y H:i' },
        { name: 'changedStatusAt', type: 'date', dateFormat: 'd.m.Y H:i' },
        { name: 'discountAmount', type: 'float' },
        { name: 'discountAmountNet', type: 'float' },
        { name: 'discountValueNet', type: 'float' },
        { name: 'listAmount', type: 'float' },
        { name: 'listAmountNet', type: 'float' },
        { name: 'listPositionCount', type: 'int' },
        { name: 'debtorNumber', type: 'int', defaultValue: 1},
        { name: 'expiredAt', type: 'date', dateFormat: 'd.m.Y H:i'},
        { name: 'expiredAtDate', type: 'date', dateFormat: 'Y-m-d', mapping: function (value) {
            return value.expiredAt;
        }},
        { name: 'expiredAtTime', type: 'date', dateFormat: 'H:i', mapping: function (value) {
            return value.expiredAt;
        }},
        { name: 'summaryStatus', type: 'string', mapping: function (value) {
            return value.status;
        }},
        { name: 'summaryDiscountAmount', type: 'float', mapping: function (value) {
            return value.discountAmount;
        }},
        { name: 'summaryDiscountAmountNet', type: 'float', mapping: function (value) {
            return value.discountAmountNet;
        }},
        { name: 'summaryListAmount', type: 'float', mapping: function (value) {
            return value.listAmount;
        }},
        { name: 'summaryListAmountNet', type: 'float', mapping: function (value) {
            return value.listAmountNet;
        }},
        { name: 'summaryListPositionCount', type: 'int', mapping: function (value) {
            return value.listPositionCount;
        }}
    ]
});