//

//{namespace name="backend/advisor/main"}
//{block name="backend/advisor/view/components/layouts/combobox"}
Ext.define('Shopware.apps.Advisor.view.components.layouts.Combobox', {
    extend: 'Shopware.apps.Advisor.view.components.layouts.AbstractLayout',
    alias: 'widget.advisor-layout-combobox',

    snippets: {
        name: '{s name="layout_selection_option_single_selection_drop"}Single choice - Dropdown{/s}',
        label: '{s name="layout_selection_option_single_selection_drop"}Single choice - Dropdown{/s}',
        description: '{s name="layout_selection_option_single_selection_description"}The answers are displayed as drop-down list in a button.{/s}'
    },

    imageUrls: {
        image: '{link file="backend/_resources/images/combobox.jpg"}'
    },

    id: 'combobox',
    key: 'combobox',

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