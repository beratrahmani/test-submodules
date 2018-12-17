//{block name="backend/swag_promotion/model/Voucher"}
Ext.define('Shopware.apps.SwagPromotion.model.Voucher', {

    extend: 'Shopware.data.Model',

    idProperty: 'id',

    fields: [
        //{block name="backend/swag_promotion/model/voucher/fields"}{/block}
        { name: 'id', type: 'int' },
        { name: 'description', type: 'string' }
    ]

});
//{/block}
