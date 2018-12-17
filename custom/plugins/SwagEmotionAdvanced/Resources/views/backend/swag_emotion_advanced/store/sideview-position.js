//
// {block name=backend/swag_emotion_advanced/store/side_view_position}
Ext.define('Shopware.apps.Emotion.store.SideviewPosition', {
    extend: 'Ext.data.Store',
    fields: [ 'value', 'display' ],
    data: [
        { value: 'right', display: 'Slidet von der rechten Seite in das Element' },
        { value: 'bottom', display: 'Slidet von der unteren Seite in das Element' }
    ]
});
// {/block}
