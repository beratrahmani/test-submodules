// {block name="backend/article/model/bundle/search"}
Ext.define('Shopware.apps.Article.model.bundle.Search', {
    /**
     * Extends the standard Ext Model
     * @string
     */
    extend: 'Shopware.apps.Article.model.Detail',

    fields: [
        {
            name: 'name',
            type: 'string',
            convert: function(value, record) {
                return record.raw.article.name;
            }
        }
    ]
});
// {/block}
