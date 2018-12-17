
//{block name="backend/ticket/model/locale"}
Ext.define('Shopware.apps.Ticket.model.Locale', {
    /**
     * Extends the standard Ext Model
     * @string
     */
    extend: 'Ext.data.Model',

    /**
     * The fields used for this model
     * @array
     */
    fields: [
        //{block name="backend/ticket/model/locale/fields"}{/block}
        { name: 'id', type: 'int' },
        { name: 'name', type: 'string' }
    ]
});
//{/block}