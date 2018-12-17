//{namespace name=backend/plugins/swag_digital_publishing/main}
//{block name="backend/swag_digital_publishing/model/content_banner"}
Ext.define('Shopware.apps.SwagDigitalPublishing.model.ContentBanner', {

    extend: 'Ext.data.Model',

    fields: [
        { name: 'id',               type: 'int',        useNull: true },
        { name: 'name',             type: 'string' },
        { name: 'bgType',           type: 'string',     defaultValue: 'color' },
        { name: 'bgOrientation',    type: 'string',     defaultValue: 'center center' },
        { name: 'bgMode',           type: 'string',     defaultValue: 'cover'  },
        { name: 'bgColor',          type: 'string' },
        { name: 'mediaId',          type: 'int' }
    ],

    associations: [{
        type: 'hasMany',
        model: 'Shopware.apps.SwagDigitalPublishing.model.Layer',
        name: 'getLayers',
        associationKey: 'layers'
    }],

    proxy: {
        type: 'ajax',
        api: {
            read:    '{url controller="SwagContentBanner" action="detail"}',
            create:  '{url controller="SwagContentBanner" action="create"}',
            update:  '{url controller="SwagContentBanner" action="update"}',
            destroy: '{url controller="SwagContentBanner" action="delete"}'
        },
        reader: {
            type: 'json',
            root: 'data'
        }
    }
});
//{/block}