
//{block name="backend/ticket/model/mapping"}
Ext.define('Shopware.apps.Ticket.model.Mapping', {
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
		//{block name="backend/ticket/model/form_field/fields"}{/block}
        { name: 'message', type: 'int' },
        { name: 'subject', type: 'int' },
        { name: 'author', type: 'int' },
        { name: 'email', type: 'int' },
        { name: 'ext', type: 'string' }
    ]
});
//{/block}
