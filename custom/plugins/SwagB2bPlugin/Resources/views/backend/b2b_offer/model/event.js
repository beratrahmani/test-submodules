Ext.define('Shopware.apps.b2bOffer.model.Event', {
    extend: 'Shopware.data.Model',
    fields: [
        { name: 'id', type: 'int' },
        { name: 'logValue', type: 'auto' },
        { name: 'logType', type: 'string' },
        { name: 'eventDate', type: 'auto' },
        { name: 'authorHash', type: 'string' },
        { name: 'authorIdentity', type: 'auto' },
    ],

    configure: function() {
        return {
            controller: 'B2bOffer'
        };
    },
});