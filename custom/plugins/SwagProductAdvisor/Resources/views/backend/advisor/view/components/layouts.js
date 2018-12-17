//

//{namespace name="backend/advisor/main"}
//{block name="backend/advisor/view/components/layouts"}
Ext.define('Shopware.apps.Advisor.view.components.Layouts', {
    alias: 'widget.advisor-components-layouts',

    // price Configs
    wizardPriceRangeSliderTemplateConfig: {
        mediaAllowed: false,
        designerAllowed: false,
        addAnswerAllowed: false,
        editValueAllowed: false,
        answerEditorIsNumberField: true,
        answerSelectionAllowed: false
    },

    wizardPriceRadioImageTemplateConfig: {
        mediaAllowed: true,
        designerAllowed: true,
        addAnswerAllowed: true,
        editValueAllowed: true,
        answerEditorIsNumberField: false,
        answerSelectionAllowed: false
    },

    wizardPriceRadioTemplateConfig: {
        mediaAllowed: false,
        designerAllowed: false,
        addAnswerAllowed: true,
        editValueAllowed: true,
        answerEditorIsNumberField: false,
        answerSelectionAllowed: false
    },

    sidebarPriceRangeSliderTemplateConfig: {
        mediaAllowed: false,
        designerAllowed: false,
        addAnswerAllowed: false,
        editValueAllowed: false,
        answerEditorIsNumberField: true,
        answerSelectionAllowed: false
    },

    sidebarPriceRadioTemplateConfig: {
        mediaAllowed: false,
        designerAllowed: false,
        addAnswerAllowed: true,
        editValueAllowed: true,
        answerEditorIsNumberField: false,
        answerSelectionAllowed: false
    },

    sidebarPriceComboboxTemplateConfig: {
        mediaAllowed: false,
        designerAllowed: false,
        addAnswerAllowed: true,
        editValueAllowed: true,
        answerEditorIsNumberField: false,
        answerSelectionAllowed: false
    },

    // Default configs
    wizardCheckboxTemplate: {
        mediaAllowed: false,
        designerAllowed: false,
        addAnswerAllowed: false,
        editValueAllowed: false,
        answerEditorIsNumberField: false,
        answerSelectionAllowed: true
    },

    wizardRadioImageTemplate: {
        mediaAllowed: true,
        designerAllowed: true,
        addAnswerAllowed: false,
        editValueAllowed: false,
        answerEditorIsNumberField: false,
        answerSelectionAllowed: true
    },

    wizardCheckboxImageTemplate: {
        mediaAllowed: true,
        designerAllowed: true,
        addAnswerAllowed: false,
        editValueAllowed: false,
        answerEditorIsNumberField: false,
        answerSelectionAllowed: true
    },

    sidebarCheckboxTemplate: {
        mediaAllowed: false,
        designerAllowed: false,
        addAnswerAllowed: false,
        editValueAllowed: false,
        answerEditorIsNumberField: false,
        answerSelectionAllowed: true
    },

    sidebarRadioTemplate: {
        mediaAllowed: false,
        designerAllowed: false,
        addAnswerAllowed: false,
        editValueAllowed: false,
        answerEditorIsNumberField: false,
        answerSelectionAllowed: true
    },

    /**
     * @param { string } advisorMode
     * @param { boolean } isMultipleSelection
     * @returns Array
     */
    getTemplates: function (advisorMode, isMultipleSelection) {
        var me = this;

        if (advisorMode == 'wizard_mode') {
            if (isMultipleSelection) {
                return me.getWizardMultipleChoiceTemplates();
            }

            return me.getWizardSingleChoiceTemplates();
        }

        if (isMultipleSelection) {
            return me.getSidebarMultipleChoiceTemplates();
        }

        return me.getSidebarSingleChoiceTemplates();
    },

    /**
     * @param { string } advisorMode
     * @returns { Array }
     */
    getPriceTemplates: function (advisorMode) {
        var me = this,
            rangeSliderTemplate = Ext.create('Shopware.apps.Advisor.view.components.layouts.RangeSlider'),
            radioImageTemplate = Ext.create('Shopware.apps.Advisor.view.components.layouts.RadioImage'),
            comboboxTemplate = Ext.create('Shopware.apps.Advisor.view.components.layouts.Combobox'),
            radioTemplate = Ext.create('Shopware.apps.Advisor.view.components.layouts.Radio');

        if (advisorMode == 'wizard_mode') {
            rangeSliderTemplate.setGridConfig(me.wizardPriceRangeSliderTemplateConfig);
            radioImageTemplate.setGridConfig(me.wizardPriceRadioImageTemplateConfig);
            radioTemplate.setGridConfig(me.wizardPriceRadioTemplateConfig);

            return [
                //{block name="backend/advisor/view/components/layouts/wizard_price_templates"}{/block}
                rangeSliderTemplate,
                radioTemplate,
                radioImageTemplate
            ];
        }

        rangeSliderTemplate.setGridConfig(me.sidebarPriceRangeSliderTemplateConfig);
        radioTemplate.setGridConfig(me.sidebarPriceRadioTemplateConfig);
        comboboxTemplate.setGridConfig(me.sidebarPriceComboboxTemplateConfig);

        return [
            //{block name="backend/advisor/view/components/layouts/sidebar_price_templates"}{/block}
            rangeSliderTemplate,
            comboboxTemplate,
            radioTemplate
        ];
    },

    /**
     * @returns { Array }
     */
    getSidebarSingleChoiceTemplates: function () {
        var me = this,
            radioTemplate = Ext.create('Shopware.apps.Advisor.view.components.layouts.Radio'),
            comboboxTemplate = Ext.create('Shopware.apps.Advisor.view.components.layouts.Combobox');

        radioTemplate.setGridConfig(me.sidebarRadioTemplate);
        comboboxTemplate.setGridConfig(me.sidebarCheckboxTemplate);

        return [
            //{block name="backend/advisor/view/components/layouts/sidebar_single_choice"}{/block}
            radioTemplate,
            comboboxTemplate
        ];
    },

    /**
     * @returns { Array }
     */
    getSidebarMultipleChoiceTemplates: function () {
        var me = this,
            checkboxTemplate = Ext.create('Shopware.apps.Advisor.view.components.layouts.Checkbox');

        checkboxTemplate.setGridConfig(me.sidebarCheckboxTemplate);

        return [
            //{block name="backend/advisor/view/components/layouts/sidebar_multiple_choice"}{/block}
            checkboxTemplate
        ];
    },

    /**
     * @returns { Array }
     */
    getWizardSingleChoiceTemplates: function () {
        var me = this,
            radioTemplate = Ext.create('Shopware.apps.Advisor.view.components.layouts.Radio'),
            radioImageTemplate = Ext.create('Shopware.apps.Advisor.view.components.layouts.RadioImage');

        radioTemplate.setGridConfig(me.sidebarRadioTemplate);
        radioImageTemplate.setGridConfig(me.wizardRadioImageTemplate);

        return [
            //{block name="backend/advisor/view/components/layouts/wizard_single_choice"}{/block}
            radioTemplate,
            radioImageTemplate
        ];
    },

    /**
     * @returns { Array }
     */
    getWizardMultipleChoiceTemplates: function () {
        var me = this,
            checkboxImageTemplate = Ext.create('Shopware.apps.Advisor.view.components.layouts.CheckboxImage'),
            checkboxTemplate = Ext.create('Shopware.apps.Advisor.view.components.layouts.Checkbox');

        checkboxImageTemplate.setGridConfig(me.wizardCheckboxImageTemplate);
        checkboxTemplate.setGridConfig(me.wizardCheckboxTemplate);

        return [
            //{block name="backend/advisor/view/components/layouts/wizard_multiple_choice"}{/block}
            checkboxTemplate,
            checkboxImageTemplate
        ];
    }
});
//{/block}