
// {block name="backend/article/model/abo_commerce/price"}
Ext.define('Shopware.apps.Article.model.abo_commerce.Price', {
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
        // {block name="backend/article/model/abo_commerce/price/fields"}{/block}
        { name: 'id', type: 'int', useNull: true },
        { name: 'durationFrom', type: 'int' },
        { name: 'discountAbsolute', type: 'float' },
        { name: 'discountPercent', type: 'float' },
        { name: 'customerGroupId', type: 'int' }
    ]
});
// {/block}
