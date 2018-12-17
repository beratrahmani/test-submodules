//

//{namespace name="backend/advisor/main"}
//{block name="backend/advisor/view/details/stream"}
Ext.define('Shopware.apps.Advisor.view.details.Stream', {
    extend: 'Shopware.model.Container',
    alias: 'widget.advisor-stream-selection',

    padding: 0,
    layout: {
        type: 'vbox',
        align: 'center'
    },

    snippets: {
        stream: '{s name="tabs_stream_stream"}Product-Stream{/s}',
        createStream: '{s name="tabs_create_stream_stream"}Create Product-Stream{/s}',
        fieldSetTitle: '{s name="tabs_stream_field_set"}Select existing Product-Stream{/s}'
    },

    /**
     * @returns { { controller: string, splitFields: boolean, fieldSets: *[] } }
     */
    configure: function() {
        var me = this;

        return {
            controller: 'Advisor',
            splitFields: false,
            fieldSets: [{
                margin: 20,
                maxHeight: 70,
                flex: 1,
                width: '100%',
                name: 'fieldSetContainer',
                title: me.snippets.fieldSetTitle,
                fields: {
                    streamId: me.createStreamSelection
                }
            }]
        }
    },

    /**
     * @returns { Shopware.form.field.ProductStreamSelection }
     */
    createStreamSelection: function() {
        var me = this;

        me.streamSelection = Ext.create('Shopware.form.field.ProductStreamSelection', {
            name: 'streamId',
            labelWidth: 150,
            anchor: '100%',
            allowBlank: false,
            fieldLabel: me.snippets.stream,
            forceSelection: true,
            listeners: {
                change: Ext.bind(me.streamSelectionChanged, me)
            },
            loadStore: function() {
                this.store.load({
                    callback: Ext.bind(me.loadStoreCallback, me)
                });
            }
        });

        return me.streamSelection;
    },

    /**
     * Loads the saved streamRecord if one was selected.
     */
    loadStoreCallback: function() {
        var me = this,
            streamId = me.record.get('streamId'),
            record, store;

        if (!streamId) {
            me.selectionStoreIsLoaded = true;
            return;
        }

        if (me.selectionStoreIsLoaded) {
            return;
        }

        me.selectionStoreIsLoaded = true;
        store = me.streamSelection.getStore();
        record = store.getById(~~(1 * streamId));

        if (!record) {
            store.load({
                params: { id: streamId },
                callback: function(item) {
                    me.streamSelection.select(item);
                }
            });
            return;
        }

        me.streamSelection.select(record);
    },

    /**
     * @overwrite
     *
     * Insert a panel with the Toolbar and the Stream-Grid with
     * the preview of the products in the selected Product-Stream
     *
     * @returns { * }
     */
    createItems: function() {
        var me = this,
            items = me.callParent(arguments);

        // create the StreamGrid witch contains the products in the selected stream
        me.streamGrid = Ext.create('Shopware.apps.Advisor.view.details.ui.StreamGrid');
        items.push(me.streamGrid);

        // create a Ext.panel.Panel to wrap the container and insert a toolbar
        items = me.createToolBarPanel(items);

        return items;
    },

    /**
     * create the create Product-Stream button
     *
     * @returns { Ext.button.Button }
     */
    createCreateStreamButton: function() {
        var me = this;

        return Ext.create('Ext.button.Button', {
            text: me.snippets.createStream,
            iconCls: 'sprite-product-streams',
            handler: function() {
                Shopware.app.Application.addSubApplication({
                    name: 'Shopware.apps.ProductStream'
                });
            }
        });
    },

    /**
     * @param { Array | * } items
     * @returns { Ext.panel.Panel }
     */
    createToolBarPanel: function(items) {
        var me = this;

        return Ext.create('Ext.panel.Panel', {
            dockedItems: me.createDockedItems(),
            items: items,
            padding: 0,
            margin: 0,
            border: false,
            layout: 'vbox',
            width: '100%',
            bodyStyle: {
                background: '#EBEDEF'
            },
            flex: 1
        });
    },

    /**
     * Create the toolbar with the create stream Button
     */
    createDockedItems: function() {
        var me = this;

        return Ext.create('Ext.toolbar.Toolbar', {
            docked: 'top',
            ui: 'shopware-ui',
            items: me.createCreateStreamButton()
        })
    },

    /**
     * @param { Ext.form.field.Combobox } combo
     * @param { string | * } newValue
     */
    streamSelectionChanged: function(combo, newValue) {
        var me = this,
            store = me.streamGrid.getStore();

        if (!newValue) {
            return;
        }

        store.getProxy().setExtraParam('streamId', newValue);
        store.currentPage = 1;
        store.load();
    }
});
//{/block}