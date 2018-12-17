//

//{namespace name="backend/advisor/main"}
//{block name="backend/advisor/view/details/result-config"}
Ext.define('Shopware.apps.Advisor.view.details.ResultConfig', {
    extend: 'Shopware.model.Container',
    alias: 'widget.advisor-details-result-config',

    /**
     * this is a small hack because Shopware cls "shopware-form"
     * on the FormPanel do not working
     */
    style: {
        background: '#EBEDEF'
    },

    snippets: {
        previewButton: '{s name="tabs_result_config_preview_button"}Open preview{/s}',

        fields: {
            listingTitleFiltered: '{s name="tabs_result_config_listingTitleFiltered"}Listingtitle: Filtered{/s}',
            remainingPostsTitle: '{s name="tabs_result_config_remainingPostTitle"}Remaining posts title{/s}',
            productLayout: '{s name="tabs_result_config_productLayout"}Listing layout{/s}',
            infoLinkText: '{s name="tabs_result_config_infoLinkText"}Information link text{/s}',
            searchButtonText: '{s name="tabs_result_config_searchButtonText"}Search button text{/s}',
            highlightTopHit: '{s name="tabs_result_config_highlightTopHit"}Highlight top hit{/s}',
            lastListingSort: '{s name="tabs_result_config_lastListingSort"}Resort{/s}',
            topHitTitle: '{s name="tabs_result_config_topHitTitle"}Top hit title{/s}',
            minMatchingAttributes: '{s name="tabs_result_config_minMatchingAttributes"}Minimum matches{/s}'
        },

        helpText: {
            listingTitleFiltered: '{s name="tabs_result_config_listingTitleFiltered_help_text"}The title for the listing with the matching products.{/s}',
            remainingPostsTitle: '{s name="tabs_result_config_remainingPostTitle_help_text"}Title for the products without matches.{/s}',
            highlightTopHit: '{s name="tabs_result_config_highlightTopHit_help_text"}The top hit product is particularly pointed.{/s}',
            topHitTitle: '{s name="tabs_result_config_topHitTitle_help_text"}The title for the top search results if it is highlighted.{/s}',
            infoLinkText: '{s name="tabs_result_config_infoLinkText_help_text"}The text displayed text for the link to further information.{/s}',
            searchButtonText: '{s name="tabs_result_config_searchButtonText_help_text"}The label for the search button.{/s}',
            minMatchingAttributes: '{s name="tabs_result_config_minMatchingAttributes_help_text"}The number of question that must be answered before you can search.{/s}',
            lastListingSort: '{s name="tabs_result_config_lastListingSort_help_text"}The last sort that is applied to the result.{/s}'
        },

        store: {
            lastListingStoreASC: '{s name="tabs_result_config_store_asc"}Lowest price first{/s}',
            lastListingStoreDESC: '{s name="tabs_result_config_store_desc"}Highest price first{/s}'
        }
    },

    id: 'result_config',

    /**
     * @overwrite
     *
     * Insert a panel with the Toolbar
     *
     * @returns { * }
     */
    createItems: function () {
        var me = this,
            items = me.callParent(arguments);

        // create a Ext.panel.Panel to wrap the container and insert a toolbar
        items = me.createToolBarPanel(items);

        return items;
    },

    /**
     * @param { Array | * } items
     * @returns { Ext.panel.Panel }
     */
    createToolBarPanel: function (items) {
        var me = this;

        return Ext.create('Ext.panel.Panel', {
            dockedItems: me.createDockedItems(),
            items: items,
            padding: 0,
            margin: 0,
            border: false,
            width: '100%',
            bodyStyle: {
                background: '#EBEDEF'
            },
            flex: 1
        });
    },

    /**
     * Create the toolbar with the preview Button
     */
    createDockedItems: function () {
        var me = this;

        return Ext.create('Ext.toolbar.Toolbar', {
            docked: 'top',
            ui: 'shopware-ui',
            items: me.createCreateStreamButton()
        })
    },

    /**
     * create the open Preview button
     *
     * @returns { Ext.button.Button }
     */
    createCreateStreamButton: function () {
        var me = this;

        me.previewButton = Ext.create('Ext.button.Button', {
            text: me.snippets.previewButton,
            iconCls: 'sprite-view-as',
            handler: Ext.bind(me.createPreview, me)
        });

        return me.previewButton;
    },

    /**
     * @overwrite
     *
     * This method is overwritten only to overwrite the labelWidth from 130px to 150px
     *
     * @returns { * }
     */
    createModelField: function () {
        var me = this,
            formField = me.callParent(arguments);

        formField.labelWidth = 150;

        return formField;
    },

    /**
     * @returns { { controller: string, fieldSets: *[] } }
     */
    configure: function () {
        var me = this;

        return {
            controller: 'Advisor',
            fieldSets: [{
                title: false,
                margin: 20,
                fields: {
                    listingTitleFiltered: {
                        allowBlank: true,
                        fieldLabel: me.snippets.fields.listingTitleFiltered,
                        helpText: me.snippets.helpText.listingTitleFiltered,
                        translatable: true
                    },
                    remainingPostsTitle: {
                        allowBlank: true,
                        fieldLabel: me.snippets.fields.remainingPostsTitle,
                        helpText: me.snippets.helpText.remainingPostsTitle,
                        translatable: true
                    },
                    listingLayout: me.createListingLayoutSelection,
                    highlightTopHit: {
                        allowBlank: true,
                        fieldLabel: me.snippets.fields.highlightTopHit,
                        helpText: me.snippets.helpText.highlightTopHit
                    },
                    topHitTitle: {
                        allowBlank: true,
                        fieldLabel: me.snippets.fields.topHitTitle,
                        helpText: me.snippets.helpText.topHitTitle,
                        translatable: true
                    },
                    infoLinkText: {
                        allowBlank: true,
                        fieldLabel: me.snippets.fields.infoLinkText,
                        helpText: me.snippets.helpText.infoLinkText,
                        translatable: true
                    },
                    buttonText: {
                        allowBlank: true,
                        fieldLabel: me.snippets.fields.searchButtonText,
                        helpText: me.snippets.helpText.searchButtonText,
                        translatable: true
                    },
                    minMatchingAttributes: {
                        fieldLabel: me.snippets.fields.minMatchingAttributes,
                        helpText: me.snippets.helpText.minMatchingAttributes
                    },
                    lastListingSort: me.createLastListingSortSelection
                }
            }]
        }
    },

    /**
     * @returns { Shopware.apps.Advisor.view.details.ui.ProductBoxLayoutSelect }
     */
    createListingLayoutSelection: function () {
        return Ext.create('Shopware.apps.Advisor.view.details.ui.ProductBoxLayoutSelect', {
            name: 'listingLayout',
            anchor: '100%',
            margin: '0 3 7 0',
            allowBlank: false,
            labelWidth: 150
        });
    },

    /**
     * this is the createPreviewAction
     */
    createPreview: function () {
        var previewWindow = Ext.create('Shopware.apps.Advisor.view.details.Preview');
        previewWindow.setAdvisor(this.record);
        previewWindow.show();
    },

    /**
     * @returns { Ext.form.field.ComboBox }
     */
    createLastListingSortSelection: function () {
        var me = this;

        return Ext.create('Ext.form.field.ComboBox', {
            labelWidth: 150,
            name: 'lastListingSort',
            anchor: '100%',
            margin: '0 3 7 0',
            fieldLabel: me.snippets.fields.lastListingSort,
            helpText: me.snippets.helpText.lastListingSort,
            allowBlank: false,
            store: me.createLastListingSortSelectionStore(),
            displayField: 'name',
            valueField: 'id',
            queryMode: 'local',
            forceSelection: true
        });
    },

    /**
     * @returns { Ext.data.Store }
     */
    createLastListingSortSelectionStore: function () {
        var me = this;

        return Ext.create('Ext.data.Store', {
            fields: ['id', 'name'],
            data: {
                data: [
                    { name: me.snippets.store.lastListingStoreASC, id: 'ASC' },
                    { name: me.snippets.store.lastListingStoreDESC, id: 'DESC' }
                ]
            },
            proxy: {
                type: 'memory',
                reader: {
                    type: 'json',
                    root: 'data'
                }
            }
        });
    }
});
//{/block}