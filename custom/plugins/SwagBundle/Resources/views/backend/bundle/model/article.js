// {block name="backend/bundle/model/article"}
Ext.define('Shopware.apps.Bundle.model.Article', {
    /**
     * Extends the standard Ext Model
     * @string
     */
    extend: 'Ext.data.Model',

    groupField: 'groupId',

    /**
     * The fields used for this model
     * @array
     */
    fields: [
        { name: 'id', type: 'int', useNull: true },
        { name: 'groupId', type: 'int', useNull: true },
        { name: 'articleDetailId', type: 'string' },
        { name: 'bundleId', type: 'int', useNull: true },
        { name: 'quantity', type: 'int' },
        { name: 'configurable', type: 'boolean' }
    ],

    associations: [
        { type: 'hasMany', model: 'Shopware.apps.Bundle.model.Detail', name: 'getDetail', associationKey: 'articleDetail' },
        { type: 'hasMany', model: 'Shopware.apps.Bundle.model.Price', name: 'getPrices', associationKey: 'prices' }
    ]
});
// {/block}
