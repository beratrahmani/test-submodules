//
//{namespace name=backend/plugins/swag_digital_publishing/emotion}
//{block name="backend/emotion/view/detail/elements/base"}
//{$smarty.block.parent}
Ext.define('Shopware.apps.Emotion.view.detail.elements.DigitalPublishing', {

    extend: 'Shopware.apps.Emotion.view.detail.elements.Base',

    alias: 'widget.detail-element-emotion-digital-publishing',

    componentCls: 'emotion--digital-publishing',

    icon: 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACQAAAAkCAYAAADhAJiYAAACBklEQVRYhc2YPUscQRiAnwvXiQgBvyBNIATFyibXpPEXKAgJBPfQZhorHSysLWWKtBPCyW4vp31Ic81JUkhQG0Ww8gPExspCi52T9bx1d2Zncz7V7b477zzM7My+cxWlo3vKZUeKYC7vw2/KNHGh2vkhRVDxmVjpaBFo2LarZj/izCHwHfhn06g0ISmCPWDPtl1PIaWjD8DnolIZtKQIjnMJGRnr+bdkCcgt1OEcaHsWqQFjacEsobbNHpIHpaMmMJsW78c+tAJMA7u9gmUu+55IEZy+FLcSUjpaJ34HSsN2hGq8MP8+cJ2yn0DLpwhmm3EVakkRbPlzAaWjBrzmr31RlA4ngXGgLUX91jWPlxFSOlTEX/dfwImR64+Q6Xw1cWsU2OibEPE0dfPONZkPoTZw0XWv6ZqssJB5gWeAbWK5dWDTNd/jKjM18KGp9GyljoB5V4kkyRFqAN98JC1CFdhJXFsV5GVQ9V2ApaF0+BYQwCfgDvgD/JCifvNEyENHFWDBdPYRuAb2gb/AAXBPXKMvA0OJpl+ANaXDKSnqV16ElA4HgRBIjvIIMAF8zZFiGBgAHoWyln1N6aipdPQ+Jb7dJVOYLKEx4oJsKCU+7FMG0qesRXxu6nDmu+M0egqZE+WzQ9z/oOgqWyF9OvNymbwoJCRF/Xcxl+dY/SeUder0waurqR8A8e51AHOWpVAAAAAASUVORK5CYII=',

    createPreview: function() {
        var me = this,
            preview = me.callParent(arguments),
            data = me.getConfigValue('digital_publishing_banner_data'),
            style;

        if (!data || data.length === 0) {
            return preview;
        }

        data = Ext.decode(data);

        if (data['bgType'] === 'image' && data['media']) {
            style = Ext.String.format(
                'background-image: url([0]); background-position: [1];',
                data['media'].source,
                data['bgOrientation']
            );

            preview = Ext.String.format('<div class="x-emotion-banner-element-preview" style="[0]"></div>', style);

        } else if (data['bgType'] === 'color' && data['bgColor']) {
            style = Ext.String.format('background: [0];', data['bgColor']);

            preview = Ext.String.format('<div class="x-emotion-banner-element-preview" style="[0]"></div>', style);
        }

        return preview;
    }
});
//{/block}