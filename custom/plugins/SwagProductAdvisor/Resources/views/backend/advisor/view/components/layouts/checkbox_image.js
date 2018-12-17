//

//{namespace name="backend/advisor/main"}
//{block name="backend/advisor/view/components/layouts/checkboxImage"}
Ext.define('Shopware.apps.Advisor.view.components.layouts.CheckboxImage', {
    extend: 'Shopware.apps.Advisor.view.components.layouts.AbstractLayout',
    alias: 'widget.advisor-components-layout-checkbox-image',

    snippets: {
        name: '{s name="layout_selection_option_image_multiple_selection"}Multiple choice - Picture{/s}',
        label: '{s name="layout_selection_option_image_multiple_selection"}Multiple choice - Picture{/s}',
        description: '{s name="layout_selection_option_image_multiple_selection_description"}The answers can be supplemented with pictures and arranged in a grid.{/s}'
    },

    imageUrls: {
        image: '{link file="backend/_resources/images/checkbox_image.jpg"}'
    },

    id: 'checkbox_image',
    key: 'checkbox_image',

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