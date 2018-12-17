//
// {namespace name=backend/swag_emotion_advanced/view/main}
// {block name=backend/swag_emotion_advanced/view/detail/window}
Ext.define('Shopware.apps.SwagEmotionAdvanced.view.detail.Window', {
    override: 'Shopware.apps.Emotion.view.detail.Window',

    /**
     * @override
     *
     * @param { Shopware.apps.Emotion.model.Emotion } emotion
     * @param activeTab
     */
    loadEmotion: function(emotion, activeTab) {
        var me = this,
            mode = emotion.get('mode');

        me.callParent(arguments);

        if (mode === 'storytelling') {
            // fallback for old save data:
            // Set "showListing" to false if storytelling is active
            emotion.set('showListing', false);

            me.settingsForm.listingCheckbox.hide();
        }
    },
});
// {/block}