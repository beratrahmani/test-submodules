//{namespace name=backend/ticket/main}
//{block name="backend/ticket/view/settings/forms"}
Ext.define('Shopware.apps.Ticket.view.settings.Locale', {

    /**
     * The parent class that this class extends.
     * @string
     */
    extend: 'Ext.grid.Panel',

    /**
     * Set base css class prefix and module individual css class for css styling
     * @string
     */
    cls: Ext.baseCSSPrefix + 'ticket-settings-locale',

    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets.
     * @string
     */
    alias: 'widget.ticket-settings-locale',

    /**
     * Layout of the component.
     * @string
     */
    layout: 'border',
    border: 0,
    bodyBorder: 0,

    /**
     * Title of the component.
     * @string
     */
    title: '{s name=settings/locale_title}Shop specific submission(s){/s}',

    /**
     * Initialize the component
     *
     * @public
     * @return void
     */
    initComponent: function () {
        var me = this;

        me.addEvents('addLocale', 'deleteLocale');

        me.tbar = me.createActionToolbar();
        me.selModel = me.createSelModel();
        me.store = me.localeStore;
        me.columns = me.createColumns();

        me.callParent(arguments);
    },

    /**
     * Creates the action toolbar for the navigation grid
     *
     * @public
     * @return [object] Ext.toolbar.Toolbar
     */
    createActionToolbar: function () {
        var me = this;

        me.deleteButton = Ext.create('Ext.button.Button', {
            text: '{s name=settings/locale/toolbar/delete_locale}Delete submission{/s}',
            iconCls: 'sprite-minus-circle',
            disabled: true,
            handler: function (btn) {
                Ext.MessageBox.confirm('{s name=window_title}Ticket system{/s}', '{s name=settings/submission/delete_confirm}Are you sure to delete the selected shop specific submisssions?{/s}', function (button) {
                    if (button != 'yes') {
                        return false;
                    }
                    me.fireEvent('deleteLocales', btn, me);
                });

            }
        });

        return Ext.create('Ext.toolbar.Toolbar', {
            ui: 'shopware-ui',
            items: [{
                xtype: 'button',
                iconCls: 'sprite-plus-circle',
                text: '{s name=settings/locale/toolbar/create_submission}Create shop specific submission{/s}',
                handler: function (btn) {
                    me.fireEvent('addLocale', btn, me);
                }
            }, me.deleteButton]
        });
    },

    /**
     * Creates the column model for the grid.
     *
     * @public
     * @return [array] - Array of the columns
     */
    createColumns: function () {
        var me = this;
        return [{
            dataIndex: 'name',
            header: '{s name="settings/locale/columns/name"}Name{/s}',
            flex: 1,
            renderer: function (value) {
                return '<strong>' + value + '</strong>';
            }
        }, {
            xtype: 'actioncolumn',
            header: '{s name=overview/columns/actions}Action(s){/s}',
            width: 70,
            items: [{
                iconCls: 'sprite-minus-circle',
                handler: function (view, rowIdx, colIdx, item, e, record) {
                    me.fireEvent('deleteLocale', me, record);
                }
            }]
        }];
    },

    /**
     * Creates the selection model for the component.
     *
     * @public
     * @return [object] Ext.selection.CheckboxModel
     */
    createSelModel: function () {
        var me = this;
        return Ext.create('Ext.selection.CheckboxModel', {
            listeners: {
                scope: me,
                selectionchange: function (selModel, selection) {
                    me.deleteButton.setDisabled(!selection.length);
                }
            }
        });
    }
});
//{/block}
