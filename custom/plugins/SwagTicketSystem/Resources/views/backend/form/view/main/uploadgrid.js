
//{namespace name="backend/form/view/main"}
//{block name="backend/form/view/main/fieldgrid"}
//{$smarty.block.parent}
Ext.define('Shopware.apps.Form.view.main.Uploadgrid', {
    
    override: 'Shopware.apps.Form.view.main.Fieldgrid',
    
    /**
     * Override fieldgrid and add new upload field
     */
    getTypComboStore: function() {
        return new Ext.data.SimpleStore({
            fields: ['id', 'label'],
            data: [
                ['text', 'Text'],
                ['text2', 'Text2'],
                ['checkbox', 'Checkbox'],
                ['email', 'Email'],
                ['select', 'select'],
                ['textarea', 'textarea'],
                ['upload', 'Upload']
            ]
        });
    }
    
});
//{/block}