//

//{namespace name="backend/advisor/main"}
//{block name="backend/advisor/view/details/questions/manufacturer"}
Ext.define('Shopware.apps.Advisor.view.details.questions.Manufacturer', {
    extend: 'Shopware.apps.Advisor.view.details.questions.AbstractQuestion',

    label: '{s name="filter_manufacturerLabel"}Manufacturer{/s}',

    /**
     * @overwrite
     *
     * @returns { string }
     */
    getKey: function () {
        return 'manufacturer';
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
                me.refreshManufacturers(advisor);
            }
        });

        me.answerGrid.refreshGridData(advisor, question, question.getAnswers());

        return [
            me.answerGrid
        ];
    },

    /**
     * @overwrite
     *
     * @param layout
     */
    updateQuestionViewData: function (layout) {
        var me = this;

        me.answerGrid.reconfigureGrid(layout);
    },

    /**
     * @param { Shopware.apps.Advisor.model.Advisor } advisor
     */
    refreshManufacturers: function (advisor) {
        var me = this,
            store;

        store = Ext.create('Ext.data.Store', {
            model: 'Shopware.apps.Advisor.model.Answer',
            proxy: {
                type: 'ajax',
                url: '{url controller=Advisor action=getManufacturerAjax}',
                extraParams: {
                    streamId: advisor.get('streamId')
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