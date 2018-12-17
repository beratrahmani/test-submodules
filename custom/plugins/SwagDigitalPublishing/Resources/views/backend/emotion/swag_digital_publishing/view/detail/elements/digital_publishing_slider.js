//
//{namespace name=backend/plugins/swag_digital_publishing/emotion}
//{block name="backend/emotion/view/detail/elements/base"}
//{$smarty.block.parent}
Ext.define('Shopware.apps.Emotion.view.detail.elements.DigitalPublishingSlider', {

    extend: 'Shopware.apps.Emotion.view.detail.elements.Base',

    alias: 'widget.detail-element-emotion-digital-publishing-slider',

    componentCls: 'emotion--digital-publishing-slider',

    icon: 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACQAAAAfCAYAAACPvW/2AAACYklEQVRYhdWXvWsUQRiHn4tnoddoCm0scpBKFBUjIQFJ4T9wNjEWc4XCgCISGUhraeOAWk4sZKcQ0xgsA6JplIMIJxIkjUllJYkkqIXoWdxM3IC7O3N7F/DX7DLzfjzszLzvbIUMaWOPAFtZ8wGqKyk2Yp2qATZ3gHZEzFFgPhbEKwSoraR4HRpQG9sCVoDPgwLyiS4DUzHBtbH+dVlJ8byvQA7mCtCKgQLG3bPvQAAtJUUjxkEbexv4EmofC+STVIETgeYvnM+IH8g7fT0BOZj1Hn0BKlkTvQJ5zQBrEfZjFJSEskBrSordGqVNclDJ5s8sY1dsczVUEsiD1LRJVoDv2iQ3ysTqCxBwAThP94tfLxOoX0DvgFWgAyRlAoUAjWpjD+UZKNncAU4DNSWbj8oAhWzqebq9KVdKNjvAjzIwRUDbQN295zZKbZIq0ACOAx+Aj8BJQAGngDfArVJASorfwMbfpPafdtokx4BFYCInTx24HwIUu6nHXW9Ka7IAJkoxhXHZPYMbZS/K7Cl5co1yHTgHnVWgFuC2A5WLwCslxcB62RhUCttBSmeLDMoC9Xx3/m+0Zy21sYeBx8BNJcXXQSfXxj4DrikpvvmxodTkCN0CdnXQIClNA2/Tt8mKg7kELADDbvwM3UqdpW0lxWZeJnf3Kdrw/ta5CUwrKV5WtbFzwD32Fsn3BYEeArMFNrPA3QIbr2FgSRs7NwT8CnTaDx2oKim0NrZN5JIFBH8APCmw8Uu2BcwoKZZ2T5nbWIsO5ug+nbIO3dtBQ0nxCVL7xv0rTQJPBw2S0gIw4WEA/gATvqdbrrDsXQAAAABJRU5ErkJggg==',

    createPreview: function() {
        var me = this,
            preview = me.callParent(arguments),
            data = me.getConfigValue('digital_publishing_slider_preview_data'),
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