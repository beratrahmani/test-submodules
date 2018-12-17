// {block name="backend/emotion/view/components/live_shopping_slider"}
// {namespace name=backend/emotion/view/components/live_shopping_slider}
Ext.define('Shopware.apps.Emotion.view.components.LiveShoppingSlider', {
    extend: 'Shopware.apps.Emotion.view.components.Base',
    alias: 'widget.emotion-components-live-shopping-slider',

    /**
     * Snippets for the component.
     * @object
     */
    snippets: {
        description: '{s name=liveshopping_slider_title}Title{/s}',
        description_support_text: '{s name=liveshopping_slider_title_support_text}Enter a description{/s}',
        number_products: '{s name=liveshopping_slider_number_products}Number of products{/s}',
        show_arrows: '{s name=article_slider_arrows namespace="backend/emotion/view/components/article_slider"}Display arrows{/s}',
        scroll_speed: '{s name=article_slider_scrollspeed namespace="backend/emotion/view/components/article_slider"}Slide speed{/s}',
        rotate_automatically: '{s name=article_slider_rotation namespace="backend/emotion/view/components/article_slider"}Rotate automatically{/s}',
        rotation_speed: '{s name=article_slider_rotatespeed namespace="backend/emotion/view/components/article_slider"}Slide time interval{/s}',
        component_description: '{s name=liveshopping_slider_description}Live Shopping component for Shopping world{/s}'
    },

    /**
     * Initiliaze the component.
     *
     * @public
     * @return void
     */
    initComponent: function() {
        var me = this;

        me.callParent(arguments);
        me.setDefaultValues();
    },

    /**
     * Sets default values, if the product slider wasn't saved previously.
     *
     * @public
     * @return void
     */
    setDefaultValues: function() {
        var me = this,
            numberfields = me.query('numberfield');

        Ext.each(numberfields, function(field) {
            if (field.getName() === 'rotation_speed') {
                me.rotateSpeed = field;
            }

            if (!field.getValue()) {
                field.setValue(4000);
            }
        });
    },

    createDescriptionContainer: function() {
        var me = this, description = me.callParent(arguments);

        description.items.items[0].html = me.snippets.component_description;

        return description;
    },

    createFormElements: function() {
        var me = this, elements = me.callParent(arguments);

        Ext.each(elements, function(element) {
            if (element.name === 'description') {
                element.supportText = me.snippets.description_support_text;
            }
        });

        return elements;
    }
});
// {/block}
