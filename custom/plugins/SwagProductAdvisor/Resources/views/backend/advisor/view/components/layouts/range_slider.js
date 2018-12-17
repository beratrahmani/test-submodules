//

//{namespace name="backend/advisor/main"}
//{block name="backend/advisor/view/components/layouts/rangeSlider"}
Ext.define('Shopware.apps.Advisor.view.components.layouts.RangeSlider', {
    extend: 'Shopware.apps.Advisor.view.components.layouts.AbstractLayout',
    alias: 'widget.advisor-components-layout-range-slider',

    snippets: {
        name: '{s name="layout_selection_option_range_price"}Price slider{/s}',
        label: '{s name="layout_selection_option_range_price"}Price slider{/s}',
        description: '{s name="layout_selection_option_range_price_description"}The customer can limit the price by means of a slider. You yourself can define the lowest and the highest price which the customer can select a maximum.{/s}'
    },

    imageUrls: {
        image: '{link file="backend/_resources/images/slider.jpg"}'
    },

    id: 'range_slider',
    key: 'range_slider',

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