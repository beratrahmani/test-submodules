//

//{namespace name="backend/advisor/main"}
//{block name="backend/advisor/model/advisor"}
Ext.define('Shopware.apps.Advisor.model.Advisor', {
    alias: 'widget.advisor-model',
    extend: 'Shopware.data.Model',

    configure: function () {
        return {
            controller: 'Advisor',
            detail: 'Shopware.apps.Advisor.view.details.Advisor'
        };
    },

    fields: [
        //{block name="backend/advisor/model/advisor/properties"}{/block}
        { name: 'id', type: 'int', useNull: true, defaultValue: null },
        { name: 'teaserBannerId', type: 'int' },
        { name: 'streamId', type: 'int', useNull: true, defaultValue: null },
        { name: 'active', type: 'boolean' },
        { name: 'name', type: 'string' },
        { name: 'description', type: 'string' },
        { name: 'infoLinkText', type: 'string' },
        { name: 'buttonText', type: 'string' },
        { name: 'remainingPostsTitle', type: 'string' },
        { name: 'listingTitleFiltered', type: 'string' },
        { name: 'highlightTopHit', type: 'boolean' },
        { name: 'listingLayout', type: 'string' },
        { name: 'links', type: 'string' },
        { name: 'mode', type: 'string' },
        { name: 'lastListingSort', type: 'string' },
        { name: 'topHitTitle', type: 'string' },
        { name: 'minMatchingAttributes', type: 'int' }
    ],

    associations: [
        //{block name="backend/advisor/model/advisor/associations"}{/block}
        {
            type: 'hasMany',
            model: 'Shopware.apps.Advisor.model.Stream',
            name: 'getStream',
            associationKey: 'stream',
            field: 'streamId',

            relation: 'ManyToOne'
        }, {
            type: 'hasMany',
            model: 'Shopware.apps.Base.model.Media',
            name: 'getTeaserBanner',
            associationKey: 'teaserBanner',
            field: 'teaserBannerId',

            relation: 'ManyToOne'
        },
        {
            type: 'hasMany',
            model: 'Shopware.apps.Advisor.model.Question',
            name: 'getQuestions',
            associationKey: 'questions',
            relation: 'OneToMany'
        }
    ]
});
//{/block}