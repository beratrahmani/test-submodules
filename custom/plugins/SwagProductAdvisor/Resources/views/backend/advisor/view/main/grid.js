//

//{namespace name="backend/advisor/main"}
//{block name="backend/advisor/view/main/grid"}
Ext.define('Shopware.apps.Advisor.view.main.Grid', {
    extend: 'Shopware.grid.Panel',
    alias: 'widget.AdvisorListingGrid',
    region: 'center',

    snippets: {
        col_title_name: '{s name="global_name"}Name{/s}',
        col_title_active: '{s name="global_active"}Active{/s}',
        col_title_mode: '{s name="listing_column_mode"}Mode{/s}',
        renderer: {
            wizard: '{s name="main_grid_renderer_wizard"}Step by step mode{/s}',
            sidebar: '{s name="main_grid_renderer_sidebar"}Sidebar mode{/s}'
        }
    },

    /**
     * @returns
     * { { columns: { name: { title: string, flex: number },
     *  mode: { title: string, flex: number }, active: string }, detailWindow: string } }
     */
    configure: function () {
        var me = this;

        return {
            columns: {
                name: {
                    title: me.snippets.col_title_name,
                    flex: 7
                },
                mode: {
                    title: me.snippets.col_title_mode,
                    flex: 3,
                    editor: false,
                    renderer: me.modeRenderer,
                    scope: me
                },
                active: me.snippets.col_title_active
            },
            detailWindow: 'Shopware.apps.Advisor.view.details.Main',
            rowEditing: true
        };
    },

    /**
     * init this component
     */
    initComponent: function () {
        var me = this;

        me.registerInlineEditEvent();

        me.callParent(arguments);
    },

    /**
     * register the "saveInlineEditValues"
     */
    registerInlineEditEvent: function () {
        var me = this;

        me.on('edit', function (editor, e) {
            me.saveInlineEditing(e.record.data.id, e.newValues.name, e.newValues.active)
        });
    },

    /**
     * @param { string } value
     * @returns { * }
     */
    modeRenderer: function (value) {
        var me = this;

        if (value == 'sidebar_mode') {
            return me.snippets.renderer.sidebar;
        }

        return me.snippets.renderer.wizard;
    },

    /**
     * @overwrite
     */
    createAddButton: function () {
        var me = this,
            button = me.callParent(arguments);

        button.handler = function () {
            me.fireEvent('listing-add-advisor');
        };

        return button;
    },

    /**
     * @overwrite
     */
    createActionColumnItems: function () {
        var me = this,
            items = me.callParent(arguments);

        items.push(me.createDuplicateButton());

        return items;
    },

    /**
     * @returns { * }
     */
    createDuplicateButton: function () {
        var me = this;

        return {
            iconCls: 'sprite-duplicate-article',
            handler: function (view, rowIndex, colIndex, item, opts, record) {
                me.fireEvent('listing-duplicate-advisor', record);
            }
        };
    },

    /**
     * @param { int } advisorId
     * @param { string } name
     * @param { boolean } active
     */
    saveInlineEditing: function (advisorId, name, active) {
        var me = this;

        me.setLoading(true);

        Ext.Ajax.request({
            url: '{url controller=Advisor action=saveDataInline}',
            params: {
                id: advisorId,
                name: name,
                active: active
            },
            success: function (response) {
                var text = Ext.decode(response.responseText);

                if (text.success) {
                    me.setLoading(false);
                    me.getStore().reload();
                }
            }
        });
    }
});
//{/block}