// {namespace name=backend/plugins/swag_digital_publishing/main}
// {block name="backend/swag_digital_publishing/view/selection/listing"}
Ext.define('Shopware.apps.SwagDigitalPublishing.view.selection.Listing', {

    extend: 'Ext.grid.Panel',

    alias: 'widget.publishing-selection-listing',

    cls: Ext.baseCSSPrefix + 'swag-publishing-selection-listing',

    layout: {
        type: 'fit'
    },

    border: false,

    snippets: {
        selectButtonLabel: '{s name="selectButtonLabel"}{/s}',
        nameColumnLabel: '{s name="nameColumnLabel"}{/s}',
        searchEmptyText: '{s name="searchEmptyText"}{/s}'
    },

    initComponent: function () {
        var me = this;

        me.columns = me.createColumns();

        me.selModel = me.createSelectionModel();

        me.dockedItems = [
            me.createToolbar()
        ];

        me.addEvents('onSelectButtonClick');

        me.callParent(arguments);
    },

    createColumns: function() {
        var me = this;

        return [{
            text: me.snippets.nameColumnLabel,
            dataIndex: 'name',
            flex: 1
        }];
    },

    createSelectionModel: function() {
        var me = this;

        return Ext.create('Ext.selection.CheckboxModel', {
            mode: (me.multiSelect) ? 'simple' : 'single',
            allowDeselect: false,
            listeners: {
                selectionchange: function() {
                    me.selectButton.setDisabled(false);
                }
            }
        });
    },

    createSearchField: function() {
        var me = this;

        me.searchField = Ext.create('Ext.form.field.Text', {
            width: 150,
            margin: '0 0 0 10',
            emptyText: me.snippets.searchEmptyText,
            enableKeyEvents: true,
            checkChangeBuffer: 500,
            listeners: {
                scope: me,
                change: function (field, value) {
                    value = Ext.String.trim(value);
                    me.store.filters.clear();
                    me.store.currentPage = 1;

                    (value.length > 0) ? me.store.filter({ property: 'name', value: value }) : me.store.load();
                }
            }
        });

        return me.searchField;
    },

    createToolbar: function() {
        var me = this;

        me.toolbar = Ext.create('Ext.toolbar.Paging', {
            store: me.store,
            dock: 'bottom',
            items: [
                me.createSearchField(),
                '->',
                me.createSelectButton()
            ]
        });

        return me.toolbar;
    },

    createSelectButton: function() {
        var me = this;

        me.selectButton = Ext.create('Ext.Button', {
            text: me.snippets.selectButtonLabel,
            cls: 'primary',
            disabled: true,
            handler: function() {
                var selection = me.selModel.getSelection();

                if (!selection.length) {
                    return false;
                }

                me.fireEvent('onSelectButtonClick', me, selection);
            }
        });

        return me.selectButton;
    }
});
// {/block}
