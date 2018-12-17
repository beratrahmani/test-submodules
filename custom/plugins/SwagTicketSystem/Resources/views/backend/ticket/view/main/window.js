
//{namespace name=backend/ticket/main}
//{block name="backend/ticket/view/main/window"}
Ext.define('Shopware.apps.Ticket.view.main.Window', {

    /**
     * The parent class that this class extends.
     * @string
     */
    extend:'Enlight.app.Window',

    /**
     * Set base css class prefix and module individual css class for css styling
     * @string
     */
    cls:Ext.baseCSSPrefix + 'ticket-main-window',

    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets.
     * @string
     */
    alias:'widget.ticket-main-window',

    /**
     * Set no border for the window
     * @boolean
     */
    border:false,

    /**
     * True to automatically show the component upon creation.
     * @boolean
     */
    autoShow:true,

    /**
     * Set border layout for the window
     * @string
     */
    layout:'fit',

    /**
     * Define window width
     * @integer
     */
    width:1000,

    /**
     * Define window height
     * @integer
     */
    height:'90%',

    /**
     * True to display the 'maximize' tool button and allow the user to maximize the window, false to hide the button and disallow maximizing the window.
     * @boolean
     */
    maximizable:true,

    /**
     * True to display the 'minimize' tool button and allow the user to minimize the window, false to hide the button and disallow minimizing the window.
     * @boolean
     */
    minimizable:true,
    /**
     * A flag which causes the object to attempt to restore the state of internal properties from a saved state on startup.
     */
    stateful:true,

    /**
     * The unique id for this object to use for state management purposes.
     */
    stateId:'shopware-ticket-main-window',

    /**
     * Title of the window.
     * @string
     */
    title: '{s name=window_title}Ticket system{/s}',

    /**
     * Initializes the component and the
     * main tab panel.
     *
     * @public
     * @return void
     */
    initComponent: function() {
        var me = this;

        me.items = [ me.getTabPanel() ];

        me.callParent(arguments);
    },

    /**
     * Creates the tab panel for the component.
     *
     * @public
     * @return [object] Ext.tab.Panel
     */
    getTabPanel: function() {
        var me = this;
        return me.tabPanel = Ext.create('Ext.tab.Panel', {
            plain: true,
            items: [{
                internalTitle: 'overview',
                title: '{s name=tab/title/overview}Overview{/s}',
                layout: 'border',
                items: [{
                    xtype: 'ticket-list-overview',
                    region: 'center',
                    overviewStore: me.overviewStore,
                    employeeStore: me.employeeStore,
                    employeeComboStore: me.employeeComboStore,
                    statusStore: me.statusStore,
                    statusComboStore: me.statusComboStore
                }, {
                    xtype: 'ticket-list-ticket-info',
                    region: 'south',
                    height: 200
                }]
            }
            /*{if {acl_is_allowed privilege=configure}}*/
                , {
                internalTitle: 'settings',
                title: '{s name=tab/title/settings}Settings{/s}',
                margin: '5 0 0',
                layout: 'fit',
                bodyBorder: 0,
                border: 0,
                items: [ me.getSettingsTabPanel() ]
            }
            /*{/if}*/
            ]
        });
    },

    /**
     * Creates the settings tab panel for the component.
     *
     * @public
     * @return [object] Ext.tab.Panel
     */
    getSettingsTabPanel: function() {
        var me = this;

        return me.settingsTabPanel = Ext.create('Ext.tab.Panel', {
            plain: true,
            items: [{
                xtype: 'ticket-settings-submission',
                submissionStore: me.submissionStore,
                localeStore: me.localeStore
            }, {
                xtype: 'ticket-settings-forms',
                formsStore: me.formsStore,
                typesStore: me.typesStore
            }, {
                xtype: 'ticket-settings-types',
                typesStore: me.typesStore
            }, {
                xtype: 'ticket-settings-locale',
                localeStore: me.localeStore
            }]
        });
    }
});
//{/block}
