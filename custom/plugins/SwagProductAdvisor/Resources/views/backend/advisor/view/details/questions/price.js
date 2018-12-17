//

//{namespace name="backend/advisor/main"}
//{block name="backend/advisor/view/details/questions/price"}
Ext.define('Shopware.apps.Advisor.view.details.questions.Price', {
    extend: 'Shopware.apps.Advisor.view.details.questions.AbstractQuestion',

    label: '{s name="filter_priceLabel"}Price{/s}',

    snippets: {
        displayField: '{s name="filter_price_display_field"}Max price in Product-Stream{/s}'
    },

    rangeStore: [
        { key: 'minPrice', value: '{s name="filter_price_min"}Min price{/s}' },
        { key: 'maxPrice', value: '{s name="filter_price_max"}Max price{/s}' }
    ],

    /**
     * @overwrite
     *
     * @returns { string }
     */
    getKey: function () {
        return 'price';
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

        me.advisor = advisor;
        me.question = question;

        question.set('type', me.getKey());

        me.answerGrid = Ext.create('Shopware.apps.Advisor.view.details.ui.AnswerGrid', {
            advisor: advisor,
            question: question,
            refreshGridData: function (advisor, question, store) {
                me.answerGrid.__proto__.refreshGridData.apply(me.answerGrid, arguments);
                me.refreshPrices(advisor, question, store);
            }
        });

        me.displayField = Ext.create('Ext.form.field.Display', {
            margin: '10px 0 0 0',
            fieldLabel: me.snippets.displayField,
            labelWidth: 150,
            value: 0,
            hidden: true
        });

        me.answerGrid.refreshGridData(advisor, question, question.getAnswers());

        return [
            me.answerGrid,
            me.displayField
        ];
    },

    /**
     * @param { Shopware.apps.Advisor.view.components.layouts.AbstractLayout } layout
     *
     * @returns { null | { object } }
     */
    updateQuestionViewData: function (layout) {
        var me = this;

        me.answerGrid.reconfigureGrid(layout);
        me.refreshPrices(me.advisor, me.question, me.answerGrid.store);

        return {
            hideCheckbox: true
        };
    },

    /**
     * @param { Shopware.apps.Advisor.model.Advisor } advisor
     * @param { Shopware.apps.Advisor.model.Question } question
     * @param { Ext.data.Store } store
     */
    refreshPrices: function (advisor, question, store) {
        var me = this;

        // find out the MaxPrice in Stream an show it to the user
        me.getMaxPriceInStoreSetToDisplayField(advisor);

        // in the next step we must find out what template is Selected.
        // if the template is a "range_slider" we must on create set the default
        // dataSet. Else we could leave the store empty
        if (question.get('template') == 'range_slider') {
            me.mergeStoreAndAnswers(store, me.rangeStore);
            return;
        }

        me.refreshFromDatabase(question, store);
    },

    /**
     * @param { Shopware.apps.Advisor.model.Advisor } advisor
     */
    getMaxPriceInStoreSetToDisplayField: function (advisor) {
        var me = this;

        Ext.Ajax.request({
            url: '{url controller=Advisor action=getMaxPriceAjax}',
            method: 'POST',
            params: {
                streamId: advisor.get('streamId')
            },
            success: function (operation) {
                var response = Ext.decode(operation.responseText);

                me.displayField.setValue(response.data);
                me.displayField.show();
            }
        });
    },

    /**
     * @param { Shopware.apps.Advisor.model.Question } question
     * @param { Ext.data.Store } store
     */
    refreshFromDatabase: function (question, store) {
        var me = this;

        if (question.get('id')) {

            Ext.Ajax.request({
                url: '{url controller=Advisor action=getSavedPrices}',
                method: 'POST',
                params: {
                    questionId: question.get('id')

                },
                success: function (operation) {
                    var response = Ext.decode(operation.responseText),
                        data = [];

                    Ext.Array.each(response.data, function (row) {
                        if (row.key != 'minPrice' && row.key != 'maxPrice') {
                            data.push(row);
                        }
                    });

                    me.mergeStoreAndAnswers(store, data);
                }
            });
        } else {
            store.removeAll();
        }
    }
});
//{/block}