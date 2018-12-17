
//{block name="backend/customer/ticket_system/model/employee"}
Ext.define('Shopware.apps.Customer.model.ticket_system.Employee', {

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