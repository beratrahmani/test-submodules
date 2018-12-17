//
// {namespace name=backend/swag_emotion_advanced/view/main}
// {block name=backend/swag_emotion_advanced/view/list/grid}
Ext.define('Shopware.apps.SwagEmotionAdvanced.view.list.Grid', {

    override: 'Shopware.apps.Emotion.view.list.Grid',

    initComponent: function() {
        var me = this;

        me.typeMapping['storytelling'] = '{s name="settings/mode/store/story/display"}{/s}';

        me.callParent(arguments);
    }
});
// {/block}
