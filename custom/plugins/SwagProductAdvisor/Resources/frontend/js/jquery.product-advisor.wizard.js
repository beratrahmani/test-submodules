;(function ($) {
    'use strict';

    $.plugin('swProductAdvisorWizard', {

        defaults: {

            /**
             * This is the selector for the label of a non-image radio-question.
             * @type string
             */
            questionRadioLabelSelector: '.question-ct--radio-ct .question-ct--label',

            /**
             * This is the selector for the label of an image radio-question.
             * @type string
             */
            questionImageRadioLabelSelector: '.question-ct--radio-image.question-ct--label',

            /**
             * The selector for the wizard-question-wrapper.
             * @type string
             */
            questionWrapperSelector: '.advisor--wizard-question',

            /**
             * The selector for a checkbox inside of the wizard-question-wrapper.
             * @type string
             */
            questionWrapperCheckboxSelector: '.advisor--wizard-question :checkbox',

            /**
             * The name of the data-attribute to display a wizard-question as "required"
             * @type string
             */
            requiredDataAttribute: 'data-advisor-required',

            /**
             * The selector for the next-button in the wizard.
             * @type string
             */
            nextButtonSelector: '.next-button--btn',

            /**
             * The selector for the skip-select to skip to a question.
             * @type string
             */
            skipSelectSelector: '.wizard-actions--question-select'
        },

        /**
         * Initializes the necessary events, applies the data-attributes and additionally initializes the global-variables.
         */
        init: function () {
            var me = this;

            /**
             * The next-question button in the wizard-mode.
             *
             * @private
             * @property nextButton
             * @type {jQuery}
             */
            me.nextButton = me.$el.find(me.opts.nextButtonSelector);

            me.applyDataAttributes(false);
            me.registerEvents();
        },

        /**
         * Will register all necessary events for this plugin.
         */
        registerEvents: function () {
            var me = this;

            me._on(me.opts.skipSelectSelector, 'change', $.proxy(me.onChangeSkipSelect, me));
            me._on(me.opts.questionRadioLabelSelector, 'click', $.proxy(me.onClickRadioLabel, me));
            me._on(me.opts.questionImageRadioLabelSelector, 'click', $.proxy(me.onClickRadioLabel, me));
            me._on(me.$el.find('img'), 'error', $.proxy(me.onErrorImage, me));
        },

        /**
         * Hides an image when the src couldn't be loaded properly.
         */
        onErrorImage: function (event) {
            $(event.currentTarget).hide();
        },

        /**
         * Triggered when changing the selected option of the "skip"-selection to skip a question.
         * It will read the attached url and redirect to that page.
         */
        onChangeSkipSelect: function (event) {
            var me = this,
                $select = $(event.currentTarget),
                $selectedOption = $select.find(':selected'),
                form = me.$el.find('form');

            $.ajax({
                method: 'POST',
                url: form.attr('data-save-url'),
                data: form.serialize(),
                dataType: 'json',
                success: function () {
                    window.location = $selectedOption.attr('data-question-url');
                }
            });
        },

        /**
         * This is triggered when a radio-element in the wizard-mode is clicked.
         * It provides the functionality to de-select a radio-button and additionally enables/disables the "next" button.
         * @param event
         */
        onClickRadioLabel: function (event) {
            var $el = $(event.currentTarget),
                input = $('#' + $el.attr('for'));

            event.preventDefault();

            if (input.is(':checked')) {
                input.prop('checked', false);
                return;
            }
            input.prop('checked', true);
        },

        /**
         * Is called when the plugin gets destroyed
         */
        destroy: function () {
            this._destroy();
        }
    });

    $(function () {
        StateManager.addPlugin('*[data-advisor-wizard=true]', 'swProductAdvisorWizard');
    });
})(jQuery);