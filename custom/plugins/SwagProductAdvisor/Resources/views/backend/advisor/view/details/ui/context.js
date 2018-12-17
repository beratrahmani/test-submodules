//

//{namespace name="backend/advisor/main"}
//{block name="backend/advisor/view/details/ui/context"}
Ext.define('Shopware.apps.Advisor.view.details.ui.Context', {
    extend: 'Ext.toolbar.Toolbar',
    alias: 'widget.advisor-details-ui-context',
    cls: 'shopware-toolbar',
    dock: 'top',

    layout: {
        type: 'hbox',
        pack: 'start',
        align: 'stretch'
    },

    snippets: {
        shop: '{s name="tabs_stream_shop"}Shop{/s}',
        currency: '{s name="tabs_stream_currency"}Currency{/s}',
        customerGroup: '{s name="tabs_stream_customer_group"}Customer group{/s}',
        shopComboFieldLabel: '{s name=tabs_stream_shop}Shop{/s}',
        customerComboFieldLabel: '{s name=tabs_stream_customer_group}Customer group{/s}',
        currencyComboFieldLabel: '{s name=tabs_stream_currency}Currency{/s}'
    },

    initComponent: function () {
        var me = this;

        me.items = me.createToolbarItems();

        me.callParent(arguments);
    },

    /**
     * creates and returns the toolbar elements
     *
     * @returns { [] }
     */
    createToolbarItems: function () {
        var me = this;

        me.customerGroupStore = me.createCustomerGroupStore();
        me.currencyStore = me.createCurrencyStore();

        me.currencyCombo = me.createCurrencyCombo();
        me.customerCombo = me.createCustomerCombo();
        me.shopCombo = me.createShopCombo();

        return [
            me.shopCombo,
            me.customerCombo,
            me.currencyCombo
        ];
    },

    /**
     * @returns { Ext.form.field.ComboBox }
     */
    createShopCombo: function () {
        var me = this;

        return Ext.create('Ext.form.field.ComboBox', {
            store: Ext.create('Shopware.apps.Base.store.ShopLanguage').load(),
            flex: 1,
            displayField: 'name',
            valueField: 'id',
            name: 'shop',
            forceSelection: true,
            value: 1,
            fieldLabel: me.snippets.shopComboFieldLabel,
            labelWidth: 90
        });
    },

    /**
     * @returns { Ext.form.field.ComboBox }
     */
    createCustomerCombo: function () {
        var me = this;

        return Ext.create('Ext.form.field.ComboBox', {
            flex: 1,
            displayField: 'name',
            valueField: 'key',
            store: me.customerGroupStore,
            value: 'EK',
            name: 'customerGroup',
            fieldLabel: me.snippets.customerComboFieldLabel,
            forceSelection: true,
            labelWidth: 90
        });
    },

    /**
     * @returns { Ext.form.field.ComboBox }
     */
    createCurrencyCombo: function () {
        var me = this;

        return Ext.create('Ext.form.field.ComboBox', {
            flex: 1,
            displayField: 'currency',
            valueField: 'id',
            store: me.currencyStore,
            value: 1,
            fieldLabel: me.snippets.currencyComboFieldLabel,
            forceSelection: true,
            name: 'currency',
            labelWidth: 90
        });
    },

    /**
     * @returns { Ext.data.Store }
     */
    createCustomerGroupStore: function () {
        return Ext.create('Shopware.store.Search', {
            configure: function () {
                return { entity: 'Shopware\\Models\\Customer\\Group' }
            },
            fields: ['key', 'name']
        }).load();
    },

    /**
     * @returns { Ext.data.Store }
     */
    createCurrencyStore: function () {
        return Ext.create('Shopware.store.Search', {
            configure: function () {
                return { entity: 'Shopware\\Models\\Shop\\Currency' }
            },
            fields: ['id', 'currency']
        }).load();
    },

    /**
     * returns the current value of the shopComboBox
     *
     * @returns { * }
     */
    getShopValue: function () {
        var me = this;

        return me.shopCombo.getValue();
    },

    /**
     * returns the current value of the currencyComboBox
     *
     * @returns { * }
     */
    getCurrencyValue: function () {
        var me = this;

        return me.currencyCombo.getValue();
    },

    /**
     * returns the current value of the customerComboBox
     *
     * @returns { * }
     */
    getCustomerValue: function () {
        var me = this;

        return me.customerCombo.getValue();
    }
});
//{/block}