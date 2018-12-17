
//{block name="backend/advisor/view/details/ui/advisor-selection-model"}
Ext.define('Shopware.apps.Advisor.view.details.ui.AdvisorSelectionModel', {
    extend: 'Ext.selection.CheckboxModel',

    /**
     * Fixes an issue with the drag-and-drop plugin and the selection model on the grid.
     * @overwrite
     */
    bindComponent: function(view) {
        var me = this;

        me.views = me.views || [];
        me.views.push(view);
        me.bindStore(view.getStore(), true);

        view.on({
            itemclick: me.onRowMouseDown,
            scope: me
        });

        if (me.enableKeyNav) {
            me.initKeyNav(view);
        }
    }
});
//{/block}