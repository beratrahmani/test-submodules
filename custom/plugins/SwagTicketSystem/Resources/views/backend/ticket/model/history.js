
//{block name="backend/ticket/model/history"}
Ext.define('Shopware.apps.Ticket.model.History', {
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
		//{block name="backend/ticket/model/history/fields"}{/block}
        { name: 'id', type: 'int' },
        { name: 'ticketId', type: 'int' },
        { name: 'email', type: 'string' },
        { name: 'swUser', type: 'string' },
        { name: 'subject', type: 'string' },
        { name: 'message', type: 'string' },
        { name: 'receipt', type: 'date' },
        { name: 'support_type', type: 'string' },
        { name: 'receiver', type: 'string' },
        { name: 'direction', type: 'string' }
    ]
});
//{/block}
