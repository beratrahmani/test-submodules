//{namespace name=backend/plugins/swag_digital_publishing/main}
//{block name="backend/swag_digital_publishing/model/element"}
Ext.define('Shopware.apps.SwagDigitalPublishing.model.Element', {

    extend: 'Ext.data.Model',

    fields: [
        { name: 'id',       type: 'int', useNull: true },
        { name: 'layerID',  type: 'int', useNull: true },
        { name: 'position', type: 'int' },
        { name: 'name',     type: 'string' },
        { name: 'label',    type: 'string' },
        { name: 'payload' }
    ]
});
//{/block}