//{namespace name=backend/plugins/b2b_debtor_plugin}
Ext.define('Shopware.apps.b2bOffer.view.list.B2bOfferFilter', {
    extend: 'Shopware.listing.FilterPanel',
    alias:  'widget.offer-listing-filter-panel',
    width: 270,

    configure: function() {
        var me = this;

        return {
            controller: 'B2bOffer',
            model: 'Shopware.apps.b2bOffer.model.B2bOffer',
            fields: {
                status: me.createComboBox()
            }
        }
    },

    createComboBox: function () {
        var me = this;

        var states = Ext.create('Ext.data.Store', {
            fields: ['database', 'status'],
            data : [
                {
                    'database': 'offer_status_open',
                    'status': '{s name=offer_status_open}Open{/s}'
                },
                {
                    'database': 'offer_status_accepted_user',
                    'status': '{s name=offer_status_accepted_user}Sent to the admin by user{/s}'
                },
                {
                    'database': 'offer_status_accepted_admin',
                    'status': '{s name=offer_status_accepted_admin}Sent to the user by admin{/s}'
                },
                {
                    'database': 'offer_status_declined_user',
                    'status': '{s name=offer_status_declined_user}Declined by user{/s}'
                },
                {
                    'database': 'offer_status_declined_admin',
                    'status': '{s name=offer_status_declined_admin}Declined by admin{/s}'
                },
                {
                    'database': 'offer_status_expired',
                    'status': '{s name=offer_status_expired}Expired{/s}'
                },
                {
                    'database': 'offer_status_accepted_both',
                    'status': '{s name=offer_status_accepted_both}Accepted by both{/s}'
                },
                {
                    'database': 'offer_status_converted',
                    'status': '{s name=offer_status_converted}Converted{/s}'
                },
            ]
        });

        return {
            fieldLabel: '{s name="Status"}Status{/s}',
                xtype: 'combobox',
                displayField: 'status',
                valueField: 'database',
                store: states
        }
    },
});