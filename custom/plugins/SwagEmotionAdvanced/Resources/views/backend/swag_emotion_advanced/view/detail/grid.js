//
// {namespace name=backend/swag_emotion_advanced/view/main}
// {block name=backend/swag_emotion_advanced/view/detail/grid}
Ext.define('Shopware.apps.SwagEmotionAdvanced.view.detail.Grid', {

    override: 'Shopware.apps.Emotion.view.detail.Grid',

    initComponent: function () {
        var me = this;

        me.defaultTypeSettings['storytelling'] = {
            sections: 4,
            rowButtons: false
        };

        me.callParent(arguments);
    },

    getSettings: function () {
        var me = this,
            settings = me.callParent(arguments);

        if (settings['mode'] !== 'storytelling') {
            return settings;
        }

        settings['sections'] = me.emotion.get('swagRows') || 6;

        return settings;
    }
});
// {/block}
