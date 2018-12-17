//{namespace name=backend/plugins/swag_digital_publishing/main}
//{block name="backend/swag_digital_publishing/view/main/listing"}
Ext.define('Shopware.apps.SwagDigitalPublishing.view.main.Listing', {

    extend: 'Ext.panel.Panel',

    alias: 'widget.publishing-main-listing',

    cls: Ext.baseCSSPrefix + 'swag-publishing-listing',

    layout: 'border',

    snippets: {
        overviewTitle: '{s name="overviewTitle"}{/s}',
        createButtonLabel: '{s name="createButtonLabel"}{/s}',
        nameColumnLabel: '{s name="nameColumnLabel"}{/s}',
        previewLabel: '{s name="previewLabel"}{/s}',
        informationLabel: '{s name="informationLabel"}{/s}',
        templateCodeLabel: '{s name="templateCodeLabel"}{/s}',
        searchEmptyText: '{s name="searchEmptyText"}{/s}',
        editTooltip: '{s name="editTooltip"}{/s}',
        duplicateTooltip: '{s name="duplicateTooltip"}{/s}',
        deleteTooltip: '{s name="deleteTooltip"}{/s}',
        helpTitle: '{s name="helpTitle"}{/s}',
        helpHeadline: '{s name="helpHeadline"}{/s}',
        helpIntro: '{s name="helpIntro"}{/s}',
        helpText: '{s name="helpText"}{/s}'
    },

    initComponent: function() {
        var me = this;

        me.title = me.snippets.overviewTitle;

        me.items = [
            me.createListing(),
            me.createSidebar()
        ];

        me.addEvents(
            'addContentBanner',
            'editContentBanner',
            'duplicateContentBanner',
            'deleteContentBanner',
            'onBannerSelect',
            'onSearch'
        );

        me.callParent(arguments);

        me.on('activate', me.onActivate, me);
    },

    /**
     * Creates the grid panel for the overview.
     *
     * @returns { Ext.grid.Panel|* }
     */
    createListing: function() {
        var me = this;

        me.listing = Ext.create('Ext.grid.Panel', {
            columns: me.createColumns(),
            store: me.store,
            layout: 'fit',
            region: 'center',
            flex: 1,
            dockedItems: [
                me.createToolbar(),
                me.createPagingBar()
            ],
            listeners: {
                scope: me,
                select: function(grid, record) {
                    me.fireEvent('onBannerSelect', me, grid, record);
                }
            }
        });

        return me.listing;
    },

    /**
     * Creates the panel for the info sidebar.
     *
     * @returns { Ext.panel.Panel|* }
     */
    createSidebar: function() {
        var me = this;

        me.sidebar = Ext.create('Ext.panel.Panel', {
            title: me.snippets.informationLabel,
            layout: 'anchor',
            region: 'east',
            split: false,
            collapsible: false,
            flex: 1,
            items: me.getHelpItems()
        });

        return me.sidebar;
    },

    /**
     * Returns the columns for the overview grid.
     *
     * @returns { *[] }
     */
    createColumns: function() {
        var me = this;

        return [{
            text: me.snippets.nameColumnLabel,
            dataIndex: 'name',
            flex: 1
        }, {
            xtype: 'actioncolumn',
            width: 90,
            items: [{
                iconCls: 'sprite-pencil',
                tooltip: me.snippets.editTooltip,
                handler: function (view, rowIndex, colIndex, item, opts, record) {
                    me.fireEvent('editContentBanner', me.listing, record);
                }
            }, {
                iconCls: 'sprite-document-copy',
                tooltip: me.snippets.duplicateTooltip,
                handler: function (view, rowIndex, colIndex, item, opts, record) {
                    me.fireEvent('duplicateContentBanner', me.listing, record);
                }
            }, {
                iconCls: 'sprite-minus-circle-frame',
                tooltip: me.snippets.deleteTooltip,
                handler: function (view, rowIndex, colIndex, item, opts, record) {
                    me.fireEvent('deleteContentBanner', me.listing, record);
                }
            }]
        }]
    },

    /**
     * Creates the toolbar for the overview.
     *
     * @returns { Ext.toolbar.Toolbar|* }
     */
    createToolbar: function() {
        var me = this;

        me.toolbar = Ext.create('Ext.toolbar.Toolbar', {
            ui: 'shopware-ui',
            dock: 'top',
            items: [
                me.createToolbarButtons(),
                '->',
                me.createSearchField()
            ]
        });

        return me.toolbar;
    },

    /**
     * Creates and returns the toolbar buttons.
     *
     * @returns { Ext.Button|* }
     */
    createToolbarButtons: function() {
        var me = this;

        me.createBtn = Ext.create('Ext.Button', {
            text: me.snippets.createButtonLabel,
            iconCls: 'digpub-icon-plus',
            action: 'publishing-overview-toolbar-add',
            handler: function() {
                me.fireEvent('addContentBanner');
            }
        });

        return me.createBtn;
    },

    /**
     * Creates and returns the searchfield for the toolbar.
     *
     * @returns { Ext.form.field.Text|* }
     */
    createSearchField: function() {
        var me = this;

        me.searchField = Ext.create('Ext.form.field.Text', {
            width: 170,
            emptyText: me.snippets.searchEmptyText,
            enableKeyEvents: true,
            checkChangeBuffer: 500,
            listeners: {
                scope: me,
                change: function (field, value) {
                    me.fireEvent('onSearch', me, me.listing, field, value);
                }
            }
        });

        return me.searchField;
    },

    /**
     * Creates the paging toolbar for the overview grid.
     *
     * @returns { Ext.toolbar.Paging|* }
     */
    createPagingBar: function() {
        var me = this;

        me.pagingBar = Ext.create('Ext.toolbar.Paging', {
            store: me.store,
            dock: 'bottom'
        });

        return me.pagingBar;
    },

    /**
     * Returns the contents of the help information for the info sidebar.
     *
     * @returns { *[] }
     */
    getHelpItems: function() {
        var me = this;

        return [{
            xtype: 'container',
            padding: 20,
            layout: 'fit',
            items: [{
                xtype: 'fieldset',
                title: me.snippets.helpTitle,
                html: '<h1>' + me.snippets.helpHeadline + '</h1>' +
                    '<br />' +
                    '<p>' + me.snippets.helpIntro + '</p>' +
                    '<br />' +
                    '<p>' + me.snippets.helpText + '</p>'
            }]
        }];
    },

    /**
     * Returns the items for the element infos in the info sidebar.
     *
     * @param record
     * @returns { * }
     */
    getInfoItems: function(record) {
        var me = this;

        return [{
            xtype: 'container',
            padding: 20,
            layout: 'fit',
            items: [{
                xtype: 'fieldset',
                title: me.snippets.previewLabel,
                items: [{
                    height: 340,
                    layout: 'fit',
                    border: false,
                    html: '<iframe id="listingPreviewFrame" style="background: #fff;" frameborder="0" scrolling="none" width="100%" height="100%"></iframe>'
                }]
            }, {
                xtype: 'fieldset',
                title: me.snippets.informationLabel,
                layout: 'anchor',
                items: [{
                    xtype: 'textfield',
                    name: 'widgetCall',
                    fieldLabel: me.snippets.templateCodeLabel,
                    value: '{ldelim}action module=widgets controller=SwagDigitalPublishing bannerId=' + record.get('id') + '{rdelim}',
                    anchor: '100%',
                    readOnly: true
                }]
            }]
        }]
    },

    /**
     * Event handler for activating the overview tab.
     * Reloads the store when the user switches back to the overview.
     */
    onActivate: function() {
        var me = this;

        me.listing.getStore().load();
        me.sidebar.removeAll();
        me.sidebar.add(me.getHelpItems());
    }
});
//{/block}