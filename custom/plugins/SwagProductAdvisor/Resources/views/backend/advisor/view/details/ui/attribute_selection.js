//

//{namespace name="backend/advisor/main"}
//{block name="backend/advisor/view/details/ui/attribute-selection"}
Ext.define('Shopware.apps.Advisor.view.details.ui.AttributeSelection', {
    extend: 'Ext.container.Container',
    alias: 'widget.advisor-details-ui-AttributeSelection',
    layout: 'anchor',

    snippets: {
        selectionLabel: '{s name="attribute_filter_attribute"}Attribute{/s}',
        helpText: '{s name=attribute_filter_help_text}All available attributes are listed here.{/s}'
    },

    /**
     * @overwrite
     */
    initComponent: function () {
        var me = this;

        me.items = me.createItems();

        me.callParent(arguments);
    },

    /**
     * @returns { *[] }
     */
    createItems: function () {
        return [this.createAttributeSelection()];
    },

    /**
     * @returns { Ext.form.field.ComboBox }
     */
    createAttributeSelection: function () {
        var me = this;

        return Ext.create('Ext.form.field.ComboBox', {
            name: 'configuration',
            fieldLabel: me.snippets.selectionLabel,
            labelWidth: 150,
            displayField: 'name',
            valueField: 'id',
            helpText: me.snippets.helpText,
            anchor: '100%',
            store: me.createAttributeStore(),
            editable: false,
            forceSelection: true,
            allowBlank: false,
            listeners: {
                change: Ext.bind(me.onChange, me),
                select: Ext.bind(me.answerGrid.clearGrid, me.answerGrid)
            }
        });
    },

    /**
     * @param { Ext.form.field.Combobox } comboBox
     * @param { string } newValue
     */
    onChange: function (comboBox, newValue) {
        var me = this;

        me.question.set('configuration', newValue);

        me.answerGrid.refreshGridData(
            me.answerGrid.advisor,
            me.answerGrid.question,
            me.answerGrid.store
        );
    },

    /**
     * @returns { Ext.data.Store | * }
     */
    createAttributeStore: function () {
        return Ext.create('Ext.data.Store', {
            fields: ['id', 'name'],
            proxy: {
                type: 'ajax',
                url: '{url controller=Advisor action=getAttributesAjax}',
                reader: {
                    type: 'json',
                    root: 'data'
                }
            }
        }).load();
    }
});
//{/block}