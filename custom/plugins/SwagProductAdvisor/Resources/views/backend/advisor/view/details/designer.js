//

//{namespace name="backend/advisor/main"}
//{block name="backend/advisor/view/details/designer"}
Ext.define('Shopware.apps.Advisor.view.details.Designer', {
    extend: 'Enlight.app.Window',
    alias: 'widget.details.Designer',
    bodyPadding: 20,
    width: '90%',
    height: '90%',
    modal: true,
    title: '{s name="advisor_wizard_mode_style_title"}Answers layout{/s}',
    layout: 'hbox',
    bodyBorder: true,
    bodyStyle: {
        background: '#EBEDEF',
        border: '1px solid #A4B5C0 !important'
    },

    snippets: {
        columnSelectionTitle: '{s name="designer_column_selection"}Number of Columns{/s}',
        columnsSelection: {
            col2: '{s name="designer_sel_col_2"}2 Columns{/s}',
            col3: '{s name="designer_sel_col_3"}3 Columns{/s}',
            col4: '{s name="designer_sel_col_4"}4 Columns{/s}',
            col5: '{s name="designer_sel_col_5"}5 Columns{/s}',
            col6: '{s name="designer_sel_col_6"}6 Columns{/s}'
        },
        pixelInput: '{s name="designer_px_input"}Column height in px{/s}',
        addRowButton: '{s name="designer_add_row_button"}Add row{/s}',
        removeButton: '{s name="designer_remove_row_button"}Remove row{/s}',
        okButton: '{s name="designer_ok_button"}OK{/s}',
        gridTitle: '{s name="designer_answer_grid_title"}Answers{/s}',
        columns: {
            name: '{s name="designer_grid_column_name"}Value{/s}',
            answer: '{s name="designer_grid_column_answer"}Answer{/s}'
        },
        overwriteTitle: '{s name="designer_message_title"}Overwrite?{/s}',
        overwriteMessage: '{s name="designer_message_message"}Do you want to overwrite the initial answer to this point?{/s}',
        gridDescriptionTitle: '{s name="designer_grid_description_title"}Description{/s}',
        gridDescription: '{s name="designer_grid_description"}This is the designer grid. You can drag individual answers from the grid and drop in the Designer-Grid. Add or remove answers, edit answers, add or change images.{/s}'
    },

    /**
     * This are the properties who come from outside.
     * They are passed with the Ext.create of THIS.
     */
    question: null,

    /**
     * the initial method
     */
    initComponent: function() {
        var me = this;

        me.registerEvents();

        me.leftContainer = me.createLeftContainer();
        me.rightContainer = me.createRightContainer();

        me.dockedItems = [
            me.createTBar(),
            me.createBBar()
        ];

        me.items = [
            me.leftContainer,
            me.rightContainer
        ];

        me.callParent(arguments);
    },

    /**
     * @returns { Ext.toolbar.Toolbar }
     */
    createTBar: function() {
        var me = this;

        return Ext.create('Ext.toolbar.Toolbar', {
            dock: 'top',
            ui: 'shopware-ui',
            items: [me.createTBarItem()]
        });
    },

    /**
     * @returns { Ext.container.Container }
     */
    createTBarItem: function() {
        var me = this;

        return Ext.create('Ext.container.Container', {
            layout: 'vbox',
            items: [
                me.createTBarButtons(),
                me.createTBarInputs()
            ]
        });
    },

    /**
     * @returns { Ext.container.Container }
     */
    createTBarInputs: function() {
        var me = this;

        return Ext.create('Ext.container.Container', {
            layout: 'hbox',
            items: [
                me.createColumnSelection(),
                me.createPixelInput()
            ]
        });
    },

    /**
     * @returns { Ext.container.Container }
     */
    createTBarButtons: function() {
        var me = this;

        return Ext.create('Ext.container.Container', {
            items: [
                me.createAddRowButton(),
                me.createRemoveRowButton()
            ]
        });
    },

    /**
     * @overwrite
     */
    afterRender: function() {
        var me = this;

        me.callParent(arguments);

        me.answerGrid.reconfigureGrid(me.currentLayout, true);
    },

    /**
     * register the internal Events
     */
    registerEvents: function() {
        var me = this;

        me.on('afterRender', function() {
            me.createRemoveItemButton();
        });
    },

    /**
     * @returns { Ext.container.Container }
     */
    createLeftContainer: function() {
        var me = this;

        me.leftDDContainer = Ext.create('Ext.container.Container', {
            items: me.createRaster(),
            listeners: {
                render: me.initializeDragZone,
                scope: me
            }
        });

        return Ext.create('Ext.form.FieldSet', {
            flex: 1,
            autoScroll: true,
            margin: '0 20px 0 0',
            height: '100%',
            items: [
                me.leftDDContainer
            ]
        });
    },

    /**
     * @returns { Ext.container.Container }
     */
    createRightContainer: function() {
        var me = this;

        return Ext.create('Ext.container.Container', {
            height: '100%',
            flex: 1,
            layout: 'border',
            items: [
                me.createGridDescription(),
                me.createAnswerGrid()
            ]
        });
    },

    /**
     * @returns { Ext.form.FieldSet }
     */
    createGridDescription: function() {
        var me = this;

        return Ext.create('Ext.form.FieldSet', {
            region: 'north',
            title: me.snippets.gridDescriptionTitle,
            html: [
                '<p style="font-style: italic;">',
                me.snippets.gridDescription,
                '</p>'
            ].join('')
        });
    },

    /**
     * @param { Shopware.apps.Advisor.view.details.Designer } me
     */
    resetRaster: function(me) {
        me.leftDDContainer.removeAll();
        me.leftDDContainer.add(me.createRaster());
        me.createRemoveItemButton();
    },

    /**
     * @returns { Ext.form.Number }
     */
    createPixelInput: function() {
        var me = this,
            pixelTask = new Ext.util.DelayedTask();

        me.pixelInput = Ext.create('Ext.form.field.Number', {
            fieldLabel: me.snippets.pixelInput,
            enableKeyEvents: true,
            labelWidth: 150,
            minValue: 0,
            margin: '2',
            value: me.question.get('columnHeight'),
            listeners: {
                change: Ext.bind(me.onPixelInputChange, me, [pixelTask], true)
            }
        });

        return me.pixelInput;
    },

    /**
     * This method is the eventHandler "change" of the pixelInput.
     * Here we create a little delay, so the user can type a new value by keyboard.
     */
    onPixelInputChange: function(field, oldvalue, newValue, eOpts, task) {
        var me = this;

        task.cancel();
        task.delay(500, me.onPixelTask, me, arguments);
    },

    /**
     * This method is meant to prevent that the value may become smaller than 50.
     * Also it sets the questionProperty "columnHeight".
     *
     * @param { Ext.form.field.Number } numberfield
     * @param { int | string } newValue
     */
    onPixelTask: function(numberfield, newValue) {
        var me = this;

        me.question.set('columnHeight', newValue);
        me.resetRaster(me);
    },

    /**
     * @returns { Ext.form.ComboBox }
     */
    createColumnSelection: function() {
        var me = this;

        return Ext.create('Ext.form.ComboBox', {
            store: me.createColumnSelectionStore(),
            fieldLabel: me.snippets.columnSelectionTitle,
            margin: '2',
            labelWidth: 150,
            displayField: 'name',
            valueField: 'id',
            value: me.question.get('numberOfColumns'),
            listeners: {
                'change': Ext.bind(me.onColumnSelectionChange, me)
            }
        });
    },

    /**
     * @param { Ext.form.field.Combobox } comboBox
     * @param { string } newValue
     */
    onColumnSelectionChange: function(comboBox, newValue) {
        var me = this;

        me.question.set('numberOfColumns', newValue);
        me.resetRaster(me);
    },

    /**
     * @returns { Ext.data.Store }
     */
    createColumnSelectionStore: function() {
        var me = this;

        return Ext.create('Ext.data.Store', {
            fields: [
                'id',
                'name'
            ],
            data: me.createColumnSelectionStoreData(),
            proxy: {
                type: 'memory',
                reader: {
                    type: 'json'
                }
            }
        })
    },

    /**
     * @returns { * [] }
     */
    createColumnSelectionStoreData: function() {
        var me = this;

        return [
            { id: '2', name: me.snippets.columnsSelection.col2 },
            { id: '3', name: me.snippets.columnsSelection.col3 },
            { id: '4', name: me.snippets.columnsSelection.col4 },
            { id: '5', name: me.snippets.columnsSelection.col5 },
            { id: '6', name: me.snippets.columnsSelection.col6 }
        ];
    },

    /**
     * @returns { Ext.button.Button }
     */
    createAddRowButton: function() {
        var me = this;

        return Ext.create('Ext.button.Button', {
            text: me.snippets.addRowButton,
            iconCls: 'sprite-layout-split-vertical',
            margin: '2',
            handler: Ext.bind(me.addRowButtonHandler, me)
        });
    },

    /**
     * Add row button handler
     */
    addRowButtonHandler: function() {
        var me = this;

        var newValue = me.question.get('numberOfRows') + 1;
        me.question.set('numberOfRows', newValue);
        me.resetRaster(me);
        if (newValue > 1) {
            me.removeRowButton.setDisabled(false);
        }
    },

    /**
     * @returns { Ext.button.Button | * }
     */
    createRemoveRowButton: function() {
        var me = this;

        me.removeRowButton = Ext.create('Ext.button.Button', {
            text: me.snippets.removeButton,
            iconCls: 'sprite-layout-join-vertical',
            margin: '2',
            disabled: me.question.get('numberOfRows') == 1,
            handler: Ext.bind(me.removeRowButtonHandler, me)
        });

        return me.removeRowButton;
    },

    /**
     * Remove row button handler
     */
    removeRowButtonHandler: function() {
        var me = this;

        var newValue = me.question.get('numberOfRows') - 1;
        me.question.set('numberOfRows', newValue > 0 ? newValue : 1);
        me.resetRaster(me);

        if (newValue == 1) {
            me.removeRowButton.setDisabled(true);
        }
    },

    /**
     * @returns { Ext.toolbar.Toolbar }
     */
    createBBar: function() {
        var me = this;

        me.createOkButton();

        return Ext.create('Ext.toolbar.Toolbar', {
            ui: 'shopware-ui',
            dock: 'bottom',
            cls: 'shopware-toolbar',
            items: [
                '->',
                me.okButton
            ]
        });
    },

    /**
     * @returns { Ext.Button | * }
     */
    createOkButton: function() {
        var me = this;

        me.okButton = Ext.create('Ext.Button', {
            text: me.snippets.okButton,
            cls: 'primary',
            handler: Ext.bind(me.close, me)
        });

        return me.okButton;
    },

    /**
     * @returns { Shopware.apps.Advisor.view.details.ui.AnswerGrid | * }
     */
    createAnswerGrid: function() {
        var me = this;

        me.answerGrid = Ext.create('Shopware.apps.Advisor.view.details.ui.AnswerGrid', {
            designer: true,
            advisor: me.advisor,
            question: me.question,
            region: 'center',
            selModel: me.createAnswerGridSelectionModel(),
            listeners: {
                'advisor_answer_grid_media_selected': Ext.bind(me.afterGridChanged, me),
                'advisor_answer_grid_answer_editor_changed': Ext.bind(me.afterGridChanged, me)
            }
        });

        me.answerGrid.setPossibleAnswers(me.possibleAnswerSelectionStore);

        return me.answerGrid;
    },

    /**
     * After the Grid has changed we need to reset the raster to render
     * the new settings
     */
    afterGridChanged: function() {
        var me = this;

        me.resetRaster(me)
    },

    /**
     * @returns { Ext.selection.RowModel }
     */
    createAnswerGridSelectionModel: function() {
        return Ext.create('Ext.selection.RowModel', {
            singleSelect: true
        });
    },

    /**
     * @returns { Ext.container.Container }
     */
    createRaster: function() {
        var me = this;

        return Ext.create('Ext.container.Container', {
            html: me.createRasterRows(),
            autoScroll: true,
            listeners: {
                render: me.initializeDropZone,
                scope: me
            }
        });
    },

    /**
     * @returns { string }
     */
    createRasterRows: function() {
        var me = this,
            numberOfRows = me.question.get('numberOfRows'),
            rowHtml = '';

        for (var i = 0; i < numberOfRows; i++) {
            rowHtml += '<div style="clear: both">' + me.createRasterColumns(i) + '</div>';
        }

        return rowHtml;
    },

    /**
     * @param { int } row
     * @param { int } column
     * @returns { * }
     */
    hasColumnContent: function(row, column) {
        var me = this,
            model = null;

        me.question.getAnswers().each(function(item) {
            if (item.get('rowId') == row && item.get('columnId') == column) {
                if (me.checkColumnId(row, column, item.get('targetId'))) {
                    model = item;
                }
            }
        });

        return model;
    },

    /**
     * @param { int } row
     * @param { int } column
     * @param { string } id
     * @returns { boolean }
     */
    checkColumnId: function(row, column, id) {
        var colIndex = ~~(1 * id.slice(id.indexOf('c-') + 2, id.length)),
            rowIndex = ~~(1 * id.slice(2, id.indexOf('c-')));

        return (rowIndex == row && colIndex == column);
    },

    /**
     * @param { Ext.data.Model | * } model
     */
    resetRowAndColOfTheOldContent: function(model) {
        var me = this,
            item;

        item = me.question.getAnswers().findRecord('id', model.get('id'));
        if (item) {
            item.set('rowId', null);
            item.set('columnId', null);
            item.set('targetId', null);
        }

        item = null;
        item = me.answerGrid.grid.getStore().findRecord('id', model.get('id'));
        if (item) {
            item.set('rowId', null);
            item.set('columnId', null);
            item.set('targetId', null);
        }
    },

    /**
     * initialize the Drag zone if there a elements in the Grid
     * this is needed to move elements between gridColumns
     *
     * @param { * } view
     */
    initializeDragZone: function(view) {
        var me = this;

        view.dragZone = Ext.create('Ext.dd.DragZone', view.getEl(), {
            ddGroup: 'advisor_answer_dd_designer',

            getDragData: function(e) {
                var sourceEl = e.getTarget(view.itemSelector, 10),
                    item = null,
                    d, row, col, displayElement;

                if (me.isButton) {
                    return;
                }

                if (sourceEl) {
                    col = ~~(1 * sourceEl.getAttribute('data-column'));
                    row = ~~(1 * sourceEl.getAttribute('data-row'));
                    item = me.hasColumnContent(row, col);

                    if (item) {
                        displayElement = me.getDisplayElement(sourceEl);
                        if (!displayElement) {
                            displayElement = sourceEl;
                        }

                        d = displayElement.cloneNode(true);

                        return view.dragData = {
                            sourceEl: item,
                            repairXY: Ext.fly(sourceEl).getXY(),
                            ddel: d,
                            patientData: item
                        };
                    }
                }
            },

            getRepairXY: function() {
                return this.dragData.repairXY;
            }
        });
    },

    /**
     * Tries to find the display element
     *
     * @param { HTMLElement } element
     * @returns { HTMLElement }
     */
    getDisplayElement: function(element) {
        var me = this,
            className = '.dd-display-container',
            returnElement, parent;

        if (element.classList.contains(className)) {
            return element;
        }

        returnElement = me.getChildElement(element, className);

        if (returnElement) {
            return returnElement;
        }

        parent = me.getParentElement(element, className);
        returnElement = me.getChildElement(parent, className);

        return returnElement;
    },

    /**
     * Tries to find the displayElement in childs
     *
     * @param { HTMLElement } element
     * @param { string } className
     * @returns { HTMLElement }
     */
    getChildElement: function(element, className) {
        return element.querySelector(className);
    },

    /**
     * Returns the parent displayElement with the class "ddTarget"
     *
     * @param { HTMLElement } element
     * @returns { HTMLElement }
     */ 
    getParentElement: function(element) {
        var className = 'ddTarget',
            returnElement = null;

        while (element = element.parentElement) {
            if (element.classList.contains(className)) {
                returnElement = element;
                break;
            }
        }

        return returnElement;
    },

    /**
     * @param { * } view
     */
    initializeDropZone: function(view) {
        var me = this;

        view.dropZone = Ext.create('Ext.dd.DropZone', view.el, {
            ddGroup: 'advisor_answer_dd_designer',

            getTargetFromEvent: function(e) {
                return e.getTarget('.ddTarget');
            },

            onNodeDrop: function(target, dd) {
                var col,
                    row,
                    model;

                if (me.isButton) {
                    return;
                }

                target = Ext.get(target);

                col = ~~(1 * target.getAttribute('data-column'));
                row = ~~(1 * target.getAttribute('data-row'));

                if (dd.dragData && dd.dragData.hasOwnProperty('records')) {
                    model = dd.dragData.records;
                    model = model[0];
                } else if (dd.dragData.sourceEl) {
                    model = dd.dragData.sourceEl;
                }

                if (model == null) {
                    return;
                }

                var oldModel = me.hasColumnContent(row, col);

                if (oldModel != null) {
                    if (oldModel.internalId == model.internalId) {
                        me.createTask();
                        return;
                    }
                    me.createMessage(model, oldModel, row, col, target.getAttribute('id'));
                } else {
                    me.setModelData(model, row, col, target.getAttribute('id'));
                    me.createTask();
                }
            }
        });
    },

    /**
     * @param { Ext.data.Model } model
     * @param { Ext.data.Model } oldModel
     * @param { int } row
     * @param { int } col
     * @param { int } targetId
     */
    createMessage: function(model, oldModel, row, col, targetId) {
        var me = this;

        Ext.Msg.show({
            title: me.snippets.overwriteTitle,
            msg: me.snippets.overwriteMessage,
            closable: false,
            buttons: Ext.Msg.YESNO,
            fn: function(btn) {
                if (btn == 'yes') {
                    me.resetRowAndColOfTheOldContent(oldModel);
                    me.setModelData(model, row, col, targetId);
                    me.resetRaster(me);
                } else {
                    me.resetRaster(me);
                }
            }
        });
    },

    /**
     * create a task for a little delay...
     * this is a workaround for fixing a render bug after drop on the same field
     */
    createTask: function() {
        var me = this,
            task = new Ext.util.DelayedTask(function() {
                me.resetRaster(me);
            });

        task.delay(50);
    },

    /**
     * @param { Ext.data.Model } model
     * @param { int } row
     * @param { int } col
     * @param { int } targetId
     */
    setModelData: function(model, row, col, targetId) {
        model.set('rowId', row);
        model.set('columnId', col);
        model.set('targetId', targetId);
    },

    /**
     * @param { int } row
     * @returns { string }
     */
    createRasterColumns: function(row) {
        var me = this,
            columnHtml = '',
            columnCount = me.question.get('numberOfColumns');

        for (var i = 0; i < columnCount; i++) {
            var model = me.hasColumnContent(row, i),
                colHeight = me.question.get('columnHeight'),
                id = 'id="r-' + row + 'c-' + i + '"',
                btnId = 'btn-cnt-' + 'r-' + row + 'c-' + i,
                htmlClass = 'class="ddTarget"',
                data = 'data-column="' + i + '" data-row="' + row + '"',
                border = '',
                style = 'style="height: ' + colHeight + 'px; width: ' + (100 / columnCount) +
                    '%; border: 1px dashed darkgrey; float: left; background: url({link file="backend/_resources/images/stripe.png"});"';

            if (model == null) {
                columnHtml += '<div ' + id + ' ' + htmlClass + ' ' + data + ' ' + style + '></div>';

            } else {
                border = 'border: 1px solid darkgrey;';

                var gripImage = '{link file="backend/_resources/images/grip.png"}',
                    blueBackground = '{link file="backend/_resources/images/blue.png"}',
                    content,
                    divOpen = '<div ',
                    divClose = '</div>';

                content = [
                    divOpen,
                    data,
                    'style="cursor:move; position:relative; height: ',
                    (colHeight - 2),
                    'px; border: 1px solid #bdeefc; top: -1; left: -1; ',
                    'background: url(',
                    model.get('thumbnail'),
                    '); background-repeat: no-repeat; background-position: center;">',
                    divOpen,
                    data,
                    ' style="height: 100%; background: url(',
                    blueBackground,
                    ');">',
                    divOpen,
                    data,
                    ' style="position: absolute; top: 50%; margin-top: -8px; width:100%;">',
                    divOpen,
                    data,
                    ' style="max-width:18px; float: left; overflow: hidden; width:10%;">',
                    '<img ',
                    data,
                    ' src="',
                    gripImage,
                    '" style="margin-right:3px; float: right;">',
                    divClose,
                    divOpen,
                    data,
                    ' style="width: 70%; float: left; overflow: hidden;">',
                    '<p class="dd-display-container" ',
                    data,
                    'style="margin-top:2px; color: #2775db;">',
                    model.get('answer') ? model.get('answer') : model.get('value'),
                    '</p>',
                    divClose,
                    '<div class="advisor-remove-item-button-container" id="',
                    btnId,
                    '" ',
                    data,
                    ' ',
                    'style="width: 20px; float:right; overflow: hidden;">',
                    divClose,
                    divClose,
                    divClose,
                    divClose
                ].join('');

                columnHtml += '<div ' + id + ' ' + htmlClass + ' ' + data + ' ' + style + '>' + content + '</div>';
            }

        }

        return columnHtml;
    },

    /**
     * create a little clickabel button to remove the Object
     * from the Grid.
     */
    createRemoveItemButton: function() {
        var me = this,
            className = 'advisor-remove-item-button-container',
            parent = Ext.get(document),
            elements = parent.select('div.' + className);

        elements.elements.forEach(function(item) {
            me.createClickButton(item);
        });
    },

    /**
     * @param { * } item
     */
    createClickButton: function(item) {
        var me = this,
            image = '{link file="backend/_resources/images/remove.png"}',
            elementId = item.getAttribute('id').replace('btn-cnt-', ''),
            element = me.getElement(elementId);

        Ext.create('Ext.button.Button', {
            icon: image,
            border: false,
            style: {
                background: 'none',
                margin: '0',
                padding: '0'
            },
            renderTo: item.getAttribute('id'),
            handler: function() {
                me.resetRowAndColOfTheOldContent(element);
                me.resetRaster(me);
                me.isButton = false;
            },
            listeners: {
                mouseout: Ext.bind(me.onMouseOverOutHandler, me),
                mouseover: Ext.bind(me.onMouseOverOutHandler, me)
            }
        });
    },

    /**
     * @param { Ext.button.Button | * } btn
     * @param { * } event
     */
    onMouseOverOutHandler: function(btn, event) {
        var me = this;

        if (event.type == 'mouseout') {
            me.isButton = false;
        }

        if (event.type == 'mouseover') {
            me.isButton = true;
        }
    },

    /**
     * @param { int | string | * } elementId
     * @returns { * }
     */
    getElement: function(elementId) {
        return this.answerGrid.grid.getStore().findRecord('targetId', elementId);
    }
});
//{/block}