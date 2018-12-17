//

//{namespace name="backend/advisor/main"}
//{block name="backend/advisor/view/details/preview"}
Ext.define('Shopware.apps.Advisor.view.details.Preview', {
    extend: 'Enlight.app.Window',
    alias: 'widget.details-Preview',
    title: '{s name="preview_window_title"}Preview{/s}',
    layout: 'border',
    height: '90%',
    width: '70%',
    modal: true,

    bodyPadding: 20,
    bodyStyle: {
        background: '#F0F2F4'
    },

    snippets: {
        fieldSetTitle: '{s name="preview_window_filter_label"}Questions{/s}',
        filterButton: '{s name="preview_window_button_find_article"}Show products{/s}',
        close: '{s name="close_button_txt"}Close{/s}',
        previewGridName: '{s name="preview_window_col_article"}Article{/s}',
        previewGridMatches: '{s name="preview_window_col_matches"}Matches{/s}',
        previewFieldSetTitle: '{s name="preview_window_article_preview"}Article preview{/s}',
        boost: '{s name="preview_column_boost_title"}Weighting{/s}'
    },

    /**
     * it is necessary to set the advisor in the initial call
     *
     * For example:
     *      var previewWindow = Ext.create('Shopware.apps.Advisor.view.details.Preview');
     *
     *      previewWindow.setAdvisor(ADVISOR);
     *      previewWindow.show();
     */
    advisor: null,

    /**
     * @overwrite
     *
     * if show method is called..... first check if a advisor is set
     * if no advisor is set throw a Exception
     */
    show: function () {
        var me = this;

        if (me.advisor === null) {
            throw 'no advisor is set in "Shopware.apps.Advisor.view.details.Preview"';
        } else {
            me.callParent(arguments);
        }
    },

    /**
     * init function
     */
    initComponent: function () {
        var me = this;

        me.items = me.createItems();
        me.dockedItems = me.createBBar();

        me.callParent(arguments);
    },

    createItems: function () {
        var me = this;

        return [
            me.createQuestionPanel(),
            me.createArticlePanel()
        ];
    },

    /**
     * @returns { Ext.panel.Panel }
     */
    createQuestionPanel: function () {
        var me = this;

        return Ext.create('Ext.panel.Panel', {
            title: me.snippets.fieldSetTitle,

            width: '40%',
            layout: 'fit',
            items: [ me.createFormPanel() ],
            dockedItems: [ me.createAnswerGridToolbar() ],
            bodyStyle: me.defaultBodyStyle,
            region: 'west'
        });
    },

    /**
     * @returns { Ext.toolbar.Toolbar }
     */
    createAnswerGridToolbar: function () {
        var me = this;

        return Ext.create('Ext.toolbar.Toolbar', {
            dock: 'bottom',
            border: 1,
            style: {
                borderColor: '#A4B5C0',
                borderStyle: 'solid'
            },
            ui: 'shopware-ui',
            items: [
                '->',
                me.createFilterButton()
            ]
        });
    },

    /**
     * @returns { Ext.button.Button | * }
     */
    createFilterButton: function () {
        var me = this;

        me.filterButton = Ext.create('Ext.button.Button', {
            text: me.snippets.filterButton,
            handler: function () {
                me.searchArticle(me);
            }
        });

        return me.filterButton;
    },

    /**
     * @param { Shopware.apps.Advisor.view.details.Preview } me
     */
    searchArticle: function (me) {
        me.setLoading(true);
        var formValues = me.formPanel.getForm().getValues();

        Ext.Ajax.request({
            url: '{url controller="Advisor" action="findProduct"}',
            params: {
                advisorId: me.advisor.get('id'),
                answers: Ext.JSON.encode(formValues),
                currency: me.contextToolBar.getCurrencyValue(),
                shop: me.contextToolBar.getShopValue(),
                customer: me.contextToolBar.getCustomerValue()
            },
            success: function (response) {
                var responseResult = Ext.JSON.decode(response.responseText);

                me.reconfigureGrid(responseResult);
                me.setLoading(false);
            }
        });
    },

    /**
     * Needs the response from the AjaxRequest SearchArticle
     *
     * @param { * } response
     */
    reconfigureGrid: function (response) {
        var me = this,
            store = me.createNewStore(response);

        me.previewGrid.reconfigure(store);
    },

    /**
     * @param { * } response
     * @returns { Ext.data.Store }
     */
    createNewStore: function (response) {
        return Ext.create('Ext.data.Store', {
            fields: [ 'id', 'name', 'matches', 'boost' ],
            data: {
                data: response.result
            },
            proxy: {
                type: 'memory',
                reader: {
                    type: 'json',
                    root: 'data'
                }
            }
        });
    },

    /**
     * @returns { Ext.form.Panel }
     */
    createFormPanel: function () {
        var me = this;

        me.formPanel = Ext.create('Ext.form.Panel', {
            overflowY: 'auto',
            border: false
        });

        return me.formPanel;
    },

    /**
     * @returns { Ext.form.FieldSet }
     */
    createArticlePanel: function () {
        var me = this;

        me.contextToolBar = Ext.create('Shopware.apps.Advisor.view.details.ui.Context');

        me.previewGrid = Ext.create('Ext.grid.Panel', {
            title: me.snippets.previewFieldSetTitle,
            tbar: me.contextToolBar,
            region: 'center',
            margin: '0 0 0 20',
            columns: [
                { text: me.snippets.previewGridName, dataIndex: 'name', flex: 6 },
                { text: me.snippets.previewGridMatches, dataIndex: 'matches', flex: 1 },
                { text: me.snippets.boost, dataIndex: 'boost', flex: 1 }
            ]
        });

        return me.previewGrid;
    },

    /**
     * @param { Shopware.apps.Advisor.model.Advisor | * } advisor
     */
    setAdvisor: function (advisor) {
        var me = this;

        me.advisor = advisor;

        me.advisor.getQuestions().each(function (question) {
            me.formPanel.add(me.getQuestionUI(question));
            me.formPanel.add(Ext.create('Ext.container.Container', {
                height: 1,
                anchor: '100%',
                style: 'background:lightgray;',
                margin: '20px 0'
            }));
        });
    },

    /**
     * @param { Shopware.apps.Advisor.model.Question } question
     * @returns { * }
     */
    getQuestionUI: function (question) {
        var me = this;

        switch (question.get('template')) {
            // singleChoice
            case 'radio_image':
            case 'radio':
            case 'combobox':
                return me.createSingleSelection(question);
            // multipleChoice
            case 'checkbox_image':
            case 'checkbox':
                return me.createMultiSelection(question);
            // rangeSelection
            case 'range_slider':
                return me.createRangeSelection(question);
            default:
                break;
        }
    },

    /**
     * @param { Shopware.apps.Advisor.model.Question } question
     * @returns { Ext.form.field.ComboBox }
     */
    createSingleSelection: function (question) {
        var me = this,
            id = 'q' + question.get('id') + '_values',
            store = me.createSingleSelectionStore(question),
            fieldName = 'id';

        if (question.get('type') === 'price') {
            id += '_max';
            fieldName = 'answer';
        }

        return Ext.create('Ext.form.field.ComboBox', {
            anchor: '100%',
            margin: '10 20',
            name: id,
            displayField: 'answer',
            valueField: fieldName,
            fieldLabel: question.get('question'),
            labelWidth: 150,
            store: store
        });
    },

    /**
     * @param { Shopware.apps.Advisor.model.Question } question
     * @returns { Ext.data.Store }
     */
    createSingleSelectionStore: function (question) {
        var me = this;

        return Ext.create('Ext.data.Store', {
            model: 'Shopware.apps.Advisor.model.Answer',
            data: {
                data: me.createComboData(question)
            },
            proxy: {
                type: 'memory',
                reader: {
                    type: 'json',
                    root: 'data'
                }
            }
        });
    },

    /**
     * @param { Shopware.apps.Advisor.model.Question } question
     * @returns { Array }
     */
    createComboData: function (question) {
        var data = [];

        question.getAnswers().each(function (answer) {
            if (!answer.data.answer) {
                answer.data.answer = answer.data.value;
            }
            data.push(answer.data);
        });

        return data;
    },

    /**
     * @param { Shopware.apps.Advisor.model.Question } question
     * @returns { Ext.form.FieldContainer }
     */
    createMultiSelection: function (question) {
        var me = this;

        return Ext.create('Ext.form.FieldContainer', {
            defaultType: 'checkbox',
            margin: '10 20',
            anchor: '100%',
            labelWidth: 150,
            fieldLabel: question.get('question'),
            items: me.createFieldItems(question)
        });
    },

    /**
     * @param { Shopware.apps.Advisor.model.Question } question
     * @returns { Array }
     */
    createFieldItems: function (question) {
        var fieldItems = [],
            val = 'answer';

        question.getAnswers().each(function (answer) {
            fieldItems.push({
                id: answer.get('id') + ',' + question.get('id'),
                boxLabel: answer.get(val) ? answer.get(val) : answer.get('value') + '',
                inputValue: answer.get('id'),
                name: 'q' + question.get('id') + '_values'
            });
        });

        return fieldItems;
    },

    /**
     * @param { Shopware.apps.Advisor.model.Question } question
     * @returns { Shopware.apps.Advisor.view.details.ui.Range }
     */
    createRangeSelection: function (question) {
        var rangeView = Ext.create('Shopware.apps.Advisor.view.details.ui.Range', {
            margin: '10 20'
        });

        rangeView.setQuestion(question);

        return rangeView;
    },

    /**
     * @returns { Ext.toolbar.Toolbar }
     */
    createBBar: function () {
        var me = this;

        me.okButton = me.createOkButton();

        return Ext.create('Ext.toolbar.Toolbar', {
            ui: 'shopware-ui',
            dock: 'bottom',
            items: [
                '->',
                me.okButton
            ]
        });
    },

    /**
     * @returns { Ext.button.Button }
     */
    createOkButton: function () {
        var me = this;

        return Ext.create('Ext.button.Button', {
            text: me.snippets.close,
            cls: 'primary',
            handler: me.close,
            scope: me
        });
    }
});
//{/block}
