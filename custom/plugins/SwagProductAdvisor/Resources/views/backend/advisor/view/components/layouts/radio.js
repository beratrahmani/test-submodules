//

//{namespace name="backend/advisor/main"}
//{block name="backend/advisor/view/components/layouts/Radio"}
Ext.define('Shopware.apps.Advisor.view.components.layouts.Radio', {
    extend: 'Shopware.apps.Advisor.view.components.layouts.AbstractLayout',
    alias: 'widget.advisor-layout-radio',

    snippets: {
        name: '{s name="layout_selection_option_single_selection"}Single choice - Text{/s}',
        label: '{s name="layout_selection_option_single_selection"}Single choice - Text{/s}',
        description: '{s name="layout_selection_option_single_selection_radio_description"}The answers are displayed as text with radio buttons.{/s}'
    },

    imageUrls: {
        image: '{link file="backend/_resources/images/radio.jpg"}'
    },

    id: 'radio',
    key: 'radio',

    name: null,
    label: null,
    description: null,

    image: null,

    initComponent: function () {
        var me = this;

        me.name = me.snippets.name;
        me.label = me.snippets.label;
        me.description = me.snippets.description;
        me.image = me.imageUrls.image;

        me.callParent(arguments);
    }
});
//{/block}