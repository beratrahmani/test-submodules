//

//{namespace name="backend/advisor/main"}
//{block name="backend/advisor/view/details/ui/answer-layout-selection"}
Ext.define('Shopware.apps.Advisor.view.details.ui.AnswerLayoutSelection', {
    extend: 'Ext.container.Container',
    alias: 'widget.advisor-details-ui-Answer-Layout-Selection',
    layout: 'anchor',
    anchor: '100%',

    oldValue: null,

    /**
     * the answerLayoutSelection contains a Checkbox and a template comboBox selection
     *
     * init this component
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
        var me = this;

        return [
            me.createMultipleAnswerCheckbox(),
            me.createAnswerLayoutComboBox()
        ];
    },

    /**
     * @returns { Shopware.apps.Advisor.view.details.ui.MultipleAnswerCheckbox | * }
     */
    createMultipleAnswerCheckbox: function () {
        var me = this;

        me.multipleAnswerCheckbox = Ext.create('Shopware.apps.Advisor.view.details.ui.MultipleAnswerCheckbox', {
            advisor: me.advisor,
            question: me.question,
            width: '40px',
            anchor: 0,
            listeners: {
                'advisor_multiple_answer_checkbox_click': function (newValue) {
                    me.fireEvent('advisor_multiple_answer_checkbox_change', newValue);
                }
            }
        });

        return me.multipleAnswerCheckbox;
    },

    /**
     * @returns { Shopware.apps.Advisor.view.details.ui.TemplateSelection | * }
     */
    createAnswerLayoutComboBox: function () {
        var me = this;

        me.answerLayoutCombobox = Ext.create('Shopware.apps.Advisor.view.details.ui.TemplateSelection', {
            anchor: '100%',
            disabled: true,
            listeners: {
                change: function (comboBox, newValue) {
                    me.fireEvent('advisor_answer_layout_selection_change', newValue);
                }
            }
        });

        return me.answerLayoutCombobox;
    },

    /**
     * @param { Array } templates
     */
    setTemplates: function (templates) {
        var me = this;

        me.answerLayoutCombobox.setTemplates(templates);
        me.answerLayoutCombobox.enable();
    },

    /**
     * @param { string } value
     * @returns { null | * | Shopware.apps.Advisor.view.components.layouts.AbstractLayout }
     */
    getSelectedTemplate: function (value) {
        var me = this;

        return me.answerLayoutCombobox.getSelected(value);
    }

});
//{/block}
