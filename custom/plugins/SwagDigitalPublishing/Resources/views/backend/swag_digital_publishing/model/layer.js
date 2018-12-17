//{namespace name=backend/plugins/swag_digital_publishing/main}
//{block name="backend/swag_digital_publishing/model/layer"}
Ext.define('Shopware.apps.SwagDigitalPublishing.model.Layer', {

    extend: 'Ext.data.Model',

    fields: [
        { name: 'id',               type: 'int', useNull: true },
        { name: 'contentBannerID',  type: 'int' },
        { name: 'position',         type: 'int' },
        { name: 'label',            type: 'string' },
        { name: 'width',            type: 'string', defaultValue: 'auto' },
        { name: 'height',           type: 'string', defaultValue: 'auto' },
        { name: 'marginTop',        type: 'int',    defaultValue: 0 },
        { name: 'marginRight',      type: 'int',    defaultValue: 0 },
        { name: 'marginBottom',     type: 'int',    defaultValue: 0 },
        { name: 'marginLeft',       type: 'int',    defaultValue: 0 },
        { name: 'borderRadius',     type: 'int',    defaultValue: 0 },
        { name: 'orientation',      type: 'string', defaultValue: 'center center' },
        { name: 'bgColor',          type: 'string' },
        { name: 'link',             type: 'string' }
    ],

    associations: [{
        type: 'hasMany',
        model: 'Shopware.apps.SwagDigitalPublishing.model.Element',
        name: 'getElements',
        associationKey: 'elements'
    }]
});
//{/block}