
//{block name="backend/ticket/model/customer"}
Ext.define('Shopware.apps.Ticket.model.Customer', {

    /**
     * Extends the standard Customer Model
     * @string
     */
    extend:'Shopware.apps.Base.model.Customer',
    /**
     * Contains the model fields
     * @array
     */
    fields:[
		//{block name="backend/ticket/model/customer/fields"}{/block}
        { name: 'id', type: 'int' },
        { name: 'name', type: 'string' }
    ]
});
//{/block}
