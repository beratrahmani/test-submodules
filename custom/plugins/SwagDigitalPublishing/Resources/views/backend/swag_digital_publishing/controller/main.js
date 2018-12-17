// {namespace name=backend/plugins/swag_digital_publishing/main}
// {block name="backend/swag_digital_publishing/controller/main"}
Ext.define('Shopware.apps.SwagDigitalPublishing.controller.Main', {

    extend: 'Enlight.app.Controller',

    snippets: {
        deleteDialogTitle: '{s name="deleteDialogTitle"}{/s}',
        deleteDialogMessage: '{s name="deleteDialogMessage"}{/s}',
        deleteGrowlTitle: '{s name="deleteGrowlTitle"}{/s}',
        deleteGrowlMessage: '{s name="deleteGrowlMessage"}{/s}',
        saveGrowlTitle: '{s name="saveGrowlTitle"}{/s}',
        saveGrowlMessage: '{s name="saveGrowlMessage"}{/s}',
        newBannerName: '{s name="newBannerName"}{/s}'
    },

    init: function() {
        var me = this;

        me.control({
            'publishing-main-listing': {
                'addContentBanner': me.addContentBanner,
                'editContentBanner': me.editContentBanner,
                'duplicateContentBanner': me.duplicateContentBanner,
                'deleteContentBanner': me.deleteContentBanner,
                'onBannerSelect': me.onBannerSelect,
                'onSearch': me.onSearch
            },
            'publishing-editor-container': {
                'saveContentBanner': me.saveContentBanner,
                'cancelContentBanner': me.cancelContentBanner
            },
            'publishing-selection-listing': {
                'onSelectButtonClick': me.onSelectButtonClick
            }
        });

        me.bannerStore = me.getStore('ContentBanner');

        (me.subApplication.mode === 'selection') ? me.createSelectionView() : me.createMainView();

        if (me.subApplication.mode === 'edit') {
            me.initializeEditMode();
        }
        me.callParent(arguments);
    },

    /**
     * Opens a banner right away when the user opens the module over the emotion
     */
    initializeEditMode: function() {
        var me = this;

        if (!me.subApplication.bannerId) {
            return false;
        }

        var bannerModel = me.subApplication.getModel('ContentBanner');

        bannerModel.load(me.subApplication.bannerId, {
            callback: function(record) {
                if (!record) {
                    return false;
                }

                me.editContentBanner(null, record);
            }
        });
    },

    /**
     * Creates and shows the main window of the module.
     */
    createMainView: function() {
        var me = this;

        me.tabContainer = me.getView('main.Container').create({
            bannerStore: me.bannerStore
        });

        me.mainWindow = me.getView('main.Window').create({
            items: [
                me.tabContainer
            ]
        }).show();
    },

    /**
     * Creates and shows the selection window of the module.
     */
    createSelectionView: function() {
        var me = this;

        me.selectionList = me.getView('selection.Listing').create({
            store: me.bannerStore,
            multiSelect: me.subApplication.multiSelect || false,
            selectionCallback: me.subApplication.selectionCallback
        });

        me.mainWindow = me.getView('selection.Window').create({
            items: [
                me.selectionList
            ]
        }).show();

        me.bannerStore.pageSize = 12;

        me.bannerStore.load();
    },

    /**
     * Event handler for the select button of the selection view.
     * Closes the selection window and triggers the passed callback.
     *
     * @param grid
     * @param selection
     */
    onSelectButtonClick: function(grid, selection) {
        var me = this,
            scope = me.subApplication.callbackScope || me,
            callback = me.subApplication.selectionCallback;

        if (Ext.isFunction(callback)) {
            callback.call(scope, selection);

            me.mainWindow.close();
        }
    },

    /**
     * The create action to add a new content banner.
     * Opens the editor for the new banner in a new tab.
     */
    addContentBanner: function() {
        var me = this, tab,
            banner = Ext.create('Shopware.apps.SwagDigitalPublishing.model.ContentBanner');

        banner.set('name', me.snippets.newBannerName);
        banner.save({
            callback: function(record, process) {
                var response = Ext.JSON.decode(process.response.responseText);

                record.set('id', response.data.id);

                tab = Ext.create('Shopware.apps.SwagDigitalPublishing.view.editor.Container', {
                    record: record,
                    bannerStore: me.bannerStore,
                    id: 'editor-tab-' + record.get('id')
                });

                me.tabContainer.add(tab);
                me.tabContainer.setActiveTab(tab);
            }
        });
    },

    /**
     * Opens the editor for the existing banner in a new tab.
     *
     * @param grid
     * @param record
     */
    editContentBanner: function(grid, record) {
        var me = this,
            tab = null;

        if (!(record instanceof Ext.data.Model)) {
            return false;
        }

        me.tabContainer.items.each(function(item) {
            if (item.record && item.record.get('id') === record.get('id')) {
                tab = item;
            }
        });

        if (tab !== null) {
            me.tabContainer.setActiveTab(tab);
            return false;
        }

        tab = Ext.create('Shopware.apps.SwagDigitalPublishing.view.editor.Container', {
            record: record,
            bannerStore: me.bannerStore,
            id: 'editor-tab-' + record.get('id')
        });

        me.tabContainer.add(tab);
        me.tabContainer.setActiveTab(tab);
    },

    /**
     * Duplicates an existing banner.
     *
     * @param grid
     * @param bannerRecord
     */
    duplicateContentBanner: function(grid, bannerRecord) {
        var me = this;

        me.mainWindow.setLoading(true);

        Ext.Ajax.request({
            url: '{url controller="SwagContentBanner" action="duplicate"}',
            method: 'POST',
            params: {
                'bannerId': bannerRecord.get('id')
            },
            success: function() {
                me.mainWindow.setLoading(false);
                me.bannerStore.load();
            }
        });
    },

    /**
     * Deletes an existing banner.
     *
     * @param grid
     * @param record
     */
    deleteContentBanner: function(grid, record) {
        var me = this;

        Ext.MessageBox.confirm(
            me.snippets.deleteDialogTitle,
            me.snippets.deleteDialogMessage,
            function (response) {
                if (response !== 'yes') {
                    return false;
                }

                me.mainWindow.setLoading(true);

                me.tabContainer.remove(
                    me.tabContainer.getComponent('editor-tab-' + record.get('id'))
                );

                record.destroy({
                    success: function() {
                        me.mainWindow.setLoading(false);

                        Shopware.Notification.createGrowlMessage(
                            me.snippets.deleteGrowlTitle,
                            me.snippets.deleteGrowlMessage
                        );

                        grid.getStore().load();
                    }
                });
            }
        );
    },

    /**
     * Saves the current settings of a banner.
     *
     * @param editor
     * @param bannerRecord
     */
    saveContentBanner: function(editor, bannerRecord) {
        var me = this,
            scope = me.subApplication.callbackScope || me,
            callback = me.subApplication.saveCallback;

        me.mainWindow.setLoading(true);

        bannerRecord.save({
            callback: function(record) {
                editor.reloadBannerRecord(record);

                me.mainWindow.setLoading(false);

                if (Ext.isFunction(callback)) {
                    callback.call(scope, record);
                }

                Shopware.Notification.createGrowlMessage(
                    me.snippets.saveGrowlTitle,
                    me.snippets.saveGrowlMessage
                );
            }
        });
    },

    /**
     * The cancel action of the banner editor.
     * Closes the editor tab without saving changes.
     *
     * @param tab
     */
    cancelContentBanner: function(tab) {
        var me = this;

        me.tabContainer.remove(tab);
    },

    /**
     * Event handler of the search field.
     * Filters the overview by the search term.
     *
     * @param tab
     * @param grid
     * @param field
     * @param value
     */
    onSearch: function(tab, grid, field, value) {
        var store = grid.getStore();

        tab.sidebar.removeAll();
        tab.sidebar.add(tab.getHelpItems());

        value = Ext.String.trim(value);
        store.filters.clear();
        store.currentPage = 1;

        (value.length > 0) ? store.filter({ property: 'name', value: value }) : store.load();
    },

    /**
     * Event handler for selecting a banner in the overview.
     * Shows a preview of the banner and additional information in the sidebar.
     *
     * @param tab
     * @param grid
     * @param record
     */
    onBannerSelect: function(tab, grid, record) {
        tab.sidebar.setLoading(true);

        Ext.Ajax.request({
            url: '{url module="backend" controller="SwagDigitalPublishing" action="preview"}',
            method: 'POST',
            params: {
                'bannerId': record.get('id')
            },
            success: function(response) {
                tab.sidebar.removeAll();
                tab.sidebar.add(tab.getInfoItems(record));

                var previewFrame = document.getElementById('listingPreviewFrame');

                previewFrame.contentWindow.document.open();
                previewFrame.contentWindow.document.write(response.responseText);
                previewFrame.contentWindow.document.close();

                tab.sidebar.setLoading(false);
            }
        });
    }
});
// {/block}
