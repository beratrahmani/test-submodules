//

//{namespace name="backend/advisor/main"}
//{block name="backend/advisor/view/details/questions/attribute"}
Ext.define('Shopware.apps.Advisor.view.details.questions.Attribute', {
    extend: 'Shopware.apps.Advisor.view.details.questions.AbstractQuestion',

    label: '{s name="filter_attributeLabel"}Attribute{/s}',

    /**
     * @overwrite
     *
     * @returns { string }
     */
    getKey: function () {
        return 'attribute';
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
                // the next line ist to call the prototype function to prevent overwrite calls without
                // call the parent data.
                me.answerGrid.__proto__.refreshGridData.apply(me.answerGrid, arguments);

                me.refreshAttributes(advisor, question);
            }
        });

        me.attributeSelection = Ext.create('Shopware.apps.Advisor.view.details.ui.AttributeSelection', {
            advisor: advisor,
            question: question,
            answerGrid: me.answerGrid
        });

        me.answerGrid.refreshGridData(advisor, question, question.getAnswers());

        return [
            me.attributeSelection,
            me.answerGrid
        ]
    },

    /**
     * @overwrite
     *
     * @param { Shopware.apps.Advisor.view.components.layouts.AbstractLayout | null | * } layout
     */
    updateQuestionViewData: function (layout) {
        var me = this;

        if (!layout) {
            return;
        }

        me.answerGrid.reconfigureGrid(layout);
    },

    /**
     * @param { Shopware.apps.Advisor.model.Advisor } advisor
     * @param { Shopware.apps.Advisor.model.Question } question
     */
    refreshAttributes: function (advisor, question) {
        var me = this;

        if (!question.get('configuration')) {
            return;
        }

        me.attributeValueStore = Ext.create('Ext.data.Store', {
            model: 'Shopware.apps.Advisor.model.Answer',
            proxy: {
                type: 'ajax',
                url: '{url controller=Advisor action=getAttributeValuesAjax}',
                extraParams: {
                    streamId: advisor.get('streamId'),
                    attributeColumn: question.get('configuration')
                },
                reader: {
                    type: 'json',
                    root: 'data',
                    totalProperty: 'total'
                }
            }
        });

        me.answerGrid.setPossibleAnswers(me.attributeValueStore);
    }
});
// {/block}