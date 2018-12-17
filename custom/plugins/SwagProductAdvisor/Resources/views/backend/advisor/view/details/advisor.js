//

//{namespace name="backend/advisor/main"}
//{block name="backend/advisor/view/details/advisor"}
Ext.define('Shopware.apps.Advisor.view.details.Advisor', {
    extend: 'Shopware.model.Container',
    alias: 'widget.advisor-details-advisor',
    padding: 20,
    layout: 'anchor',
    defaults: {
        anchor: '100%'
    },

    /**
     * this is a small hack because Shopware cls "shopware-form"
     * on the FormPanel does not work
     */
    style: {
        background: '#EBEDEF'
    },

    snippets: {

        title: '{s name="tabs_title_advisor"}Productadvisor{/s}',

        fields: {
            name: '{s name="global_name"}Name{/s}',
            active: '{s name="global_active"}{/s}',
            links: '{s name="tabs_basic_links"}Links{/s}',
            description: '{s name="global_description"}{/s}',
            teaserBanner: '{s name="tabs_basic_banner"}Teaserbanner{/s}',
            teaserBannerHelpText: '{s name=teaser_help_text}The teaser-banner, which will be shown on the start-page of the advisor.{/s}'
        },

        helpText: '{s name="link_help_text"}This URL you can use in order in shopping worlds, banners or categories directly to the consultant to link.{/s}'
    },

    /**
     * @overwrite
     */
    initComponent: function () {
        var me = this;

        me.callParent(arguments);

        me.title = me.snippets.title;
    },

    /**
     * @overwrite
     *
     * This method is overwritten only to overwrite the labelWidth from 130px to 150px
     *
     * @returns { * }
     */
    createModelField: function () {
        var me = this,
            formField = me.callParent(arguments);

        formField.labelWidth = 150;

        return formField;
    },

    /**
     * @returns { { associations: string[], splitFields: boolean, fieldSets: *[] } }
     */
    configure: function () {
        var me = this;

        return {
            splitFields: false,
            fieldSets: [{
                title: '',
                fields: {
                    name: {
                        allowBlank: false,
                        fieldLabel: me.snippets.fields.name,
                        translatable: true
                    },
                    description: {
                        allowBlank: true,
                        xtype: 'tinymce',
                        fieldLabel: me.snippets.fields.description,
                        translatable: true,
                        anchor: '100%'
                    },
                    teaserBannerId: me.createMediaSelection,
                    active: {
                        allowBlank: true,
                        fieldLabel: me.snippets.fields.active
                    },
                    links: me.createLinkFormField
                }
            }]
        };
    },

    /**
     * @returns { Shopware.apps.Advisor.view.details.ui.DisplayField }
     */
    createLinkFormField: function () {
        var me = this;

        return Ext.create('Shopware.apps.Advisor.view.details.ui.DisplayField', {
            childName: 'links',
            fieldLabel: me.snippets.fields.links,
            showCopyButton: true,
            helpText: me.snippets.helpText
        });
    },

    /**
     * @returns { Shopware.form.field.Media }
     */
    createMediaSelection: function () {
        var me = this;

        return Ext.create('Shopware.form.field.Media',{
            name: 'teaserBannerId',
            allowBlank: true,
            fieldLabel: me.snippets.fields.teaserBanner,
            helpText: me.snippets.fields.teaserBannerHelpText
        });
    }
});
//{/block}