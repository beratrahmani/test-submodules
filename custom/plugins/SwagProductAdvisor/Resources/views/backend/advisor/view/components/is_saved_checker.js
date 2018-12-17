//

//{namespace name="backend/advisor/main"}
//{block name="backend/advisor/view/components/is-saved-checker"}
Ext.define('Shopware.apps.Advisor.view.components.IsSavedChecker', {
    alias: 'widget.advisor-components-is-saved',

    /**
     * to check the advisor on close it is necessary to get on init some information's.
     *
     * @param { Shopware.apps.Advisor.model.Advisor | * } advisor
     */
    setAdvisor: function (advisor) {
        var me = this;

        me.questionLength = advisor.getQuestions().getCount();

        me.answersLength = [];
        advisor.getQuestions().each(function (question) {
            me.answersLength[question.internalId] = question.getAnswers().getCount();
        });
    },

    /**
     * @param { Shopware.apps.Advisor.model.Advisor } advisor
     * @param { Ext.form.Panel } formPanel
     * @returns { boolean }
     */
    isSaved: function (advisor, formPanel) {
        var me = this,
            advisorIsSaved = true,
            questionsAreSaved = true,
            answersAreSaved = true;

        // if the advisor not a "Shopware.apps.Advisor.model.Advisor" throw a excception
        if (!advisor || advisor.$className !== 'Shopware.apps.Advisor.model.Advisor') {
            throw 'Model is not a Shopware.apps.Advisor.model.Advisor';
        }

        // check the advisor for changes
        if (!me.checkAdvisor(advisor, formPanel)) {
            advisorIsSaved = false;
        }

        // check each question field for changes
        advisor.getQuestions().each(function (question) {
            if (!me.checkModel(question)) {
                questionsAreSaved = false;
            }

            // check each answer field for changes
            question.getAnswers().each(function (answer) {
                if (!me.checkModel(answer)) {
                    answersAreSaved = false;
                }
            })
        });

        return !(!advisorIsSaved || !questionsAreSaved || !answersAreSaved);
    },

    /**
     * check the advisor-model for changes.. for this we need the formPanel,
     * because the changes will be set later in the model.
     *
     * @param advisor
     * @param formPanel
     * @returns { boolean }
     */
    checkAdvisor: function (advisor, formPanel) {
        var me = this,
            formPanelItems = formPanel.getValues(),
            blacklist = me.getBlackList(),
            isSaved = true;

        Ext.Array.each(advisor.self.getFields(), function (modelField) {
            if (blacklist.indexOf(modelField.name) !== -1) {
                return;
            }

            if (advisor.get(modelField.name) != formPanelItems[modelField.name]) {
                isSaved = false;
            }
        });

        // check for deleted questions
        if (me.questionLength != advisor.getQuestions().getCount()) {
            isSaved = false;
        }

        // check each question for deleted answers
        advisor.getQuestions().each(function (question) {
            if (question.getAnswers().getCount() != me.answersLength[question.internalId]) {
                isSaved = false;
            }
        });

        return isSaved;
    },

    /**
     * @param { Ext.data.Model | * } model
     * @returns { boolean }
     */
    checkModel: function (model) {
        var me = this,
            isNotSaved = false;

        Ext.Array.each(model.self.getFields(), function (modelField) {
            if (model.isModified(modelField.name)) {
                isNotSaved = true;
            }
        });

        return !isNotSaved;
    },

    /**
     * This is the Blacklist for the checkAdvisor function.
     * Each property that not can be change from the user need to add to this list.
     *
     * @returns { { id: boolean, links: boolean, mode: boolean } }
     */
    getBlackList: function () {
        return [
            'id',
            'links',
            'mode'
        ];
    }
});
//{/block}