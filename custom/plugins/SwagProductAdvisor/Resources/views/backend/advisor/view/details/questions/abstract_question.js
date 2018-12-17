//

//{namespace name="backend/advisor/main"}
//{block name="backend/advisor/view/details/questions/abstract-question"}
Ext.define('Shopware.apps.Advisor.view.details.questions.AbstractQuestion', {
    /**
     * On Extend:
     * this method must be overridden
     */
    getKey: function () {
        throw 'Unimplemented method.';
    },

    /**
     * On Extend:
     * this method must be overridden
     */
    getLabel: function () {
        throw 'Unimplemented method.';
    },

    /**
     * On Extend:
     * this method must be overridden
     *
     * @param advisor
     * @param question
     * @param parent
     */
    createQuestion: function (advisor, question, parent) {
        throw 'Unimplemented method.';
    },

    /**
     * On Extend:
     * this method must be overridden
     *
     * @param { Shopware.apps.Advisor.view.components.layouts.AbstractLayout | * } layout
     * @param { Ext.data.Store | * } answerStore
     * @param { * } payload
     *
     * here u could return a object with a config to hide the
     * multiple answer checkbox
     * @returns { null | { object } }
     */
    updateQuestionViewData: function (layout, answerStore, payload) {
        throw  'Unimplemented method.';
    },

    /**
     * @param { Shopware.apps.Advisor.model.Advisor } advisor
     * @param { Shopware.apps.Advisor.model.Question } question
     * @returns { * | Array }
     */
    getLayouts: function (advisor, question) {
        var layouts = Ext.create('Shopware.apps.Advisor.view.components.Layouts');

        if (question.get('type') == 'price') {
            return layouts.getPriceTemplates(advisor.get('mode'));
        }

        return layouts.getTemplates(advisor.get('mode'), question.get('multipleAnswers'));
    },

    /**
     * Requires the store of answerGrid and a
     * array with the response data for the store.
     *
     * responseData example:
     * array[
     *      [0] {
     *          key: 'XXX',  // the id
     *          value: 'XXX' // the display value
     *       },
     *      [1] {
     *          key: 'XXX',  // the id
     *          value: 'XXX' // the display value
     *      }
     * ]
     *
     * @param { Ext.data.Store } store
     * @param { Array | * } responseData
     */
    mergeStoreAndAnswers: function (store, responseData) {
        var me = this;

        me.deleteNonExistent(store, responseData);
        me.addNonExistent(store, responseData);
    },

    /**
     * @param { Ext.data.store } store
     * @param { Array | * } responseData
     */
    deleteNonExistent: function (store, responseData) {
        var itemsToRemove = [];

        /**
         * 1. Iterate store to see if the entries from the grid also are still in the new data array.
         *      1.1 Delete non-existent
         */
        store.each(function (item) {
            var inArray = false;

            Ext.Array.each(responseData, function (dataItem) {
                if (dataItem.value == item.get('value')) {
                    inArray = true;
                }
            });

            if (!inArray) {
                itemsToRemove.push(item);
            }
        });

        Ext.Array.each(itemsToRemove, function (itemToRemove) {
            store.remove(itemToRemove);
        });
    },

    /**
     * @param { Ext.data.Store } store
     * @param { Array | * } responseData
     */
    addNonExistent: function (store, responseData) {
        /**
         * 2. iterate responseData. Check whether the entry in the store is available.
         *      2.1 if not (generate new model.Answer and then add)
         */
        Ext.Array.each(responseData, function (item) {
            var inStore = false,
                newAnswer;

            store.each(function (storeItem) {
                if (storeItem.get('value') == item.value) {
                    inStore = true
                }
            });

            if (inStore) {
                return;
            }

            newAnswer = Ext.create('Shopware.apps.Advisor.model.Answer', item);

            store.add(newAnswer);
        });
    }
});
//{/block}