//
// {namespace name=backend/swag_emotion_advanced/view/main}
// {block name=backend/swag_emotion_advanced/view/detail/preview}
Ext.define('Shopware.apps.SwagEmotionAdvanced.view.detail.Preview', {

    override: 'Shopware.apps.Emotion.view.detail.Preview',

    showPreview: function () {
        var me = this;

        // fixes layout issues with storytelling preview opened via listing
        if (me.emotion.get('mode') === 'storytelling') {
            me.on('boxready', function(comp) {
                comp.setHeight(comp.ownerCt.getHeight() - comp.ownerCt.toolbar.getHeight() - 40);
            });
        }

        me.checkSettings();
        me.callParent(arguments);
    },

    changePreview: function () {
        var me = this;

        me.checkSettings();
        me.callParent(arguments);
    },

    checkSettings: function () {
        var me = this,
            height = (me.emotion.get('mode') === 'storytelling') ? '100%' : 9000;

        me.setPreviewHeight(height);
    },

    setPreviewHeight: function (height) {
        var me = this,
            style = { 'height': height };

        me.height = height;
        me.getEl().setStyle(style);
        me.getEl().down('.x-panel-body').setStyle(style);
    }
});
// {/block}
