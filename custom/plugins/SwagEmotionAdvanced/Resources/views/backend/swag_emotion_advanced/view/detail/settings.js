//
// {namespace name=backend/swag_emotion_advanced/view/main}
// {block name=backend/swag_emotion_advanced/view/detail/settings}
Ext.define('Shopware.apps.SwagEmotionAdvanced.view.detail.Settings', {

    override: 'Shopware.apps.Emotion.view.detail.Settings',

    initComponent: function() {
        var me = this;

        me.callParent(arguments);

        me.createQuickViewCheckbox();
    },

    createQuickViewCheckbox: function() {
        var me = this;

        me.quickViewField = Ext.create('Ext.form.field.Checkbox', {
            name: 'swagQuickview',
            uncheckedValue: 0,
            inputValue: 1,
            checked: me.emotion.get('swagQuickview') || false,
            fieldLabel: '{s name="label/field/quick_view"}{/s}',
            boxLabel: '{s name="label/box/quick_view"}{/s}',
            labelWidth: me.defaults.labelWidth
        });

        me.mainFieldset.add(me.quickViewField);
    }
});
// {/block}
