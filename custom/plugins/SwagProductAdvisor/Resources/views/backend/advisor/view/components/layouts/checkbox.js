//

//{namespace name="backend/advisor/main"}
//{block name="backend/advisor/view/components/layouts/checkbox"}
Ext.define('Shopware.apps.Advisor.view.components.layouts.Checkbox', {
    extend: 'Shopware.apps.Advisor.view.components.layouts.AbstractLayout',
    alias: 'widget.advisor-components-layout-checkbox',

    snippets: {
        name: '{s name="layout_selection_option_multiple_selection"}Multiple choice - Text{/s}',
        label: '{s name="layout_selection_option_multiple_selection"}Multiple choice - Text{/s}',
        description: '{s name="layout_selection_option_multiple_selection_description"}The answers are displayed as text with checkboxes.{/s}'
    },

    imageUrls: {
        image: '{link file="backend/_resources/images/checkbox.jpg"}'
    },

    id: 'checkbox',
    key: 'checkbox',

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