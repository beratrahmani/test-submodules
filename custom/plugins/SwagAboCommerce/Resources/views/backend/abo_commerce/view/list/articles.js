
// {namespace name="backend/abo_commerce/abo_commerce/view/main"}
// {block name="backend/abo_commerce/view/list/articles"}
Ext.define('Shopware.apps.AboCommerce.view.list.Articles', {

    extend: 'Ext.grid.Panel',

    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets.
     * @string
     */
    alias: 'widget.abo-commerce-articles',

    /**
     * Contains all snippets for this view
     * @object
     */
    snippets: {
        tooltipOpenArticle: '{s name=articles/tooltip_open_article}Open article{/s}',

        columnName: '{s name=articles/column_name}Name{/s}',
        columnNumber: '{s name=articles/column_number}Number{/s}',
        columnActive: '{s name=articles/column_active}Active{/s}',
        columnExclusive: '{s name=articles/column_exclusive}Exclusive{/s}'
    },

    /**
     * Called when the component will be initialed.
     */
    initComponent: function() {
        var me = this;

        me.registerEvents();
        me.columns = me.createColumns();
        me.bbar = me.createPagingBar();
        me.callParent(arguments);
    },

    /**
     * Registers additional component events
     */
    registerEvents: function() {
        this.addEvents(
            'search',
            'openArticle'
        );
    },

    /**
     * Creates the paging toolbar for the abo_commerce listing.
     *
     * @return Object
     */
    createPagingBar: function() {
        var me = this;

        return {
            xtype: 'pagingtoolbar',
            displayInfo: true,
            store: me.store
        };
    },

    /**
     * Creates the grid columns
     *
     * @return Array grid columns
     */
    createColumns: function () {
        var me = this, actionColumItems = [];

        actionColumItems.push({
            iconCls: 'sprite-inbox--arrow',
            tooltip: me.snippets.tooltipOpenArticle,
            handler: function(grid, rowIndex, colIndex, metaData, event, record) {
                me.fireEvent('openArticle', record.get('articleId'), me);
            }
        });

        return [{
            header: me.snippets.columnName,
            dataIndex: 'articleName',
            flex: 1,
            sortable: false
        }, {
            header: me.snippets.columnNumber,
            dataIndex: 'articleNumber',
            flex: 1,
            sortable: false
        }, {
            header: me.snippets.columnActive,
            dataIndex: 'isActive',
            xtype: 'booleancolumn',
            renderer: me.activeColumnRenderer,
            flex: 1,
            sortable: false
        }, {
            header: me.snippets.columnExclusive,
            dataIndex: 'isExclusive',
            xtype: 'booleancolumn',
            renderer: me.activeColumnRenderer,
            flex: 1,
            sortable: false
        }, {
            /**
             * Special column type which provides
             * clickable icons in each row
             */
            xtype: 'actioncolumn',
            width: actionColumItems.length * 26,
            items: actionColumItems
        }];
    },

    /**
     * @param { string } value
     */
    activeColumnRenderer: function(value) {
        if (value) {
            return '<div class="sprite-tick-small"  style="width: 25px; height: 15px">&nbsp;</div>';
        } else {
            return '<div class="sprite-cross-small" style="width: 25px; height: 15px">&nbsp;</div>';
        }
    }
});
// {/block}
