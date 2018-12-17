
// {namespace name="backend/abo_commerce/abo_commerce/view/main"}
// {block name="backend/abo_commerce/view/settings/payment"}
Ext.define('Shopware.apps.AboCommerce.view.settings.Payment', {

    /**
     * @string
     */
    extend: 'Ext.panel.Panel',

    /**
     * Register the alias for this class.
     * @string
     */
    alias: 'widget.abo-commerce-settings-payment',

    /**
     * Body padding
     * @integer
     */
    bodyPadding: 10,

    layout: 'fit',

    /**
     * Translations
     * @object
     */
    snippets: {
        availablePaymentMeans: '{s name=payment/available_payment_means}Available payment means{/s}',
        selectedPaymentMeans: '{s name=payment/selected_payment_means}Selected payment means{/s}',

        buttonSaveSettings: '{s name=payment/button_save_settings}Save settings{/s}',

        buttonAdd: '{s name=payment/button_add}Add{/s}',
        buttonRemove: '{s name=payment/button_remove}Remove{/s}'
    },

    loadRecordIntoView: function(record) {
        var me = this;

        me.record = record;

        me.removeAll();
        me.add(me.getItems());
    },

    /**
     * Initialize the Shopware.apps.Category.view.category.tabs.restriction and defines the necessary
     * default configuration
     */
    initComponent: function () {
        var me = this;

        me.bbar = me.createToolbar();
        me.items = [];

        me.callParent(arguments);
    },

    /**
     * Creates the toolbar
     *
     * @return Object
     */
    createToolbar: function() {
        var me = this;

        return {
            xtype: 'toolbar',
            ui: 'shopware-ui',
            cls: 'shopware-toolbar',
            items: [
                '->',
                {
                    cls: 'primary',
                    name: 'save-abo-commerce-button',
                    text: me.snippets.buttonSaveSettings,
                    handler: function() {
                        me.fireEvent('saveSettings', me);
                    }
                }]
        };
    },

    /**
     * creates all fields for the tab
     */
    getItems: function () {
        var me = this;

        var paymentStore = Ext.create('Shopware.apps.Base.store.Payment');

        me.ddSelector = Ext.create('Shopware.DragAndDropSelector', {
            fromTitle: me.snippets.availablePaymentMeans,
            toTitle: me.snippets.selectedPaymentMeans,

            /**
             * default columns for the from grid
             */
            fromColumns: [{
                text: 'description',
                flex: 1,
                dataIndex: 'description'
            }],

            /**
             * default columns for the to grid
             */
            toColumns: [{
                text: 'description',
                flex: 1,
                dataIndex: 'description'
            }],

            fromStore: paymentStore,
            selectedItems: me.record.getPayments(),

            buttons: [ 'add', 'remove' ],

            buttonsText: {
                add: me.snippets.buttonAdd,
                remove: me.snippets.buttonRemove
            }
        });

        paymentStore.on('load', function() {
            var toRemove = [];
            me.record.getPayments().each(function(item) {
                var selectedItem = paymentStore.getById(item.get('id'));
                if (selectedItem instanceof Ext.data.Model) {
                    toRemove.push(selectedItem);
                }
            });

            Ext.each(toRemove, function(item) {
                paymentStore.remove(item);
            });
        });
        return [ me.ddSelector ];
    }
});
// {/block}
