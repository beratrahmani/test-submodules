//

//{namespace name="backend/advisor/main"}
//{block name="backend/advisor/view/details/questions"}
Ext.define('Shopware.apps.Advisor.view.details.Questions', {
    extend: 'Shopware.grid.Panel',
    alias: 'widget.advisor-details-questions',

    snippets: {
        title: '{s name="tabs_title_questions"}Questions{/s}',
        question: '{s name="questions_grid_questions"}Questions{/s}',
        type: '{s name="window_question_type"}Type{/s}',
        dragAndDrop: '{s name="drag_and_drop_order"}Drag and drop to reorder{/s}',
        hintDragDrop: '{s name=hint_dragdrop}You can move rows via Drag & Drop{/s}',
        types: {
            attribute: '{s name="filter_attributeLabel"}Attribute{/s}',
            property: '{s name="filter_propertyLabel"}Property{/s}',
            manufacturer: '{s name="filter_manufacturerLabel"}Manufacturer{/s}',
            price: '{s name="filter_priceLabel"}Price{/s}'
        }
    },

    /**
     * @param { Ext.data.Store } store
     * @param { Shopware.apps.Advisor.model.Advisor } record
     */
    reloadData: function (store, record) {
        var me = this;

        me.callParent(arguments);

        me.reconfigure(store);
    },

    /**
     * @overwrite
     */
    initComponent: function () {
        var me = this;

        me.viewConfig = me.createDragAndDrop();

        me.callParent(arguments);

        me.title = me.snippets.title;
    },

    /**
     * We need to use our very own selection-model to fix an issue with the drag'n'drop-plugin and the selection-model
     * @overwrite
     */
    createSelectionModel: function () {
        var me = this, selModel;

        selModel = Ext.create('Shopware.apps.Advisor.view.details.ui.AdvisorSelectionModel', {
            listeners: {
                selectionchange: function (selModel, selection) {
                    return me.fireEvent(me.eventAlias + '-selection-changed', me, selModel, selection);
                }
            }
        });

        me.fireEvent(me.eventAlias + '-selection-model-created', me, selModel);

        return selModel;
    },

    /**
     * @returns
     * { { columns: { question: { header: string, flex: number }, type: { header: string, flex: number } },
     *             pagingbar: boolean, detailWindow: string } }
     */
    configure: function () {
        var me = this;

        return {
            columns: {
                question: {
                    header: me.snippets.question,
                    flex: 3,
                    hideable: false,
                    sortable: false,
                    menuDisabled: true
                },
                type: {
                    header: me.snippets.type,
                    flex: 1,
                    renderer: me.typeColumnRenderer,
                    scope: me,
                    hideable: false,
                    sortable: false,
                    menuDisabled: true
                }
            },
            pagingbar: false,
            detailWindow: 'Shopware.apps.Advisor.view.details.questions.Window'
        }
    },

    /**
     * @returns { * }
     */
    createColumns: function () {
        var me = this,
            columns = me.callParent(arguments),
            ddIndicatorColumn = {
                header: '&#009868',
                width: 24,
                renderer: me.renderSortHandleColumn,
                hideable: false,
                sortable: false,
                menuDisabled: true
            };

        columns = Ext.Array.insert(columns, 0, [ddIndicatorColumn]);

        return columns;
    },

    /**
     * Create a copyButton to create a copy of a question
     */
    createActionColumnItems: function () {
        var me = this,
            actionColumnItems = me.callParent(arguments);

        actionColumnItems.push(me.createCopyButton());

        return actionColumnItems;
    },

    /**
     * @returns { { iconCls: string, handler: handler } }
     */
    createCopyButton: function () {
        var me = this;

        return {
            iconCls: 'sprite-duplicate-article',
            handler: function (view, rowIndex, colIndex, item, opts, record) {
                var newRecord = Ext.create('Shopware.apps.Advisor.model.Question', record.raw);

                newRecord.set('id', null);
                newRecord.set('order', me.getStore().getCount());
                newRecord.set('translationCloneId', record.get('id'));

                if (record.getAnswers()) {
                    // if there a answerStore create a copy of each answer
                    record.getAnswers().each(function (answer) {
                        var newAnswer = Ext.create('Shopware.apps.Advisor.model.Answer', answer.raw);
                        newAnswer.data.id = null;
                        newRecord.getAnswers().add(newAnswer);
                    });
                }

                me.getStore().add(newRecord);
            }
        }
    },

    /**
     * Renderer for ddIndicatorColumn
     *
     * @param { string } value
     * @param { * } metadata
     * @returns { string }
     */
    renderSortHandleColumn: function (value, metadata) {
        var me = this;

        metadata.tdAttr = Ext.String.format('data-qtip="[0]"', me.snippets.hintDragDrop);

        return '<div style="cursor: n-resize;">&#009868;</div>';
    },

    /**
     * @param { string } value
     * @returns { * }
     */
    typeColumnRenderer: function (value) {
        var me = this;

        switch (value) {
            case 'attribute':
                return me.snippets.types.attribute;
            case 'property':
                return me.snippets.types.property;
            case 'manufacturer':
                return me.snippets.types.manufacturer;
            case 'price':
                return me.snippets.types.price;
            default:
                return '';
        }
    },

    /**
     * @overwrite
     */
    createEditColumn: function () {
        var me = this,
            column = me.callParent(arguments);

        column.handler = function (view, rowIndex, colIndex, item, opts, record) {
            me.createDetailWindow(record);
        };

        return column;
    },

    /**
     * @overwrite
     */
    createAddButton: function () {
        var me = this,
            button = me.callParent(arguments);

        button.handler = function () {
            me.createDetailWindow(me.createNewRecord());
        };

        return button;
    },

    /**
     * @returns { Shopware.apps.Advisor.model.Question }
     */
    createNewRecord: function () {
        var me = this,
            record = Ext.create('Shopware.apps.Advisor.model.Question'),
            order = me.getStore().getCount();

        record['getAnswersStore'] = Ext.create('Ext.data.Store', {
            model: 'Shopware.apps.Advisor.model.Answer'
        });

        record.set('order', order);

        return record;
    },

    /**
     * need the current record
     *
     * @param { Shopware.apps.Advisor.model.Question } record
     */
    createDetailWindow: function (record) {
        var me = this,
            window;

        me.subApp.detailAdvisor = me.advisor;

        window = Ext.create('Shopware.apps.Advisor.view.details.questions.Window', {
            record: record,
            listing: me
        });

        window.show();
    },

    /**
     * @returns
     * { { plugins: { ptype: string, dragText: string }, listeners: { drop:
     *              { fn: Shopware.apps.Advisor.view.details.Questions.onDrop,
     *                  scope: Shopware.apps.Advisor.view.details.Questions
     *           } } } }
     */
    createDragAndDrop: function () {
        var me = this;

        return {
            plugins: {
                ptype: 'gridviewdragdrop',
                dragText: me.snippets.dragAndDrop,
                dragGroup: 'questionsDD',
                dropGroup: 'questionsDD'
            },
            listeners: {
                drop: {
                    fn: me.onDrop,
                    scope: me
                }
            }
        };
    },

    /**
     * iterate over all questions and set the new index as order
     */
    onDrop: function () {
        var me = this;

        me.getStore().each(function (question, index) {
            question.set('order', index);
        });
    }
});
//{/block}