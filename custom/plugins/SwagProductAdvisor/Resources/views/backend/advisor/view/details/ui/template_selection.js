//

//{namespace name="backend/advisor/main"}
//{block name="backend/advisor/view/details/ui/templateSelection"}
Ext.define('Shopware.apps.Advisor.view.details.ui.TemplateSelection', {
    extend: 'Shopware.apps.Base.view.element.ProductBoxLayoutSelect',

    labelWidth: 150,
    name: 'template',

    snippets: {
        fieldLabel: '{s name="template_selection_label"}Answer layout{/s}',
        helpText: '{s name="template_selection_help_text"}With the layout you determine how the answers are to be displayed.{/s}'
    },

    editable: false,
    forceSelection: true,

    /**
     * init this component
     */
    initComponent: function () {
        var me = this;

        me.fieldLabel = me.snippets.fieldLabel;
        me.helpText = me.snippets.helpText;

        me.callParent(arguments);
    },

    /**
     * @overwrite
     *
     * @returns { Ext.data.Store }
     */
    createStore: function () {
        var me = this,
            data = me.data || [];

        me.store = Ext.create('Ext.data.Store', {
            model: 'Shopware.apps.Base.model.ProductBoxLayout',
            data: data
        });

        return me.store;
    },

    /**
     * @param { Array } layoutArray
     * @returns { Array }
     */
    getStoreDataFormLayouts: function (layoutArray) {
        var data = [];

        Ext.Array.each(layoutArray, function (layout) {
            data.push(layout.getProductBoxLayoutModel());
        });

        return data;
    },

    /**
     * @param { Array } layoutArray. Array of templates
     */
    setTemplates: function (layoutArray) {
        var me = this;

        me.data = me.getStoreDataFormLayouts(layoutArray);
        me.clearValue();

        me.bindStore(me.createStore());
    },

    /**
     *
     * @param { null | string } value
     * @returns { Shopware.apps.Advisor.view.components.layouts.AbstractLayout | null }
     */
    getSelected: function (value) {
        //noinspection JSDuplicatedDeclaration
        var me = this,
            value = value || me.getValue();

        if (!value) {
            return null;
        }

        return me.findRecordByValue(value);
    }
});
//{/block}