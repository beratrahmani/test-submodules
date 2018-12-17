//

// {namespace name="backend/live_shopping/view/main"}
// {block name="backend/swag_live_shopping/model/price"}
Ext.define('Shopware.apps.SwagLiveShopping.model.Price', {
    extend: 'Ext.data.Model',

    fields: [
        // {block name="backend/swag_live_shopping/model/price/fields"}{/block}
        { name: 'id', type: 'int', useNull: true },
        { name: 'live_shopping_id', type: 'int', useNull: true },
        { name: 'customer_group_id', type: 'int', useNull: false },
        { name: 'customerGroupName', type: 'string', useNull: true },
        { name: 'price', type: 'float', useNull: false },
        { name: 'endprice', type: 'float', useNull: true }
    ]
});
// {/block}
