//

//{namespace name="backend/advisor/main"}
//{block name="backend/advisor/model/question"}
Ext.define('Shopware.apps.Advisor.model.Question', {
    extend: 'Shopware.data.Model',

    configure: function () {
        return {
            listing: 'Shopware.apps.Advisor.view.details.Questions',
            detail: 'Shopware.apps.Advisor.view.details.questions.Question'
        }
    },

    fields: [
        //{block name="backend/advisor/model/question/properties"}{/block}
        { name: 'id', type: 'int', useNull: true, defaultValue: null },
        { name: 'order', type: 'int', useNull: true, defaultValue: null },
        { name: 'type', type: 'string' },
        { name: 'exclude', type: 'boolean', defaultValue: false },
        { name: 'question', type: 'string' },
        { name: 'template', type: 'string' },
        { name: 'infoText', type: 'string' },
        { name: 'configuration', type: 'string' },
        { name: 'numberOfRows', type: 'int', defaultValue: 2 },
        { name: 'numberOfColumns', type: 'int', defaultValue: 2 },
        { name: 'columnHeight', type: 'int', defaultValue: 300 },
        { name: 'needsToBeAnswered', type: 'boolean' },
        { name: 'multipleAnswers', type: 'boolean' },
        { name: 'expandQuestion', type: 'boolean' },
        { name: 'boost', type: 'int', defaultValue: 1 },
        { name: 'hideText', type: 'boolean', defaultValue: false },
        // questionContent and layoutContent are fake property to inject
        // custom fields in the form
        { name: 'questionContent', type: 'int' },
        { name: 'layoutContent', type: 'int' },
        // Will contain the id of the question which this was cloned from
        { name: 'translationCloneId', type: 'int', defaultValue: null}
    ],

    associations: [
        {
            type: 'hasMany',
            model: 'Shopware.apps.Advisor.model.Answer',
            name: 'getAnswers',
            associationKey: 'answers'
        }
    ]
});
//{/block}