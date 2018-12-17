//

//{namespace name="backend/advisor/main"}
//{block name="backend/advisor/view/details/questions/property"}
Ext.define('Shopware.apps.Advisor.view.details.questions.Property', {
    extend: 'Shopware.apps.Advisor.view.details.questions.AbstractQuestion',

    label: '{s name="filter_propertyLabel"}Property{/s}',

    /**
     * @overwrite
     *
     * @returns { string }
     */
    getKey: function () {
        return 'property'
    },

    /**
     * @overwrite
     *
     * @returns { string }
     */
    getLabel: function () {
        return this.label;
    },

    /**
     * @overwrite
     *
     * @param { Shopware.apps.Advisor.model.Advisor } advisor
     * @param { Shopware.apps.Advisor.model.Question } question
     *
     * @returns { *[] }
     */
    createQuestion: function (advisor, question) {
        var me = this;

        question.set('type', me.getKey());

        me.answerGrid = Ext.create('Shopware.apps.Advisor.view.details.ui.AnswerGrid', {
            advisor: advisor,
            question: question,
            refreshGridData: function (advisor, question, store) {
                me.answerGrid.__proto__.refreshGridData.apply(me.answerGrid, arguments);
                me.refreshProperties(advisor, question);
            }
        });

        me.propertySelection = Ext.create('Shopware.apps.Advisor.view.details.ui.PropertySelection', {
            advisor: advisor,
            question: question,
            answerGrid: me.answerGrid
        });

        me.answerGrid.refreshGridData(advisor, question, question.getAnswers());

        return [
            me.propertySelection,
            me.answerGrid
        ]
    },

    /**
     * @param layout
     */
    updateQuestionViewData: function (layout) {
        var me = this;

        me.answerGrid.reconfigureGrid(layout);
    },

    /**
     * @param { Shopware.apps.Advisor.model.Advisor } advisor
     * @param { Shopware.apps.Advisor.model.Question } question
     */
    refreshProperties: function (advisor, question) {
        var me = this,
            store;

        if (!question.get('configuration')) {
            return;
        }

        store = Ext.create('Ext.data.Store', {
            model: 'Shopware.apps.Advisor.model.Answer',
            proxy: {
                type: 'ajax',
                url: '{url controller=Advisor action=getPropertyValuesAjax}',
                extraParams: {
                    streamId: advisor.get('streamId'),
                    propertyId: question.get('configuration')
                },
                reader: {
                    type: 'json',
                    root: 'data',
                    totalProperty: 'total'
                }
            }
        });

        me.answerGrid.setPossibleAnswers(store);
    }
});
//{/block}