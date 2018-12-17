//{namespace name=backend/plugins/swag_digital_publishing/main}
//{block name="backend/swag_digital_publishing/store/content_banner"}
Ext.define('Shopware.apps.SwagDigitalPublishing.store.ContentBanner', {

    extend: 'Ext.data.Store',

    model: 'Shopware.apps.SwagDigitalPublishing.model.ContentBanner',

    batch: true,

    remoteFilter: true,

    proxy: {
        type: 'ajax',
        api: {
            read: '{url controller="SwagContentBanner" action=list}'
        },
        reader: {
            type: 'json',
            root: 'data'
        }
    }
});
//{/block}