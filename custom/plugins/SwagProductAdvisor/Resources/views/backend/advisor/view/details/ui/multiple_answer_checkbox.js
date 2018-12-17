//

//{namespace name="backend/advisor/main"}
//{block name="backend/advisor/view/details/ui/displayfield"}
Ext.define('Shopware.apps.Advisor.view.details.ui.MultipleAnswerCheckbox', {
    extend: 'Ext.form.field.Checkbox',
    alias: 'widget.advisor-details-ui-multiple-answer-checkbox',

    fieldLabel: '{s name="multiple_choice_is_possible"}Allow Multiple Responses{/s}',
    labelWidth: 150,
    helpText: '{s name="multiple_choice_help_text"}This selection determines whether the user can only be one or more answers to the question. This setting also affects the possible layouts of the question.{/s}',
    name: 'multipleAnswers',

    uncheckedValue: false,
    inputValue: true,

    afterRender: function() {
        var me = this;

        me.callParent(arguments);

        me.bodyEl.setStyle({
            'padding': '2px 0'
        });
    },

    /**
     * @overwrite
     *
     *  at this point we need to fire a event that
     *  the user has change the value.
     */
    onBoxClick: function () {
        var me = this;

        me.callParent(arguments);

        me.fireEvent('advisor_multiple_answer_checkbox_click', me.getValue());
    }
});
//{/block}