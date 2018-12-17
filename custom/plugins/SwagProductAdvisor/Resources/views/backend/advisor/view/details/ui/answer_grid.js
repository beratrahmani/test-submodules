//

//{namespace name="backend/advisor/main"}
//{block name="backend/advisor/view/details/ui/answer-grid"}
Ext.define('Shopware.apps.Advisor.view.details.ui.AnswerGrid', {
    extend: 'Ext.container.Container',
    alias: 'widget.advisor-details-ui-Answer-Grid',
    layout: 'fit',

    images: {
        checked: '{link file="backend/_resources/images/checked.png"}',
        unchecked: '{link file="backend/_resources/images/unchecked.png"}',
        ddIndicatorHeader: '{link file="backend/_resources/images/grip_grid_header.png"}'
    },

    snippets: {
        columns: {
            hintDragDrop: '{s name=hint_dragdrop}You can move rows via Drag & Drop{/s}',
            value: '{s name="answers_grid_columns_value"}Value{/s}',
            answer: '{s name="answers_grid_columns_answer"}Answer{/s}',
            css: '{s name="answers_grid_columns_css"}CSS class{/s}',
            media: '{s name="answer_grid_columns_media"}Image{/s}'
        },
        answers: '{s name="answers_grid_answers"}Answers{/s}',
        addButton: '{s name="answers_grid_addButton"}Add answer{/s}',
        refreshButton: '{s name="answers_grid_refreshButton"}Refresh{/s}',
        layoutSelection: '{s name=layout_selection_title}Layout{/s}',
        removeTitle: '{s name="answer_grid_remove_titel"}Delete?{/s}',
        removeMessage: '{s name="answer_grid_remove_message"}Do you really want to delete the answer?{/s}',
        designerButton: '{s name="answer_grid_designer_button"}Fill grid{/s}',
        dragAndDrop: '{s name="drag_and_drop_order"}Drag and drop to reorder{/s}',
        clickToEdit: '{s name="click_to_edit"}Click to edit{/s}',
        groupingActive: '{s name="grouping_title_prefix_active"}Active Answers{/s}',
        groupingInactive: '{s name="grouping_title_prefix_inactive"}Inactive answers{/s}',
        grouping_title_suffix: '{s name="grouping_title_suffix"}selected{/s}',
        possibleAnswers: '{s name="attribute_filter_attribute_answer"}Possible answers{/s}',
        helpTextPossibleAnswers: '{s name="attribute_filter_attribute_answer_help_text"}If there are no possible answers, check the products in the selected Product-Stream for Attributes, Properties and supplier.{/s}'
    },

    defaultConfig: {
        mediaAllowed: false,
        designerAllowed: false,
        addAnswerAllowed: false,
        editValueAllowed: false,
        answerEditorIsNumberField: false,
        answerSelectionAllowed: false
    },

    /**
     * @overwrite
     */
    initComponent: function () {
        var me = this;

        me.items = me.createItems();

        me.callParent(arguments);

        me.reconfigureGrid(me.defaultSettings, true);
    },

    /**
     * @returns { * | [{ Ext.grid.Panel }] }
     */
    createItems: function () {
        return [
            this.createGrid()
        ];
    },

    /**
     * @returns { Ext.grid.Panel | * }
     */
    createGrid: function () {
        var me = this;

        me.store = me.question.getAnswers();

        me.grid = Ext.create('Ext.grid.Panel', {
            anchor: '100%',
            overflowY: 'auto',
            overflowX: 'hidden',
            minHeight: me.designer ? '100%' : 440,
            maxHeight: me.designer ? '100%' : 440,
            height: me.designer ? '100%' : 440,
            title: me.snippets.answers,
            dockedItems: me.createDockedItems(),
            plugins: me.createGridPlugins(),
            columns: me.createGridColumns(false, false, false),
            viewConfig: me.createDragAndDrop(),
            store: me.store
        });

        return me.grid;
    },

    /**
     * Creates the gridColumns based on the layout configuration
     *
     * @param { boolean } showMediaSelection
     * @param { boolean } editValueAllowed
     * @param { boolean } answerEditorIsNumberField
     *
     * @returns { Array|Object[] }
     */
    createGridColumns: function (showMediaSelection, editValueAllowed, answerEditorIsNumberField) {
        var me = this,
            columns = me.createDefaultColumns(editValueAllowed, answerEditorIsNumberField);

        if (showMediaSelection) {
            columns.push(me.createMediaColumn())
        }

        columns.push(me.createActionColumns(showMediaSelection));

        return columns;
    },

    /**
     * @returns { { text: string, dataIndex: string, flex: number, sortable: boolean, renderer: renderer } }
     */
    createMediaColumn: function () {
        var me = this;

        return {
            text: me.snippets.columns.media,
            dataIndex: 'thumbnail',
            flex: 1,
            hideable: false,
            sortable: false,
            menuDisabled: true,
            renderer: Ext.bind(me.mediaColumnRenderer, me)
        };
    },

    /**
     * @param { boolean } showMediaSelection
     *
     * @returns { { xtype: string, width: number, items: * } }
     */
    createActionColumns: function (showMediaSelection) {
        var me = this,
            items = me.createActionColumnItems();

        if (showMediaSelection) {
            items.push(me.createMediaSelectionColumn());
        }

        return {
            xtype: 'actioncolumn',
            width: items.length * 30,
            items: items,
            hideable: false,
            sortable: false,
            menuDisabled: true
        };
    },

    /**
     * @param { boolean } editValueAllowed
     * @param { boolean } answerEditorIsNumberField
     * @returns { * [] }
     */
    createDefaultColumns: function (editValueAllowed, answerEditorIsNumberField) {
        var me = this;

        return [
            {
                header: me.createDDIndicator(),
                width: 24,
                hideable: false,
                sortable: false,
                menuDisabled: true,
                renderer: Ext.bind(me.renderSortHandleColumn, me)
            }, {
                text: me.snippets.columns.value,
                hideable: false,
                sortable: false,
                menuDisabled: true,
                dataIndex: 'value',
                flex: 1,
                editor: editValueAllowed ? { xtype: 'numberfield', width: '90%' } : null,
                renderer: editValueAllowed ? me.defaultColumnRenderer : false,
                scope: me
            }, {
                text: me.snippets.columns.answer,
                hideable: false,
                sortable: false,
                menuDisabled: true,
                dataIndex: 'answer',
                renderer: me.defaultColumnRenderer,
                scope: me,
                flex: 2,
                editor: { xtype: answerEditorIsNumberField ? 'numberfield' : 'textfield' },
                translationEditor: {
                    xtype: 'textfield',
                    name: 'answer_value',
                    fieldLabel: me.snippets.columns.answer,
                    allowBlank: false
                }
            }, {
                text: me.snippets.columns.css,
                hideable: false,
                sortable: false,
                menuDisabled: true,
                flex: 0.5,
                editor: { xtype: 'textfield', width: '90%' },
                dataIndex: 'cssClass',
                renderer: me.defaultColumnRenderer,
                scope: me
            }
        ];
    },

    /**
     * @returns { string | * }
     */
    createDDIndicator: function () {
        var me = this;

        if (me.designer) {
            return [
                '<img src="',
                me.images.ddIndicatorHeader,
                '">'
            ].join('');
        }

        return '&#009868';
    },

    /**
     * @param { string } value
     * @returns { * }
     */
    defaultColumnRenderer: function (value) {
        var me = this;

        if (me.isNullOrEmptyString(value)) {
            return Ext.String.format('<span style="color: lightgrey;">[0]</span>', me.snippets.clickToEdit);
        }

        return value;
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

        metadata.tdAttr = Ext.String.format('data-qtip="[0]"', me.snippets.columns.hintDragDrop);

        if (me.designer) {
            return [
                '<img style="cursor: move;" src="',
                me.images.ddIndicatorHeader,
                '">'
            ].join('');
        }

        return '<div style="cursor: n-resize;">&#009868;</div>';
    },

    /**
     * @returns { *[] }
     */
    createActionColumnItems: function () {
        return [ this.createDeleteActionColumn() ];
    },

    /**
     * @returns { { iconCls: string, action: string, handler: handler } }
     */
    createDeleteActionColumn: function () {
        var me = this;

        return {
            iconCls: 'sprite-minus-circle-frame',
            action: 'delete',
            handler: function (view, rowIndex, colIndex, item, opts, record) {
                me.removeRecord(record);
            }
        };
    },

    /**
     * @returns { *[] }
     */
    createDockedItems: function () {
        var me = this,
            topBar;

        topBar = Ext.create('Ext.toolbar.Toolbar', {
            layout: 'vbox',
            dock: 'top',
            ui: 'shopware-ui',
            items: me.createTopBarItems()
        });

        return [ topBar ];
    },

    /**
     * @returns { Array }
     */
    createTopBarItems: function () {
        var me = this;

        return [
            me.createTopBarButtons(),
            me.createTopBarCombobox()
        ];
    },

    /**
     * @returns { Ext.container.Container }
     */
    createTopBarButtons: function () {
        var me = this;

        return Ext.create('Ext.container.Container', {
            width: '100%',
            items: [
                me.createAddButton(),
                me.createDesignerButton()
            ]
        });
    },

    /**
     * @returns { Ext.container.Container }
     */
    createTopBarCombobox: function () {
        var me = this;

        me.possibleAnswerSelection = Ext.create('Shopware.form.field.PagingComboBox', {
            fieldLabel: me.snippets.possibleAnswers,
            helpText: me.snippets.helpTextPossibleAnswers,
            multiSelect: true,
            labelWidth: 140,
            anchor: '100%',
            margin: '3px 10px 3px 5px',
            displayField: 'value',
            valueField: 'key',
            forceSelection: true,
            disableLoadingSelectedName: true,
            listeners: {
                scope: me,
                beforeselect: Ext.bind(me.onSelectPossibleAnswer, me),
                focus: Ext.bind(me.onFocusPossibleAnswerSelection, me),
                blur: function(field) {
                    field.clearValue();
                }
            }
        });

        return Ext.create('Ext.container.Container', {
            layout: 'anchor',
            width: '100%',
            items: [ me.possibleAnswerSelection ]
        });
    },

    /**
     * this is a hack to load the store without timing problems with the
     * configuration property of the question model
     *
     * this method get called from the "focus" event of "me.possibleAnswerSelection"
     */
    onFocusPossibleAnswerSelection: function () {
        var me = this;

        me.possibleAnswerSelection.getStore().load();
    },

    /**
     * @param { Ext.form.field.Combobox | * } combo
     * @param { * } record
     */
    onSelectPossibleAnswer: function (combo, record) {
        var me = this,
            isInStore = false;

        me.store.each(function (item) {
            if (item.get('key') == record.get('key')) {
                isInStore = true;
            }
        });

        if (!isInStore) {
            record.set('order', me.store.getCount());
            me.store.add(record);
        }

        me.possibleAnswerSelection.clearValue();
    },

    /**
     * This method is to set the possible answers to the
     * grid SearchCombo field. It is necessary to set the answers
     * in the form of a store
     *
     * @param { Ext.data.Store } answerStore
     */
    setPossibleAnswers: function (answerStore) {
        var me = this;

        me.possibleAnswerSelection.clearValue();
        me.possibleAnswerSelection.bindStore(answerStore);
    },

    /**
     * @returns { Ext.button.Button | * }
     */
    createAddButton: function () {
        var me = this;

        me.addButton = Ext.create('Ext.button.Button', {
            text: me.snippets.addButton,
            iconCls: 'sprite-plus-circle-frame',
            handler: Ext.bind(me.addButtonHandler, me)
        });

        return me.addButton;
    },

    /**
     * The addButton handler: adds a new answer
     */
    addButtonHandler: function () {
        var me = this;

        var newRecord = me.createNewRecord(),
            id = me.store.getCount();

        newRecord.set('key', id);

        me.addItem(newRecord);
    },

    /**
     * @returns { *[] }
     */
    createGridPlugins: function () {
        var me = this,
            plugins = [
                me.createCellEditingPlugin()
            ];

        if (!me.designer) {
            plugins.push(me.createGridTranslationPlugin());
        }

        return plugins;
    },

    /**
     * @returns { Ext.grid.plugin.CellEditing }
     */
    createCellEditingPlugin: function () {
        var me = this;

        return Ext.create('Ext.grid.plugin.CellEditing', {
            pluginId: me.createIndividualName('advisor_answer_grid_'),
            clicksToEdit: 1,
            listeners: {
                afteredit: Ext.bind(me.fireEditorChangeEvent, me)
            }
        });
    },

    /**
     * The "afteredit" event handler of the cellEditingPlugin.
     * Fire the "advisor_answer_grid_answer_editor_changed" event
     */
    fireEditorChangeEvent: function () {
        var me = this;

        me.fireEvent('advisor_answer_grid_answer_editor_changed', me, arguments);
    },

    /**
     * @returns { Shopware.grid.plugin.Translation }
     */
    createGridTranslationPlugin: function () {
        return Ext.create('Shopware.grid.plugin.Translation', {
            translationType: 'advisorValue'
        });
    },

    /**
     * @param { * } record
     */
    removeRecord: function (record) {
        var me = this;

        me.store.remove(record);
    },

    /**
     * clear all items in the grid.store
     */
    clearGrid: function () {
        var me = this;

        me.store.removeAll();
    },

    /**
     * @returns { Shopware.apps.Advisor.model.Answer }
     */
    createNewRecord: function () {
        return Ext.create('Shopware.apps.Advisor.model.Answer');
    },

    /**
     * @param { * } record
     */
    addItem: function (record) {
        var me = this;

        if (!record.get('id')) {
            me.grid.store.add(record);
        }

        if (me.grid.getStore().getById(record.get('id'))) {
            return;
        }

        me.grid.store.add(record);
    },

    /**
     * @returns { Ext.button.Button | * }
     */
    createDesignerButton: function () {
        var me = this;

        me.designerButton = Ext.create('Ext.button.Button', {
            text: me.snippets.designerButton,
            iconCls: 'sprite-layout-6',
            margin: '0 0 0 2',
            handler: Ext.bind(me.onClickDesignerButton, me)
        });

        return me.designerButton;
    },

    /**
     * Event listener of me.designerButton.
     * Opens the designerWindow
     */
    onClickDesignerButton: function () {
        var me = this;

        Ext.create('Shopware.apps.Advisor.view.details.Designer', {
            advisor: me.advisor,
            question: me.question,
            currentLayout: me.currentLayout,
            possibleAnswerSelectionStore: me.possibleAnswerSelection.getStore()
        }).show();
    },

    /**
     * @param { string } value
     * @returns { string }
     */
    mediaColumnRenderer: function (value) {
        var me = this;
        if (me.isNullOrEmptyString(value)) {
            return me.createEmptyImageRow();
        }

        return me.createImageRow(value);
    },

    /**
     * @param { string } value
     * @returns { string }
     */
    createImageRow: function (value) {
        return [
            '<div style="min-height: 60px;">',
            '<img src="',
            value,
            '" ',
            'style="',
            'margin: 0 auto; ',
            'display: block; ',
            'max-width: 140px; ',
            'max-height: 140px;',
            '">',
            '</div>'
        ].join('');
    },

    /**
     * @returns { string }
     */
    createEmptyImageRow: function () {
        return [
            '<div style="min-height: 60px;">',
            '</div>'
        ].join('');
    },

    /**
     * @returns { { iconCls: string, handler: handler } }
     */
    createMediaSelectionColumn: function () {
        var me = this;

        return {
            iconCls: 'sprite-inbox-upload',
            handler: function (view, rowIndex, colIndex, item, opts, record) {
                Shopware.app.Application.addSubApplication({
                    name: 'Shopware.apps.MediaManager',
                    layout: 'small',
                    eventScope: me,
                    params: {
                        albumId: me.albumId
                    },
                    mediaSelectionCallback: function (button, window, selection) {
                        me.onSelectMedia(button, window, selection, record);
                    },
                    selectionMode: false,
                    validTypes: [],
                    minimizable: false
                });
            }
        };
    },

    /**
     * @param { Ext.button.Button } button
     * @param { Ext.Window } window
     * @param { * } selection
     * @param { * } record
     *
     * @returns { boolean }
     */
    onSelectMedia: function (button, window, selection, record) {
        var me = this,
            media = selection[0];

        if (!(media instanceof Ext.data.Model)) {
            return true;
        }

        record.set('thumbnail', media.get('thumbnail'));
        record.set('mediaId', media.get('id'));

        window.close();

        me.grid.reconfigure(me.grid.getStore());

        me.fireEvent('advisor_answer_grid_media_selected', me, record);
    },

    /**
     * Every time we need to reconfigure the grid a layout, but its empty we use the
     * defaultConfig.
     * If the parameter nullStore set to true we use false as parameter
     * to reconfigure the grid. This is a workaround for a feature and plugin bug
     * in the grid.
     *
     * @param { Shopware.apps.Advisor.view.components.layouts.AbstractLayout | * } selectedLayout
     * @param { boolean= } nullStore
     */
    reconfigureGrid: function (selectedLayout, nullStore) {
        var me = this,
            store = me.store;

        nullStore = nullStore || false;

        // this is saved as property for the cssButton
        me.currentLayout = selectedLayout;

        me.setLoading(true);

        if (nullStore) {
            store = false;
        }

        if (!selectedLayout) {
            selectedLayout = me.defaultConfig;
        }

        if (me.designer) {
            me.designerButton.hide();
        } else {
            me.designerButton[selectedLayout.designerAllowed ? 'show' : 'hide']();
        }

        me.possibleAnswerSelection[selectedLayout.answerSelectionAllowed ? 'show' : 'hide']();
        me.addButton[selectedLayout.addAnswerAllowed ? 'show' : 'hide']();

        me.grid.reconfigure(
            store,
            me.createGridColumns(
                selectedLayout.mediaAllowed,
                selectedLayout.editValueAllowed,
                selectedLayout.answerEditorIsNumberField
            )
        );

        me.setLoading(false);
    },

    /**
     * @returns
     * { { plugins: { ptype: string, dragText: string }, listeners:
     *  { drop: { fn: Shopware.apps.Advisor.view.details.ui.AnswerGrid.onDrop,
     *      scope: Shopware.apps.Advisor.view.details.ui.AnswerGrid
     *  }}}}
     */
    createDragAndDrop: function () {
        var me = this;

        if (me.designer) {
            return me.createDesignerDragAndDrop();
        }

        return me.createSortingDragAndDrop();
    },

    /**
     * @returns { { plugins: {
     *              ptype: string, ddGroup: (*|string), dragGroup: (*|string), enableDrop: boolean
     *          }, style: string
     *      } }
     */
    createDesignerDragAndDrop: function () {
        var me = this;

        return {
            plugins: {
                ptype: 'gridviewdragdrop',
                ddGroup: me.createIndividualName('advisor_answer_dd_'),
                dragGroup: me.createIndividualName('advisor_answer_dd_'),
                enableDrop: false
            },
            style: "cursor:move"
        };
    },

    /**
     * @returns { { plugins: {
     *              ptype: string,
     *              dragText: string,
     *              dragGroup: (*|string),
     *              dropGroup: (*|string)
     *          }, listeners: {
     *              drop: {
     *                  fn: Shopware.apps.Advisor.view.details.ui.AnswerGrid.onDrop,
     *                  scope: Shopware.apps.Advisor.view.details.ui.AnswerGrid
     *                  }
     *              }
     *          } }
     */
    createSortingDragAndDrop: function () {
        var me = this;

        return {
            plugins: {
                ptype: 'gridviewdragdrop',
                dragText: me.snippets.dragAndDrop,
                dragGroup: me.createIndividualName('advisor_answer_dd_'),
                dropGroup: me.createIndividualName('advisor_answer_dd_')
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
     * @param { string } prefix
     * @returns { string }
     */
    createIndividualName: function (prefix) {
        var me = this;

        return [
            prefix,
            me.designer ? 'designer' : 'question'
        ].join('');
    },

    /**
     * this is the onDrop method. On Drop we set the order
     * to the current index of the store.
     */
    onDrop: function () {
        var me = this;

        // here we set the order.
        me.grid.getStore().each(function (question, index) {
            question.set('order', index);
        });
    },

    /**
     * checks if the string is NULL or empty
     *
     * @param { string } str
     * @returns { boolean }
     */
    isNullOrEmptyString: function (str) {
        return str === null || str.match(/^ *$/) !== null;
    },

    /**
     * if u overwrite this grid, it is necessary to overwrite this method on initialize it.
     *
     * for example take look into the "details/questions/price
     *
     * @param { Shopware.apps.Advisor.model.Advisor } advisor
     * @param { Shopware.apps.Advisor.model.Question } question
     * @param { Ext.data.Store } store
     */
    refreshGridData: function (advisor, question, store) {
        // do some stuff
    }
});
//{/block}