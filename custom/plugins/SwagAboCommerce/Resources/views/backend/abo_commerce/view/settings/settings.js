
// {namespace name="backend/abo_commerce/abo_commerce/view/main"}
// {block name="backend/abo_commerce/view/settings/settings"}
Ext.define('Shopware.apps.AboCommerce.view.settings.Settings', {

    /**
     * @string
     */
    extend: 'Ext.form.Panel',

    cls: 'shopware-form',

    layout: 'anchor',

    border: 0,

    bodyPadding: 10,

    /**
     * Register the alias for this class.
     * @string
     */
    alias: 'widget.abo-commerce-settings-settings',

    /**
     * Contains all snippets for this view
     * @object
     */
    snippets: {
        buttonSaveSettings: '{s name=settings/button_save_settings}Save settings{/s}',

        fieldsetVoucher: '{s name=settings/fieldset_voucher}Voucher setting{/s}',
        labelVoucher: '{s name=settings/label_voucher}Allow vouchers with subscriptions{/s}',

        fieldsetProductPrice: '{s name=settings/fieldset_product_price}Recurring orders{/s}',
        labelProductPrice: '{s name=settings/label_product_price}Use current product price{/s}',
        helptextProductPrice: '{s name=settings/helptext_product_price}If checked the current price from the product master data is taken, otherwise the product price from the initial order is taken.</br><b>Attention!</b></br>Please be aware that price changes during the subscriptions could result in legal problems.{/s}',

        fieldsetBanner: '{s name=settings/fieldset_banner}Banner{/s}',
        fieldsetSidebar: '{s name=settings/fieldset_sidebar}Sidebar{/s}',
        fieldSetSharing: '{s name=settings/fieldset_sharing}Sharing{/s}',

        labelSidebarHeadline: '{s name=settings/label_sidebar_headline}Headline{/s}',
        labelSidebarText: '{s name=settings/label_sidebar_text}Text{/s}',

        labelBannerHeadline: '{s name=settings/label_banner_headline}Headline{/s}',
        labelBannerSubheadline: '{s name=settings/label_banner_subheadline}Subheadline{/s}',

        labelSharingFacebook: '{s name=settings/label_sharing_facebook}Facebook{/s}',
        labelSharingTwitter: '{s name=settings/label_sharing_twitter}Twitter{/s}',
        labelSharingGoogle: '{s name=settings/label_sharing_google}Google Plus{/s}',
        labelSharingMail: '{s name=settings/label_sharing_mail}Email{/s}'
    },

    /**
     * Initialize the Shopware.apps.Category.view.category.tabs.restriction and defines the necessary
     * default configuration
     */
    initComponent: function() {
        var me = this;

        me.bbar = me.createToolbar();

        me.items = me.createItems();
        me.plugins = me.getPlugins();

        me.callParent(arguments);
    },

    getPlugins: function() {
        return [{
            pluginId: 'translation',
            ptype: 'translation',
            translationType: 'abo-commerce'
        }];
    },

    /**
     * @param { Ext.data.Model } record
     */
    loadRecordIntoView: function(record) {
        var me = this;

        me.getForm().loadRecord(record);
    },

    /**
     * @return Object
     */
    createItems: function() {
        var me = this;

        return [
            me.createProductPriceFieldset(),
            me.createVoucherFieldset(),
            me.createBannerFieldset(),
            me.createSidebarFieldset(),
            me.createSharingFieldset()
        ];
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
                }
            ]
        };
    },

    createProductPriceFieldset: function () {
        var me = this;

        return {
            xtype: 'fieldset',
            title: me.snippets.fieldsetProductPrice,
            defaults: {
                labelWidth: 230,
                labelStyle: 'font-weight: bold',
                anchor: '100%'
            },
            items: [
                {
                    xtype: 'checkboxfield',
                    name: 'useActualProductPrice',
                    inputValue: 1,
                    uncheckedValue: 0,
                    fieldLabel: me.snippets.labelProductPrice,
                    helpText: me.snippets.helptextProductPrice
                }
            ]
        };
    },

    createVoucherFieldset: function() {
        var me = this;

        return {
            xtype: 'fieldset',
            title: me.snippets.fieldsetVoucher,
            defaults: {
                labelWidth: 230,
                labelStyle: 'font-weight: bold',
                anchor: '100%'
            },
            items: [
                {
                    xtype: 'checkboxfield',
                    name: 'allowVoucherUsage',
                    inputValue: 1,
                    uncheckedValue: 0,
                    fieldLabel: me.snippets.labelVoucher
                }
            ]
        };
    },

    /**
     * @return Object
     */
    createBannerFieldset: function() {
        var me = this;

        return {
            xtype: 'fieldset',
            title: me.snippets.fieldsetBanner,

            defaults: {
                labelWidth: 230,
                labelStyle: 'font-weight: bold',
                anchor: '100%'
            },
            items: [
                {
                    xtype: 'textfield',
                    name: 'bannerHeadline',
                    fieldLabel: me.snippets.labelBannerHeadline,
                    translatable: true
                },
                {
                    xtype: 'textfield',
                    name: 'bannerSubheadline',
                    fieldLabel: me.snippets.labelBannerSubheadline,
                    translatable: true
                }
            ]
        };
    },

    /**
     * @return Object
     */
    createSharingFieldset: function() {
        var me = this;

        return {
            xtype: 'fieldset',
            title: me.snippets.fieldSetSharing,
            defaults: {
                labelWidth: 230,
                labelStyle: 'font-weight: bold',
                anchor: '100%'
            },
            items: [
                {
                    xtype: 'checkboxfield',
                    name: 'sharingFacebook',
                    inputValue: 1,
                    uncheckedValue: 0,
                    fieldLabel: me.snippets.labelSharingFacebook
                },
                {
                    xtype: 'checkboxfield',
                    name: 'sharingGoogle',
                    inputValue: 1,
                    uncheckedValue: 0,
                    fieldLabel: me.snippets.labelSharingGoogle
                },
                {
                    xtype: 'checkboxfield',
                    name: 'sharingTwitter',
                    inputValue: 1,
                    uncheckedValue: 0,
                    fieldLabel: me.snippets.labelSharingTwitter
                },
                {
                    xtype: 'checkboxfield',
                    name: 'sharingMail',
                    inputValue: 1,
                    uncheckedValue: 0,
                    fieldLabel: me.snippets.labelSharingMail
                }
            ]
        };
    },

    /**
     * @return Object
     */
    createSidebarFieldset: function() {
        var me = this;

        return {
            xtype: 'fieldset',
            title: me.snippets.fieldsetSidebar,
            defaults: {
                labelWidth: 230,
                labelStyle: 'font-weight: bold',
                anchor: '100%'
            },
            items: [
                {
                    xtype: 'textfield',
                    name: 'sidebarHeadline',
                    fieldLabel: me.snippets.labelSidebarHeadline,
                    translatable: true
                },
                {
                    xtype: 'textarea',
                    name: 'sidebarText',
                    fieldLabel: me.snippets.labelSidebarText,
                    translatable: true
                }
            ]
        };
    }
});
// {/block}
