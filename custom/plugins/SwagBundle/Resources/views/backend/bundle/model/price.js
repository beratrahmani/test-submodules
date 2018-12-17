// {block name="backend/bundle/model/price"}
Ext.define('Shopware.apps.Bundle.model.Price', {
    /**
     * Extends the standard Ext Model
     * @string
     */
    extend: 'Ext.data.Model',

    /**
     * The fields used for this model
     * @array
     */
    fields: [
        { name: 'id', type: 'int' },
        { name: 'from', type: 'int' },
        { name: 'to', type: 'string' },
        { name: 'price', type: 'float' },
        { name: 'pseudoPrice', type: 'float' },
        { name: 'basePrice', type: 'float' },
        { name: 'percent', type: 'float' },
        { name: 'customerGroupId', type: 'int' },
        { name: 'customerGroupKey', type: 'string' }
    ],
    associations: [
        { type: 'hasMany', model: 'Shopware.apps.Base.model.CustomerGroup', name: 'getCustomerGroup', associationKey: 'customerGroup' }
    ]

});
// {/block}
