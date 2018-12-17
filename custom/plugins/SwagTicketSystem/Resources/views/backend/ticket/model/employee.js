
//{block name="backend/ticket/model/employee"}
Ext.define('Shopware.apps.Ticket.model.Employee', {

    /**
     * Extends the standard User Model
     * @string
     */
    extend:'Shopware.apps.Base.model.User',
    /**
     * Contains the model fields
     * @array
     */
    fields:[
        //{block name="backend/ticket/model/employee/fields"}{/block}
        { name: 'id', type:'int' },
        { name: 'name', type:'string' }
    ]
});
//{/block}
