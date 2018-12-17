//

// {namespace name="backend/live_shopping/view/main"}
// {block name="backend/swag_live_shopping/model/template"}
Ext.define('Shopware.apps.SwagLiveShopping.model.LiveShopping', {
    extend: 'Shopware.data.Model',

    configure: function () {
        return {
            controller: 'SwagLiveShopping'
        };
    },

    fields: [
        // {block name="backend/swag_live_shopping/model/live_shopping/fields"}{/block}
        { name: 'id', type: 'int', useNull: true },
        { name: 'articleId', type: 'int', useNull: false },
        { name: 'type', type: 'int', useNull: false },
        { name: 'typeName', type: 'string', useNull: true },
        { name: 'name', type: 'string', useNull: false },
        { name: 'articleName', type: 'string', useNull: true },
        { name: 'active', type: 'boolean', useNull: true },
        { name: 'number', type: 'string', useNull: false },
        { name: 'max_quantity_enable', type: 'boolean', useNull: true },
        { name: 'max_quantity', type: 'int', useNull: true },
        { name: 'max_purchase', type: 'int', useNull: true },
        { name: 'validFrom', type: 'date', useNull: true },
        { name: 'validTo', type: 'date', useNull: true },
        { name: 'created', type: 'date', useNull: true },
        { name: 'sells', type: 'int', useNull: true }
    ],

    associations: [
        {
            type: 'hasMany',
            model: 'Shopware.apps.SwagLiveShopping.model.Price',
            associationKey: 'prices',
            name: 'getPrices'
        }
    ]
});
// {/block}
