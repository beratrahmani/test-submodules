//{namespace name=backend/plugins/b2b_debtor_plugin}
Ext.define('Shopware.apps.Customer.debtor.model.Emotion', {
    extend: 'Ext.data.Model',
    fields: [
        { name: 'id', type: 'int' },
        { name: 'emotionName', type: 'string', mapping: function(value) {
            if(value.id === 0) {
                return '{s name=NoLandingpage}no landingpage{/s}'
            }

            return value.name;
        }}
    ]
});
