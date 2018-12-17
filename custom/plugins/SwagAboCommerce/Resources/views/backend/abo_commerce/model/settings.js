
// {block name="backend/abo_commerce/model/settings"}
Ext.define('Shopware.apps.AboCommerce.model.Settings', {

    /**
     * Extends the standard Ext Model
     * @string
     */
    extend: 'Ext.data.Model',

    /**
     * The fields used for this model
     * @array
     */
    fields: [
        { name: 'id', type: 'int', useNull: true },
        { name: 'name', type: 'string' },
        { name: 'shopId', type: 'int' },

        { name: 'sharingTwitter', type: 'boolean' },
        { name: 'sharingFacebook', type: 'boolean' },
        { name: 'sharingGoogle', type: 'boolean' },
        { name: 'sharingMail', type: 'boolean' },

        { name: 'sidebarHeadline', type: 'string' },
        { name: 'sidebarText', type: 'string' },

        { name: 'bannerHeadline', type: 'string' },
        { name: 'bannerSubheadline', type: 'string' },

        { name: 'allowVoucherUsage', type: 'boolean' },

        { name: 'useActualProductPrice', type: 'boolean' }
    ],

    associations: [
        {
            type: 'hasMany',
            model: 'Shopware.apps.Base.model.Payment',
            name: 'getPayments',
            associationKey: 'payments'
        }
    ],

    /**
     * Configure the data communication
     * @object
     */
    proxy: {
        type: 'ajax',

        /**
         * Configure the url mapping for the different
         * store operations based on
         * @object
         */
        api: {
            read: '{url controller="AboCommerce" action="getSettings"}',
            create: '{url controller="AboCommerce" action="saveSettings"}',
            update: '{url controller="AboCommerce" action="saveSettings"}'
        },

        /**
         * Configure the data reader
         * @object
         */
        reader: {
            type: 'json',
            root: 'data',
            totalProperty: 'total'
        }
    }
});
// {/block}
