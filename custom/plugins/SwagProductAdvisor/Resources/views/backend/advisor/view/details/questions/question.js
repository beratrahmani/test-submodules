//

//{namespace name="backend/advisor/main"}
//{block name="backend/advisor/view/details/questions/question"}
Ext.define('Shopware.apps.Advisor.view.details.questions.Question', {
    extend: 'Shopware.model.Container',
    alias: 'widget.advisor-details-questions-question',
    name: 'advisor-question-window',
    id: 'advisor-question-window',

    padding: 20,

    snippets: {
        fieldSetTitle: '{s name="window_queston_fieldset_title"}Question{/s}',
        question: '{s name="window_queston_question"}Your question{/s}',
        infoText: '{s name="window_queston_info_text"}Information text{/s}',
        type: '{s name="window_question_type"}Type{/s}',
        needsToBeAnswered: '{s name="window_question_needs_to_be_answered"}Question needs to be answered{/s}',
        boost: '{s name="window_question_boost_label"}Weighting of the question{/s}',
        boostHelpText: '{s name="window_question_boost_help_text"}This setting could weighted this question more. Thus, an article that applies to this question appears above.{/s}',
        exclude: '{s name="window_question_exclude"}{/s}',
        hideText: '{s name="window_question_hide_text"}Hide answer text{/s}',
        expandQuestion: '{s name="window_question_expand_question"}Expand question{/s}',
        typeHelpText: '{s name="window_question_type_help_text"}The type of the question. Possible types are an attribute-, a property-, a supplier- or a price-question.{/s}',
        requiredHelpText: '{s name=window_question_required_help_text}The question can not be skipped and therefore there will be no result when this configuration is set and the question was not answered.{/s}',
        excludeHelpText: '{s name=window_question_exclude_help_text}If this configuration is set, all articles, which do not match to at least one of the given answers of a question, will be filtered. Therefore this could lead to no results being found.{/s}',
        expandQuestionHelpText: '{s name=window_question_expand_help_text}If you enable this configuration, this question will be initially expanded on the start-page of the advisor.{/s}'
    },

    currentQuestionHandler: null,

    /**
     * @returns { { splitFields: boolean, fieldSets: *[] } }
     */
    configure: function () {
        var me = this;

        return {
            splitFields: false,
            fieldSets: [{
                fields: {
                    question: {
                        allowBlank: true,
                        fieldLabel: me.snippets.question,
                        translatable: true
                    },
                    infoText: {
                        xtype: 'tinymce',
                        allowBlank: true,
                        fieldLabel: me.snippets.infoText,
                        translatable: true
                    },
                    boost: {
                        xtype: 'numberfield',
                        helpText: me.snippets.boostHelpText,
                        fieldLabel: me.snippets.boost,
                        minValue: 1,
                        listeners: {
                            change: Ext.bind(me.onBoostChange, me)
                        }
                    },
                    needsToBeAnswered: {
                        fieldLabel: me.snippets.needsToBeAnswered,
                        helpText: me.snippets.requiredHelpText,
                        width: '40px',
                        anchor: 0
                    },
                    exclude: {
                        fieldLabel: me.snippets.exclude,
                        helpText: me.snippets.excludeHelpText,
                        width: '40px',
                        anchor: 0
                    },
                    expandQuestion: {
                        fieldLabel: me.snippets.expandQuestion,
                        helpText: me.snippets.expandQuestionHelpText,
                        width: '40px',
                        anchor: 0
                    },
                    layoutContent: me.createLayoutContent,
                    type: me.createTypeField,
                    hideText: {
                        fieldLabel: me.snippets.hideText
                    },
                    questionContent: me.createContent
                },
                title: me.snippets.fieldSetTitle
            }]
        }
    },

    /**
     * @param { Ext.form.field.Number } numberField
     * @param { int | string } newValue
     */
    onBoostChange: function (numberField, newValue) {
        if (newValue < 1) {
            numberField.setValue(1)
        }
    },

    /**
     * @returns { Ext.container.Container | * }
     */
    createContent: function () {
        var me = this;
        me.contentContainer = Ext.create('Ext.container.Container', {
            layout: 'anchor'
        });

        return me.contentContainer;
    },

    /**
     * @returns { Shopware.apps.Advisor.view.details.ui.AnswerLayoutSelection }
     */
    createLayoutContent: function () {
        var me = this;

        me.layoutSelection = Ext.create('Shopware.apps.Advisor.view.details.ui.AnswerLayoutSelection', {
            question: me.record,
            advisor: me.subApp.detailAdvisor,
            listeners: {
                'advisor_multiple_answer_checkbox_change': function (newValue) {
                    me.onMultipleAnswersChanged(newValue);
                },
                'advisor_answer_layout_selection_change': function (newValue) {
                    me.onLayoutSelectionChanged(newValue);
                }
            }
        });

        return me.layoutSelection;
    },

    /**
     * @overwrite
     *
     * @returns { * }
     */
    initComponent: function () {
        var me = this;

        me.questionHandlers = me.registerQuestionHandlers();

        me.callParent(arguments);

        me.hideTextField = me.down('[name="hideText"]');
        me.hideTextField.hide();

        me.expandQuestionField = me.down('[name=expandQuestion]');

        if (me.subApp.detailAdvisor.get('mode') !== 'sidebar_mode') {
            me.expandQuestionField.hide();
        }
    },

    /**
     * @overwrite
     *
     * This method is overwritten only to overwrite the labelWidth from 130px to 150px
     *
     * @param { String } modelName
     * @param { * } fields
     * @param { * } customConfig
     *
     * @return Ext.form.FieldSet
     */
    createModelField: function (modelName, fields, customConfig) {
        var me = this,
            formField = me.callParent(arguments);

        formField.labelWidth = 150;

        return formField;
    },

    /**
     * @returns { *[] }
     */
    registerQuestionHandlers: function () {
        return [
            Ext.create('Shopware.apps.Advisor.view.details.questions.Attribute'),
            Ext.create('Shopware.apps.Advisor.view.details.questions.Property'),
            Ext.create('Shopware.apps.Advisor.view.details.questions.Manufacturer'),
            Ext.create('Shopware.apps.Advisor.view.details.questions.Price')
        ];
    },

    /**
     * @returns { Ext.form.field.ComboBox | * }
     */
    createTypeField: function () {
        var me = this,
            data = [];

        Ext.each(me.questionHandlers, function (handler) {
            data.push({
                key: handler.getKey(),
                label: handler.getLabel()
            });
        });

        me.typeStore = me.createTypeFieldStore(data);
        me.typeField = me.createTypeFieldCombo();

        return me.typeField;
    },

    /**
     * create the TypeCombo
     *
     * @returns { Ext.form.field.ComboBox }
     */
    createTypeFieldCombo: function () {
        var me = this;

        me.typeFieldCombo = Ext.create('Ext.form.field.ComboBox', {
            labelWidth: 150,
            anchor: '100%',
            store: me.typeStore,
            valueField: 'key',
            allowBlank: false,
            editable: false,
            forceSelection: true,
            displayField: 'label',
            name: 'type',
            helpText: me.snippets.typeHelpText,
            fieldLabel: me.snippets.type,
            listeners: {
                change: Ext.bind(me.displayQuestionContent, me),
                select: Ext.bind(me.clearRecordAnswers, me)
            }
        });

        return me.typeFieldCombo;
    },

    /**
     * this is a little fix. clear all values in the answerGrid,
     * to do not confuse the user and to force him to fill all
     * required fields below again
     */
    clearRecordAnswers: function () {
        var me = this;

        me.record.getAnswers().removeAll();
        me.record.set('configuration', null);
    },

    /**
     * create TypeStore
     *
     * @param { Array } data
     * @returns { Ext.data.Store }
     */
    createTypeFieldStore: function (data) {
        return Ext.create('Ext.data.Store', {
            fields: ['key', 'label'],
            data: data
        });
    },

    /**
     * @param { Ext.form.field.Combobox } combo
     * @param { string } newValue
     */
    displayQuestionContent: function (combo, newValue) {
        var me = this,
            callBackConfig;

        me.contentContainer.removeAll();

        if (!newValue) {
            return;
        }

        me.currentQuestionHandler = me.getQuestionHandlerByKey(newValue);
        me.contentContainer.add(
            me.currentQuestionHandler.createQuestion(me.subApp.detailAdvisor, me.record, me)
        );

        me.layoutSelection.setTemplates(me.currentQuestionHandler.getLayouts(me.subApp.detailAdvisor, me.record));

        callBackConfig = me.currentQuestionHandler.updateQuestionViewData(
            me.layoutSelection.getSelectedTemplate(),
            me.record.getAnswers()
        );

        if (!callBackConfig) {
            me.layoutSelection.multipleAnswerCheckbox.show();
            return;
        }

        me.applyCallBackConfig(callBackConfig);
    },

    /**
     * @param { string | * } key
     *
     * @returns { * }
     */
    getQuestionHandlerByKey: function (key) {
        var me = this;
        var handler = null;

        Ext.each(me.questionHandlers, function (item) {
            if (item.getKey() === key) {
                handler = item;
                return false;
            }
        });

        return handler;
    },

    /**
     * @param { string | * } newValue
     */
    onMultipleAnswersChanged: function (newValue) {
        var me = this;

        me.record.set('multipleAnswers', newValue);

        if (!me.currentQuestionHandler) {
            return;
        }
        me.layoutSelection.setTemplates(me.currentQuestionHandler.getLayouts(me.subApp.detailAdvisor, me.record));
        me.layoutSelection.answerLayoutCombobox.select(me.layoutSelection.answerLayoutCombobox.getStore().getAt(0));
    },

    /**
     * @param { string | * } newValue
     */
    onLayoutSelectionChanged: function (newValue) {
        var me = this,
            callBackConfig;

        if (null == newValue) {
            return;
        }

        me.showHideFields(newValue);

        me.record.set('template', newValue);

        callBackConfig = me.currentQuestionHandler.updateQuestionViewData(
            me.layoutSelection.getSelectedTemplate(newValue),
            me.record.getAnswers()
        );

        if (!callBackConfig) {
            me.layoutSelection.multipleAnswerCheckbox.show();
            return;
        }

        me.applyCallBackConfig(callBackConfig);
    },

    /**
     * @param { * } config
     */
    applyCallBackConfig: function (config) {
        var me = this;

        me.layoutSelection.multipleAnswerCheckbox[config.hideCheckbox ? 'hide' : 'show']();
    },

    /**
     * @param { string } newValue
     */
    showHideFields: function (newValue) {
        var me = this;

        if(newValue.search('(_image)') == -1) {
            me.hideTextField.hide();
            me.record.set('hideText', false);
            return;
        }

        me.hideTextField.show();
    }
});
//{/block}