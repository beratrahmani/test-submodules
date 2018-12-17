
//{block name="backend/ticket/model/form_field"}
Ext.define('Shopware.apps.Ticket.model.FormField', {
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
        { name: 'id', type: 'int' },
        { name: 'added', type: 'date' },
        { name: 'class', type: 'string' },
        { name: 'formId', type: 'int' },
        { name: 'label', type: 'string' },
        { name: 'name', type: 'string' },
        { name: 'note', type: 'string' },
        { name: 'typ', type: 'string' },
        { name: 'value', type: 'string' }
    ]
});
//{/block}
