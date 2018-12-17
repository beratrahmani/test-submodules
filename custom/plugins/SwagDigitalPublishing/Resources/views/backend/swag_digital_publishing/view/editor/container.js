// {namespace name=backend/plugins/swag_digital_publishing/editor}
// {block name="backend/swag_digital_publishing/view/editor/container"}
Ext.define('Shopware.apps.SwagDigitalPublishing.view.editor.Container', {

    extend: 'Ext.panel.Panel',

    alias: 'widget.publishing-editor-container',

    cls: Ext.baseCSSPrefix + 'swag-publishing-editor',

    layout: 'border',

    snippets: {
        newBannerTitle: '{s name="newBannerTitle"}{/s}',
        layoutPanelTitle: '{s name="layoutPanelTitle"}{/s}',
        settingsPanelTitle: '{s name="settingsPanelTitle"}{/s}',
        previewPanelTitle: '{s name="previewPanelTitle"}{/s}',
        saveButtonLabel: '{s name="saveButtonLabel"}{/s}',
        cancelButtonLabel: '{s name="cancelButtonLabel"}{/s}',
        newLayerLabel: '{s name="newLayerLabel"}{/s}',
        newElementLabel: '{s name="newElementLabel"}{/s}',
        newLayerName: '{s name="newLayerName"}{/s}',
        elementColumnLabel: '{s name="elementColumnLabel"}{/s}',
        optionsColumnLabel: '{s name="optionsColumnLabel"}{/s}',
        duplicateTooltipText: '{s name="duplicateTooltipText"}{/s}',
        deleteTooltipText: '{s name="deleteTooltipText"}{/s}',
        dragStateText: '{s name="dragStateText"}{/s}',
        testSizesLabel: '{s name="testSizesLabel"}{/s}',
        mediumRectangleTooltip: '{s name="mediumRectangleTooltip"}{/s}',
        wideSkyscraperTooltip: '{s name="wideSkyscraperTooltip"}{/s}',
        halfPageAdTooltip: '{s name="halfPageAdTooltip"}{/s}',
        superBannerTooltip: '{s name="superBannerTooltip"}{/s}',
        deleteDialogTitle: '{s name="deleteDialogTitle"}{/s}',
        deleteElementMessage: '{s name="deleteElementMessage"}{/s}',
        deleteLayerMessage: '{s name="deleteLayerMessage"}{/s}'
    },

    closable: true,

    elementHandlers: [
        Ext.create('Shopware.apps.SwagDigitalPublishing.view.editor.elements.TextElementHandler'),
        Ext.create('Shopware.apps.SwagDigitalPublishing.view.editor.elements.ButtonElementHandler'),
        Ext.create('Shopware.apps.SwagDigitalPublishing.view.editor.elements.ImageElementHandler')
    ],

    initComponent: function () {
        var me = this;

        me.title = me.record.get('name') || me.snippets.newBannerTitle;

        me.bannerModel = me.subApp.getModel('ContentBanner');

        me.updatePreview = Ext.Function.createBuffered(me.loadPreview, 600, me, me.record);

        me.bannerModel.load(me.record.get('id'), {
            callback: function(record) {
                me.record = record;

                me.treeStore = me.createTreeStore();
                me.treePanel = me.createTreePanel(me.treeStore);

                me.settingsPanel = me.createSettingsPanel();

                me.sidebar = me.createSidebar();
                me.preview = me.createPreview();
                me.toolbar = me.createToolbar();

                me.sidebar.add(me.treePanel, me.settingsPanel);

                me.add(me.sidebar, me.preview);
                me.addDocked(me.toolbar);

                me.loadPreview();
            }
        });

        me.addEvents(
            'saveContentBanner',
            'cancelContentBanner'
        );

        me.callParent(arguments);
    },

    /**
     * Creates and returns the panel for the sidebar.
     *
     * @returns { Ext.panel.Panel }
     */
    createSidebar: function() {
        var me = this;

        return Ext.create('Ext.panel.Panel', {
            title: me.snippets.layoutPanelTitle,
            iconCls: 'digpub-icon--layout',
            layout: { type: 'vbox', align: 'stretch' },
            width: 400,
            region: 'west',
            split: false,
            collapsible: true
        });
    },

    /**
     * Creates and returns the panel for the layout tree.
     *
     * @param store
     * @returns { Ext.tree.Panel }
     */
    createTreePanel: function(store) {
        var me = this,
            panel, tree;

        panel = Ext.create('Ext.panel.Panel', {
            layout: 'fit',
            flex: 1
        });

        tree = Ext.create('Ext.tree.Panel', {
            store: store,
            layout: 'fit',
            border: false,
            rootVisible: false,
            allowDeselect: false,
            displayField: 'text',
            viewConfig: me.createTreePanelViewConfig(),
            columns: me.createTreePanelColumns(),
            dockedItems: [ me.createElementsToolbar() ],
            listeners: {
                scope: me,
                select: Ext.bind(me.onTreeItemSelect, me),
                afterlayout: Ext.bind(me.selectLastSelectedElement, me)
            }
        });

        panel.add(tree);
        panel.tree = tree;

        return panel;
    },

    /**
     * @return { Object }
     */
    createTreePanelViewConfig: function() {
        var me = this;

        return {
            markDirty: false,
            plugins: {
                ptype: 'treeviewdragdrop',
                allowParentInserts: true,
                dragText: me.snippets.dragStateText
            },
            listeners: {
                scope: me,
                beforedrop: Ext.bind(me.onBeforeDrop, me),
                drop: Ext.bind(me.onDrop, me)
            }
        };
    },

    /**
     * @return { Array }
     */
    createTreePanelColumns: function() {
        var me = this;

        return [{
            xtype: 'treecolumn',
            text: me.snippets.elementColumnLabel,
            dataIndex: 'text',
            menuDisabled: true,
            sortable: false,
            flex: 2
        }, {
            xtype: 'actioncolumn',
            text: me.snippets.optionsColumnLabel,
            align: 'right',
            menuDisabled: true,
            width: 70,
            items: [{
                iconCls: 'sprite-document-copy',
                tooltip: me.snippets.duplicateTooltipText,
                getClass: function(value, meta, record) {
                    return (record.get('duplicatable')) ? '' : 'x-hide-display';
                },
                handler: function (view, rowIndex, colIndex, item, event, node) {
                    me.duplicateElement(node);
                }
            }, {
                iconCls: 'sprite-minus-circle-frame',
                tooltip: me.snippets.deleteTooltipText,
                getClass: function(value, meta, record) {
                    return (record.get('deletable')) ? '' : 'x-hide-display';
                },
                handler: function (view, rowIndex, colIndex, item, event, node) {
                    me[(node.get('type') === 'layer') ? 'deleteLayer' : 'deleteElement'](node);
                }
            }]
        }];
    },

    /**
     * Selects the last selected element
     */
    selectLastSelectedElement: function() {
        var me = this,
            lastSelected = me.treePanel.tree.getSelectionModel().getLastSelected();

        if (lastSelected) {
            me.treePanel.tree.getView().focusRow(
                lastSelected.data.index
            );
        }
    },

    /**
     * Creates and returns the store for the layout tree.
     *
     * @param bannerRecord
     * @returns { Ext.data.TreeStore }
     */
    createTreeStore: function(bannerRecord) {
        var me = this,
            record = bannerRecord || me.record;

        return Ext.create('Ext.data.TreeStore', {
            root: me.createTreeRoot(record),
            folderSort: true,
            fields: [
                'id',
                'nodeRecord',
                'type',
                'elementName',
                'text',
                'leaf',
                'children',
                'deletable',
                'duplicatable'
            ]
        });
    },

    /**
     * Creates and returns the toolbar for the layout panel.
     *
     * @returns { Ext.toolbar.Toolbar|* }
     */
    createElementsToolbar: function() {
        var me = this;

        me.layerButton = Ext.create('Ext.Button', {
            text: me.snippets.newLayerLabel,
            iconCls: 'digpub-icon--layer-plus',
            handler: Ext.bind(me.createLayer, me)
        });

        me.elementMenu = Ext.create('Ext.menu.Menu', {
            id: 'elementMenu-' + me.record.get('id'),
            items: me.createElementMenuItems()
        });

        me.elementButton = Ext.create('Ext.Button', {
            text: me.snippets.newElementLabel,
            iconCls: 'digpub-icon--element-plus',
            menu: me.elementMenu,
            disabled: true
        });

        me.elementsToolbar = Ext.create('Ext.toolbar.Toolbar', {
            ui: 'shopware-ui',
            dock: 'top',
            items: [
                me.layerButton,
                me.elementButton
            ]
        });

        return me.elementsToolbar;
    },

    /**
     * Creates and returns all menu items for the registered elements.
     *
     * @returns { Array }
     */
    createElementMenuItems: function() {
        var me = this,
            menuItems = [];

        Ext.each(me.elementHandlers, function(handler) {
            menuItems.push(
                Ext.create('Ext.menu.Item', {
                    text: handler.getLabel(),
                    iconCls: handler.getIconCls(),
                    handler: Ext.bind(me.onMenuItemClick, me, [ handler ])
                })
            );
        });

        return menuItems;
    },

    /**
     * Creates and returns the panel for the settings.
     *
     * @returns { Ext.panel.Panel }
     */
    createSettingsPanel: function() {
        var me = this;

        return Ext.create('Ext.panel.Panel', {
            title: me.snippets.settingsPanelTitle,
            iconCls: 'sprite-gear--arrow',
            layout: 'fit',
            border: false,
            flex: 2,
            overflowY: 'auto'
        });
    },

    /**
     * Creates and returns the panel for the preview.
     *
     * @returns { Ext.panel.Panel }
     */
    createPreview: function() {
        var me = this;

        me.previewContainer = Ext.create('Ext.Component', {
            width: '80%',
            height: '60%',
            x: '50%',
            y: '50%',
            border: false,
            padding: 12,
            style: {
                'transform': 'translate(-50%, -50%)',
                '-webkit-transform': 'translate(-50%, -50%)',
                'background': '#e1edfc'
            },
            resizable: {
                handles: 'all',
                minWidth: 200,
                minHeight: 100,
                dynamic: true,
                pinned: true
            },
            renderTpl: '<iframe id="previewFrame-' + me.record.get('id') + '" frameborder="0" scrolling="none" width="100%" height="100%"></iframe>'
        });

        me.previewToolbar = Ext.create('Ext.toolbar.Toolbar', {
            ui: 'shopware-ui',
            dock: 'top',
            style: {
                'border-left': '1px solid #a4b5c0',
                'border-right': '1px solid #a4b5c0'
            },
            items: [
                '->', {
                    xtype: 'tbtext',
                    text: me.snippets.testSizesLabel
                }, {
                    xtype: 'button',
                    iconCls: 'digpub-icon--ad-rectangle',
                    tooltip: me.snippets.mediumRectangleTooltip,
                    handler: Ext.bind(me.changePreviewSize, me, [ 420, 358 ])
                }, {
                    xtype: 'button',
                    iconCls: 'digpub-icon--ad-banner',
                    tooltip: me.snippets.superBannerTooltip,
                    handler: Ext.bind(me.changePreviewSize, me, [ 740, 420 ])
                }, {
                    xtype: 'button',
                    iconCls: 'digpub-icon--ad-skyscraper',
                    tooltip: me.snippets.wideSkyscraperTooltip,
                    handler: Ext.bind(me.changePreviewSize, me, [ 300, 640 ])
                }, {
                    xtype: 'button',
                    iconCls: 'digpub-icon--ad-half-page',
                    tooltip: me.snippets.halfPageAdTooltip,
                    handler: Ext.bind(me.changePreviewSize, me, [ 420, 640 ])
                }]
        });

        return Ext.create('Ext.panel.Panel', {
            title: me.snippets.previewPanelTitle,
            iconCls: 'sprite-globe--arrow',
            region: 'center',
            flex: 2,
            style: {
                'background': '#f8fafc'
            },
            items: [
                me.previewContainer
            ],
            dockedItems: [
                me.previewToolbar
            ]
        });
    },

    /**
     * Creates and returns the toolbar of the editor tab.
     *
     * @returns { Ext.toolbar.Toolbar }
     */
    createToolbar: function() {
        var me = this;

        me.saveBtn = Ext.create('Ext.Button', {
            text: me.snippets.saveButtonLabel,
            cls: 'primary',
            handler: function() {
                me.fireEvent('saveContentBanner', me, me.record);
            }
        });

        me.cancelBtn = Ext.create('Ext.Button', {
            text: me.snippets.cancelButtonLabel,
            cls: 'secondary',
            handler: function() {
                me.fireEvent('cancelContentBanner', me);
            }
        });

        return Ext.create('Ext.toolbar.Toolbar', {
            ui: 'shopware-ui',
            dock: 'bottom',
            items: [
                '->',
                me.cancelBtn,
                me.saveBtn
            ]
        });
    },

    /**
     * Loads the settings form of the banner element into the settings panel.
     */
    loadBannerSettings: function() {
        var me = this;

        var settings = Ext.create('Shopware.apps.SwagDigitalPublishing.view.editor.settings.Banner', {
            id: Ext.id(),
            record: me.record,
            editor: me
        });

        me.settingsPanel.removeAll();
        me.settingsPanel.add(settings);
    },

    /**
     * Loads the settings form of the selected layer into the settings panel.
     *
     * @param node
     */
    loadLayerSettings: function(node) {
        var me = this,
            layer = node.get('nodeRecord');

        var settings = Ext.create('Shopware.apps.SwagDigitalPublishing.view.editor.settings.Layer', {
            id: Ext.id(),
            record: layer,
            editor: me
        });

        me.settingsPanel.removeAll();
        me.settingsPanel.add(settings);
    },

    /**
     * Loads the settings form of the selected element into the settings panel.
     *
     * @param node
     */
    loadElementSettings: function(node) {
        var me = this,
            element = node.get('nodeRecord'),
            handler = me.getElementHandler(element);

        if (handler === null) {
            return;
        }

        handler.createSettings(me, element, function(formPanel) {
            me.settingsPanel.removeAll();
            me.settingsPanel.add(formPanel);
        });
    },

    /**
     * Loads the preview for the current settings into the preview panel.
     *
     * @param bannerRecord
     */
    loadPreview: function(bannerRecord) {
        var me = this,
            record = bannerRecord || me.record,
            layerStore = record['getLayersStore'],
            data = record.getData();

        data.layers = [];

        layerStore.each(function(layer) {
            var layerData = layer.getData(),
                elementStore = layer['getElementsStore'];

            layerData.elements = [];

            if (elementStore instanceof Ext.data.Store) {
                elementStore.each(function(element) {
                    var elementData = element.getData();

                    if (elementData['payload'] && elementData['payload'].length) {
                        var payload = Ext.JSON.decode(elementData['payload']) || {};
                        Ext.merge(elementData, payload);
                    }

                    layerData.elements.push(elementData);
                });
            }

            data.layers.push(layerData);
        });

        Ext.Ajax.request({
            url: '{url module="backend" controller="SwagDigitalPublishing" action="preview"}',
            method: 'POST',
            params: {
                'banner': Ext.JSON.encode(data)
            },
            success: function(response) {
                var previewFrame = document.getElementById('previewFrame-' + me.record.get('id'));

                previewFrame.contentWindow.document.open();
                previewFrame.contentWindow.document.write(response.responseText);
                previewFrame.contentWindow.document.close();
            }
        });
    },

    /**
     * Changes the size of the preview container to the given width and height.
     *
     * @param width
     * @param height
     */
    changePreviewSize: function(width, height) {
        var me = this;

        me.previewContainer.setSize(width, height);
    },

    /**
     * Event handler for the element menu items.
     * Creates a new element by selected type.
     *
     * @param handler
     */
    onMenuItemClick: function(handler) {
        var me = this,
            selMod = me.treePanel.tree.getSelectionModel(),
            selection = selMod.getSelection();

        if (!selection.length || selection[0].get('type') !== 'layer') {
            return false;
        }

        me.createElement(selection[0].get('nodeRecord'), handler);
    },

    /**
     * Event handler for selecting a node in the layout tree.
     * Loads the correct settings by node type.
     *
     * @param tree
     * @param node
     */
    onTreeItemSelect: function(tree, node) {
        var me = this,
            type = node.get('type');

        me.elementButton.setDisabled(type !== 'layer');

        if (type === 'layer') {
            me.loadLayerSettings(node);
        } else if (type === 'element') {
            me.loadElementSettings(node);
        } else {
            me.loadBannerSettings();
        }
    },

    /**
     * Event handler for dragging elements in the layout tree.
     * Returns if the current drop position is valid or not.
     *
     * @param nodeView
     * @param dropData
     * @param dropNode
     * @param dropType
     */
    onBeforeDrop: function(nodeView, dropData, dropNode, dropType) {
        var dragNode = dropData.records[0],
            dragNodeType = dragNode.get('type'),
            dropNodeType = dropNode.get('type');

        if (dropType === 'append' &&
            dragNodeType === 'element' &&
            dropNodeType === 'layer') {
            return true;
        }

        if ((dropType === 'before' || dropType === 'after') && dragNodeType === dropNodeType) {
            return true;
        }

        return false;
    },

    /**
     * Event handler for the drop event of a node in the layout tree.
     */
    onDrop: function() {
        this.updatePositions();
    },

    /**
     * Creates the root node of the store for the layout tree.
     *
     * @param bannerRecord
     */
    createTreeRoot: function(bannerRecord) {
        var me = this,
            record = bannerRecord || me.record;

        return {
            expanded: true,
            allowDrag: false,
            allowDrop: false,
            children: me.createTreeRecords(record)
        };
    },

    /**
     * Creates the layer and element nodes for the store of the layout tree.
     *
     * @param bannerRecord
     */
    createTreeRecords: function(bannerRecord) {
        var me = this,
            record = bannerRecord || me.record,
            layerStore = record['getLayersStore'],
            layers = [],
            data = [];

        layerStore.each(function(layer) {
            var elements = [],
                elementStore = layer['getElementsStore'];

            if (elementStore instanceof Ext.data.Store) {
                elementStore.each(function(element) {
                    elements.push(me.createElementNode(element));
                });
            }

            layers.push(me.createLayerNode(layer, elements));
        });

        data.push(me.createBannerNode(record, layers));

        return data;
    },

    /**
     * Creates the banner node for the store of the layout tree.
     *
     * @param record
     * @param children
     */
    createBannerNode: function(record, children) {
        return {
            id: 'contentBanner' + (record.getId() || Ext.id()),
            nodeRecord: record,
            type: 'contentBanner',
            iconCls: 'digpub-icon',
            text: record.get('name'),
            leaf: (children.length == 0),
            children: children || [],
            expandable: false,
            expanded: true,
            deletable: false,
            duplicatable: false,
            allowDrag: false,
            allowDrop: true
        };
    },

    /**
     * Creates a layer node for the store of the layout tree.
     *
     * @param record
     * @param children
     */
    createLayerNode: function(record, children) {
        return {
            id: 'layer' + (record.getId() || Ext.id()),
            nodeRecord: record,
            type: 'layer',
            iconCls: 'digpub-icon--layer',
            text: record.get('label'),
            leaf: false,
            children: children || [],
            expanded: true,
            deletable: true,
            duplicatable: false,
            allowDrag: true,
            allowDrop: true
        };
    },

    /**
     * Creates an element node for the store of the layout tree.
     *
     * @param record
     */
    createElementNode: function(record) {
        var me = this,
            handler = me.getElementHandler(record);

        return {
            id: 'element' + (record.getId() || Ext.id()),
            nodeRecord: record,
            type: 'element',
            elementName: handler.getName(),
            iconCls: handler.getIconCls(),
            text: record.get('label'),
            leaf: true,
            expanded: true,
            deletable: true,
            duplicatable: true,
            allowDrag: true,
            allowDrop: false
        };
    },

    /**
     * Create action to add a new layer to the layout tree.
     */
    createLayer: function() {
        var me = this,
            layerStore = me.record['getLayersStore'],
            position = layerStore.getCount(),
            newLayer;

        me.treePanel.setLoading(true);

        newLayer = Ext.create('Shopware.apps.SwagDigitalPublishing.model.Layer', {
            label: me.snippets.newLayerName,
            position: position
        });

        layerStore.add(newLayer);

        me.rebuild();
        me.updatePreview();
        me.treePanel.setLoading(false);
    },

    /**
     * Create action to add a new element to the layout tree.
     *
     * @param layer
     * @param handler
     */
    createElement: function(layer, handler) {
        var me = this,
            elementStore = layer['getElementsStore'],
            position = elementStore.getCount(),
            newElement;

        me.treePanel.setLoading(true);

        newElement = Ext.create('Shopware.apps.SwagDigitalPublishing.model.Element', {
            layerID: layer.get('id'),
            name: handler.getName(),
            label: handler.getLabel(),
            position: position
        });

        elementStore.add(newElement);

        me.rebuild();
        me.updatePreview();
        me.treePanel.setLoading(false);
    },

    /**
     * Delete action to remove a layer from the layout tree.
     *
     * @param node
     */
    deleteLayer: function(node) {
        var me = this,
            layerRecord = node.get('nodeRecord'),
            layerStore = me.record['getLayersStore'];

        Ext.MessageBox.confirm(
            me.snippets.deleteDialogTitle,
            me.snippets.deleteLayerMessage,
            function (response) {
                if (response !== 'yes') {
                    return false;
                }

                me.treePanel.setLoading(true);

                layerStore.remove(layerRecord);

                me.rebuild();
                me.updatePreview();
                me.treePanel.setLoading(false);
            }
        );
    },

    /**
     * Delete action to remove a element from the layout tree.
     *
     * @param node
     */
    deleteElement: function(node) {
        var me = this,
            elementRecord = node.get('nodeRecord'),
            layerStore = me.record['getLayersStore'],
            layer = layerStore.getById(elementRecord.get('layerID')),
            elementStore = layer['getElementsStore'];

        Ext.MessageBox.confirm(
            me.snippets.deleteDialogTitle,
            me.snippets.deleteElementMessage,
            function (response) {
                if (response !== 'yes') {
                    return false;
                }

                me.treePanel.setLoading(true);

                elementStore.remove(elementRecord);

                me.rebuild();
                me.updatePreview();
                me.treePanel.setLoading(false);
            }
        );
    },

    /**
     * Duplicate action to copy an element in the layout tree.
     *
     * @param node
     */
    duplicateElement: function(node) {
        var me = this,
            elementRecord = node.get('nodeRecord'),
            layer = me.getLayerByElementInternalId(elementRecord.internalId),
            elementStore = layer['getElementsStore'],
            newElement = elementRecord.copy();

        me.treePanel.setLoading(true);

        newElement.set('id', null);
        newElement.set('position', elementRecord.get('position'));

        elementStore.add(newElement);

        me.rebuild();
        me.updatePreview();
        me.treePanel.setLoading(false);
    },

    /**
     * @param { integer } internalId
     * @return { Shopware.apps.SwagDigitalPublishing.model.Layer }
     */
    getLayerByElementInternalId: function(internalId) {
        var me = this,
            layerStore = me.record['getLayersStore'],
            returnLayer;

        layerStore.each(function(layer) {
            layer.getElements().each(function(element) {
                if (element.internalId == internalId) {
                    returnLayer = layer;
                    return false;
                }
            });

            if (returnLayer) {
                return false;
            }
        });

        return returnLayer;
    },

    /**
     * Updates the position attributes of all elements and layers
     * by the positions of the corresponding tree nodes.
     */
    updatePositions: function() {
        var me = this,
            bannerNode = me.treeStore.getRootNode().getChildAt(0);

        Ext.each(bannerNode.childNodes, function(layerNode, layerIndex) {
            var layerRecord = layerNode.get('nodeRecord'),
                layerElements = layerRecord['getElementsStore'];

            layerRecord.set('position', layerIndex);
            layerElements.removeAll();

            Ext.each(layerNode.childNodes, function(elementNode, elementIndex) {
                var elementRecord = elementNode.get('nodeRecord');

                elementRecord.set('position', elementIndex);
                elementRecord.set('layerID', layerRecord.get('id'));
                layerElements.add(elementRecord);
            });
        });

        me.updatePreview();
    },

    /**
     * Rebuilds the editor view.
     *
     * @param bannerRecord
     */
    rebuild: function(bannerRecord) {
        var me = this,
            record = bannerRecord || me.record;

        me.reloadTree(record);
    },

    /**
     * Reloads the banner record.
     *
     * @param bannerRecord
     */
    reloadBannerRecord: function(bannerRecord) {
        var me = this,
            record = bannerRecord || me.record;

        me.bannerModel.load(record.get('id'), {
            callback: function (record) {
                me.record = record;
                me.rebuild();
                me.updatePreview();
            }
        });
    },

    /**
     * Rebuilds the store for the layout tree.
     *
     * @param bannerRecord
     */
    reloadTree: function(bannerRecord) {
        var me = this,
            record = bannerRecord || me.record,
            selectionModel = me.treePanel.tree.getSelectionModel(),
            currentSelection = selectionModel.getSelection();

        me.treeStore.setRootNode(me.createTreeRoot(record), true);

        if (currentSelection.length) {
            if (currentSelection[0]) {
                var rec = me.treePanel.tree.getStore().getNodeById(currentSelection[0].get('id'));
                selectionModel.select(rec);
            }
        }
    },

    /**
     * Updates a record from form data.
     *
     * @param formPanel
     * @param record
     */
    updateRecord: function(formPanel, record) {
        var me = this,
            form = formPanel.getForm();

        form.updateRecord(record);

        me.updateNodeText(record);
        me.updatePreview();
    },

    /**
     * Updates the text of a node of the layout tree.
     *
     * @param record
     */
    updateNodeText: function(record) {
        var me = this,
            selMod = me.treePanel.tree.getSelectionModel(),
            selection = selMod.getSelection(),
            node = selection[0],
            text = record.get('name') || record.get('label');

        node.set('text', text);
    },

    /**
     * Returns the corresponding handler for an element.
     *
     * @param element
     * @returns { * }
     */
    getElementHandler: function(element) {
        var me = this,
            elementHandler = null;

        Ext.each(me.elementHandlers, function(handler) {
            if (handler.getName() === element.get('name')) {
                elementHandler = handler;
                return false;
            }
        });

        return elementHandler;
    },

    /**
     * Returns the corresponding element handler by name.
     *
     * @param name
     * @returns { * }
     */
    getElementHandlerByName: function(name) {
        var me = this,
            elementHandler = null;

        Ext.each(me.elementHandlers, function(handler) {
            if (handler.getName() === name) {
                elementHandler = handler;
                return false;
            }
        });

        return elementHandler;
    }
});
// {/block}
