//
// {namespace name=backend/swag_emotion_advanced/view/main}
// {block name=backend/swag_emotion_advanced/view/detail/elements/side_view}
Ext.define('Shopware.apps.Emotion.view.detail.elements.SideView', {

    extend: 'Shopware.apps.Emotion.view.detail.elements.Base',

    alias: 'widget.detail-element-emotion-sideview-widget',

    componentCls: 'emotion-sideview-widget',

    icon: 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACQAAAAkCAYAAADhAJiYAAABhElEQVRYhe2Yv0vDQBSAP2t19MfsVJxFHZ0ExX9CbQbBA0e5yUEFKTjo7XLjdfOfEOdOOgg6KYIIQilOIoouSanSyyXNNc2QDwK5vMvjI3eXB29M6eYP/qhJUX+MBko3O8B0mgQVjzJeqPbcz3rI9255/ufL9SNaqa6QFPWOB6HMFG7JSiEXVdcEpU0F2Af2gBrwBGjgTIrgO3ch4ALY7RnXgFNgAdjyLRS7ZEqb5X8yvWwqbVZyFQLWHfENXyIRLqGs8dS4El5ljKcmVkiKoAUYS/hSiuA6V6GQHeAQeAnHr8AJsO1bBhIc+/Bf0wAaSpuqFMHXMEQiUm3KYctAAUtHKeSiFHKRpNqjtJkHVoE54AN4Bm6BB98nL1YorPbnwJplyqfS5g64B96AYymCtnchpc0EcAQcAOMx708CS+EFoAD/QkALWMySeFBsm3okMlDAU1YKuSickO2UDdp4sDUbEtNXSIpgZI2HRKXDA1NKN2eSTOwKeeqk2fpAN0kTFG5T/wJJAVYEEpuvWgAAAABJRU5ErkJggg==',

    createPreview: function() {
        var me = this,
            preview = '',
            image = me.getConfigValue('sideview_banner'),
            position = me.getConfigValue('sideview_bannerposition') || 'center center',
            style;

        if (Ext.isDefined(image)) {
            style = Ext.String.format('background-image: url([0]); background-position: [1];', image, position);

            preview = Ext.String.format('<div class="x-emotion-banner-element-preview" style="[0]"></div>', style);
        }

        return preview;
    }
});
// {/block}
