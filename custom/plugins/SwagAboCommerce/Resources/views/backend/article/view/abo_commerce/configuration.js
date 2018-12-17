
// {namespace name="backend/abo_commerce/article/view/main"}
// {block name="backend/article/view/abo_commerce/configuration"}
Ext.define('Shopware.apps.Article.view.abo_commerce.Configuration', {

    /**
     * The parent class that this class extends.
     */
    extend: 'Ext.form.Panel',

    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets
     */
    alias: 'widget.abo-commerce-configuration',

    cls: 'shopware-form',

    layout: 'anchor',

    /**
     * Specifies the border size for this component. The border can be a single numeric value to apply to all
     * sides or it can be a CSS style specification for each style, for example: '10 5 3 10' (top, right, bottom, left).
     * For components that have no border by default, setting this won't make the border appear by itself.
     */
    border: false,

    /**
     * A shortcut for setting a padding style on the body element. The value can either be
     * a number to be applied to all sides, or a normal css string describing padding. Defaults to undefined.
     */
    bodyPadding: 10,

    /**
     * Contains all snippets for this view
     * @object
     */
    snippets: {
        title: '{s name=configuration/title}Enable subscription{/s}',

        configurationText: '{s name=configuration/configuration_text}Here you have the option of offering subscriptions to items for a fixed length of time. Strengthen your relationship with yours customers by offering discounts on subscription purchases.{/s}',

        enableSubscriptionlabel: '{s name=configuration/enable_subscription_label}Enable subscription{/s}',
        enableSubscriptionText: '{s name=configuration/enable_subscription_text}Activating this option enables customers to order this item as a subscription.{/s}',

        exclusiveLabel: '{s name=configuration/exclusive_label}Exclusive subscription article{/s}',
        exclusiveText: '{s name=configuration/exclusive_text}This item cannot be purchased separately, but only as part of a subscription.{/s}'
    },

    /**
     * @return void
     */
    initComponent: function() {
        var me = this;

        me.items = [{
            xtype: 'fieldset',
            defaults: {
                labelWidth: 155,
                labelStyle: 'font-weight: bold'
            },
            title: me.snippets.title,
            items: [
                {
                    xtype: 'container',
                    html: me.snippets.configurationText,
                    margin: '0 0 15',
                    style: 'color: #999; font-style: italic;'
                },
                {
                    xtype: 'checkboxfield',
                    name: 'active',
                    inputValue: 1,
                    uncheckedValue: 0,
                    anchor: '100%',
                    fieldLabel: me.snippets.enableSubscriptionlabel,
                    boxLabel: me.snippets.enableSubscriptionText
                },
                {
                    xtype: 'checkboxfield',
                    name: 'exclusive',
                    inputValue: 1,
                    uncheckedValue: 0,
                    anchor: '100%',
                    fieldLabel: me.snippets.exclusiveLabel,
                    boxLabel: me.snippets.exclusiveText
                }
            ]
        }];

        me.callParent(arguments);
    }
});
// {/block}
