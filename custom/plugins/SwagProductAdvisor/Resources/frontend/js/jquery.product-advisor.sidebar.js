;(function ($) {
    'use strict';

    $.plugin('swProductAdvisorSidebar', {

        defaults: {

            /**
             * This contains the number of answers that must be given before
             * the submit-button on the sidebar mode becomes enabled.
             * @type int
             */
            minQuestions: 0,

            /**
             * This is the selector for the container, that has to be clicked to toggle the collapsing
             * of the question ct.
             * @type string
             */
            collapsibleCtSelector: '.question-ct--question-name',

            /**
             * This selector will find all radio-inputs from any non-price question.
             * It will be used to listen for a change and to find checked radio-inputs.
             * @type string
             */
            defaultRadioSelector: '.filter--advisor-radio',

            /**
             * This selector will find all checkbox-inputs from any question.
             * It will be used to listen for a change and to find checked checkbox-inputs.
             * @type string
             */
            checkBoxSelector: '.filter--advisor-checkbox',

            /**
             * This selector will find all non-price select-inputs itself, it won't select the options.
             * It is used for listening to a change and to find the selected option afterwards.
             * @type string
             */
            defaultComboBoxSelector: '.advisor--combobox',

            /**
             * This selector is used to find the hidden input from a price range-slider,
             * which contains the minimal selected price.
             * It is used to listen for a change on the sliders.
             * @type string
             */
            priceMinInputSelector: '.advisor--price-min',

            /**
             * This selector is used to find the hidden input from a price range-slider,
             * which contains the maximum selected price.
             * It is used to listen for a change on the sliders.
             * @type string
             */
            priceMaxInputSelector: '.advisor--price-max',

            /**
             * This selector is nearly equal to the radio-input selector 'defaultRadioSelector',
             * but will only find radio-inputs being part of a price-question.
             * This is used to listen for a change on the inputs and to find the checked radio-input afterwards.
             * @type string
             */
            priceRadioSelector: '.filter--advisor-price-radio',

            /**
             * This selector is nearly equal to the select-input selector 'defaultComboBoxSelector',
             * but will only find select-inputs being part of a price-question.
             * This is used to listen for a change on the inputs and to find the selected option afterwards.
             * @type string
             */
            priceComboBoxSelector: '.advisor--price-combobox',

            /**
             * This is the selector of the container, which contains the arrow-icon.
             * This container will turn around for 180 degrees if it also has the class from the
             * "questionArrowCtSelector"-option.
             * @type string
             */
            questionArrowCtSelector: '.question-name--arrow',

            /**
             * This is the class to turn around the arrow upon toggling the collapse of the question-container.
             * @type string
             */
            turnArrowClass: 'advisor--turn-arrow',

            /**
             * This is the selector for the container to be collapsed.
             * @type string
             */
            questionAnswerCtSelector: '.question-ct--answer-ct',

            /**
             * This is the selector for the search-button in the sidebar of the sidebar-mode.
             * @type string
             */
            sidebarSearchButtonSelector: '.advisor--submit-btn',

            /**
             * This is the selector for the advisor reset-button in the sidebar of the sidebar-mode.
             * @type string
             */
            sidebarResetButtonSelector: '.advisor--reset-advisor-btn',

            /**
             * This is the selector for the reset-button for a question in the sidebar.
             * @type string
             */
            resetQuestionButtonSelector: '.advisor--reset-btn',

            /**
             * This is the selector for the form wrapping around all questions in the sidebar.
             * @type string
             */
            sideBarFormSelector: '.sidebar--questions-form',

            /**
             * This is the selector for all the required checkboxes in the sidebar.
             * @type string
             */
            requiredCheckboxesSelector: '.answers--checkbox-wrapper.required',

            /**
             * The class for the disabled-state of the reset-button.
             * @type string
             */
            resetButtonDisabledClass: 'is--disabled',

            /**
             * The selector for the sidebar-question-container.
             * @type string
             */
            sidebarQuestionCtSelector: '.advisor--question-ct',

            /**
             * The selector for the container which wraps around the "minimum-answers necessary"-warning.
             * @type string
             */
            minimumAnswersCtSelector: '.sidebar-buttons--warning',

            /**
             * This is the selector for the minimum-answer warning itself.
             * @type string
             */
            minimumAnswersWarningSelector: '.sidebar-buttons--minimum-answers',

            /**
             * The selector for the anchor to be scrolled to when opening the result in a smaller viewport.
             * Should be an id, as it needs to be unique.
             * @type string
             */
            listingAnchorSelector: '#advisor-listing--container',

            /**
             * The default scroll-speed for the scroll-animation in smaller viewports.
             * @type number
             */
            defaultScrollSpeed: 750,

            /**
             * The timeout until the scroll-animation in smaller viewports begins.
             * @type number
             */
            scrollTimeout: 750
        },

        /**
         * Initializes the necessary events, applies the data-attributes and additionally initializes the global-variables.
         */
        init: function () {
            var me = this;

            me.applyDataAttributes(false);

            /**
             * This will contain all answers given.
             * It is used for counting the answers in order to enable/disable the buttons.
             *
             * @private
             * @property answers
             * @type {Object}
             */
            me.answers = {};

            /**
             * Contains the submit-button of the sidebar to trigger the product-search.
             * This is necessary to enable/disable the button.
             *
             * @private
             * @property sidebarSearchButton
             * @type {jQuery}
             */
            me.sidebarSearchButton = me.$el.find(me.opts.sidebarSearchButtonSelector);

            /**
             * Contains the advisor reset-button of the sidebar.
             * This is necessary to enable/disable the button and to prevent the default action if
             * the button should be disabled.
             *
             * @private
             * @property sidebarResetButton
             * @type {jQuery}
             */
            me.sidebarResetButton = me.$el.find(me.opts.sidebarResetButtonSelector);

            /**
             * Contains the container for the minimum-answer-warning.
             * It will be used to dynamically hide/show the warning and to update the amount of missing minimum
             * questions.
             *
             * @private
             * @property minimumAnswersMessage
             * @type {jQuery}
             */
            me.minimumAnswersMessage = me.$el.find(me.opts.minimumAnswersCtSelector);

            me.initScroll();
            me.initAnswers();
            me.registerEvents();
        },

        /**
         * Will register all necessary events for this plugin.
         */
        registerEvents: function () {
            var me = this,
                opts = me.opts,
                $defaultComboBox = me.$el.find(opts.defaultComboBoxSelector);

            me._on(me.$el.find(opts.collapsibleCtSelector), 'click', $.proxy(me.onCollapseQuestion, me));
            me._on(me.$el.find(opts.resetQuestionButtonSelector), 'click', $.proxy(me.onClickResetButton, me));
            me._on(me.sidebarResetButton, 'click', $.proxy(me.onClickAdvisorResetButton, me));

            me._on(me.$el.find(opts.defaultRadioSelector), 'change', $.proxy(me.onChangeRadio, me));
            me._on(me.$el.find(opts.checkBoxSelector), 'change', $.proxy(me.onChangeCheckbox, me));
            me._on(me.$el.find(opts.priceRadioSelector), 'change', $.proxy(me.onChangePriceRadio, me));
            me._on(me.$el.find(opts.priceComboBoxSelector), 'change', $.proxy(me.onChangePriceComboBox, me));
            me._on(me.$el.find(opts.priceMinInputSelector), 'change', $.proxy(me.onChangePriceSlider, me));
            me._on(me.$el.find(opts.priceMaxInputSelector), 'change', $.proxy(me.onChangePriceSlider, me));
            me._on($defaultComboBox, 'change', $.proxy(me.onChangeComboBox, me));

            // iOS workaround. The change event on a select on iOS is triggered too early, so the "checkFormValidity-method
            // fails and prevents the button from becoming enabled
            me._on($defaultComboBox, 'blur', $.proxy(me.checkStatus, me));
        },

        /**
         * Initializes the scroll-animation on the-result page.
         */
        initScroll: function() {
            var me = this,
                scrollAnimated = false,
                timeout;

            if (StateManager.isCurrentState('xs') || StateManager.isCurrentState('s')) {
                timeout = window.setTimeout(function() {
                    scrollAnimated = true;
                    me.scrollToElement(me.opts.listingAnchorSelector);
                    window.clearTimeout(timeout);
                }, me.opts.scrollTimeout);

                $(window).one('scroll', function() {
                    window.clearTimeout(timeout);
                });
            }
        },

        /**
         * Scrolls to the given selector.
         *
         * @param selector
         * @param scrollSpeed
         * @returns {boolean}
         */
        scrollToElement: function (selector, scrollSpeed) {
            var me = this;

            scrollSpeed = scrollSpeed || me.opts.defaultScrollSpeed;
            if (!selector) {
                return false;
            }

            $('html, body').animate({
                scrollTop: $(selector).offset().top
            }, scrollSpeed);

            return true;
        },

        /**
         * Event listener which is called when clicking on the advisor-reset-button.
         * Will prevent the resetting if the button is supposed to be disabled.
         * @param event
         */
        onClickAdvisorResetButton: function (event) {
            var me = this,
                $element = $(event.currentTarget);

            if ($element.hasClass(me.opts.resetButtonDisabledClass)) {
                event.preventDefault();
            }
        },

        /**
         * Event listener which is called after selecting a radio-item of a non-price question.
         * @param {Object} event
         */
        onChangeRadio: function (event) {
            this.addRadioAnswer($(event.currentTarget));
        },

        /**
         * Event listener which is called after selecting any checkbox-item.
         * @param {Object} event
         */
        onChangeCheckbox: function (event) {
            this.addCheckboxAnswer($(event.currentTarget));
        },

        /**
         * Event listener which is called after selecting a select-option of a non-price question.
         * @param {Object} event
         */
        onChangeComboBox: function (event) {
            this.addComboBoxAnswer($(event.currentTarget));
        },

        /**
         * Event listener which is called after selecting a radio-item of a price question.
         * @param {Object} event
         */
        onChangePriceRadio: function (event) {
            this.addPriceRadioAnswer($(event.currentTarget));
        },

        /**
         * Event listener which is called after selecting a select-option of a price question.
         * @param {Object} event
         */
        onChangePriceComboBox: function (event) {
            this.addPriceComboAnswer($(event.currentTarget));
        },

        /**
         * Event listener which is called after using any of the price-slider handles.
         * @param {Object} event
         */
        onChangePriceSlider: function (event) {
            this.addPriceSliderAnswer($(event.currentTarget));
        },

        /**
         * Event listener which is called upon clicking the "questionArrowCtSelector"-container.
         * It will toggle the collapsing of the own question-container.
         * @param {Object} event
         */
        onCollapseQuestion: function (event) {
            var me = this,
                $el = $(event.currentTarget),
                opts = me.opts;

            $el.find(opts.questionArrowCtSelector).toggleClass(opts.turnArrowClass);
            $el.next(opts.questionAnswerCtSelector).slideToggle();
        },

        /**
         * This is triggered when the reset-button for question is clicked .
         * @param event
         */
        onClickResetButton: function (event) {
            var me = this,
                $el = $(event.currentTarget);

            me.resetQuestion($el);
        },

        /**
         * Helper method to add a radio-input answer to the answers-array.
         * @param {Object} $target The radio-button itself
         */
        addRadioAnswer: function ($target) {
            var me = this;
            me.addAnswer($target.attr('name'), $target.val(), true);
            me.showResetButton($target);
        },

        /**
         * Helper method to add a checkbox-answer to the answers-array.
         * This will also handle deselecting an answer again.
         * @param {Object} $target The checkbox itself
         * @return void
         */
        addCheckboxAnswer: function ($target) {
            var me = this,
                name = $target.attr('name'),
                val = $target.val();

            // Cut off the last 2 characters, because checkbox-name is something like q1_values_1 and q1_values_2
            name = name.substring(0, name.length - 2);

            if (!$target.is(':checked')) {
                me.removeAnswer(name, val);
                return;
            }

            me.addAnswer(name, val);
            me.showResetButton($target);
        },

        /**
         * Helper method to add a select-answer to the answers-array.
         * @param {Object} $target The combo-box itself, not the selected option
         */
        addComboBoxAnswer: function ($target) {
            var me = this;
            me.addAnswer($target.attr('name'), $target.val(), true);
            me.showResetButton($target);
        },

        /**
         * Helper method to add a price-answer from a price-radio question to the answers-array.
         * @param {Object} $target The price-radio button itself
         */
        addPriceRadioAnswer: function ($target) {
            var me = this;
            me.addPriceAnswer($target.attr('name'), 'max', $target.val());
            me.showResetButton($target);
        },

        /**
         * Helper method to add a price-answer from a price-select question to the answers-array.
         * @param {Object} $target The price-combo-box itself, not a selected option
         */
        addPriceComboAnswer: function ($target) {
            var me = this;
            me.addPriceAnswer($target.attr('name'), 'max', $target.val());
            me.showResetButton($target);
        },

        /**
         * Helper method to add a price-answer from a range-slider to the answers-array.
         * @param {Object} $target The price-slider-handle itself (min or max)
         */
        addPriceSliderAnswer: function ($target) {
            this.addPriceAnswer($target.attr('data-answer'), $target.attr('name'), $target.val());
        },

        /**
         * Helper method to show the reset-button in the sidebar.
         * This is called when at least one answer is given.
         * @param {Object} $element The element being changed
         */
        showResetButton: function ($element) {
            var me = this;
            $element.parents(me.opts.questionAnswerCtSelector).find(me.opts.resetQuestionButtonSelector).show();
        },

        /**
         * This method is called upon initializing the plugin.
         * It will pre-fill the answers-array with the already given answers, if any are given.
         */
        initAnswers: function () {
            var me = this,
                opts = me.opts,
                $radioDefaultEl = me.$el.find(opts.defaultRadioSelector).filter(':checked'),
                $checkBoxEl = me.$el.find(opts.checkBoxSelector).filter(':checked'),
                $defaultComboBox = me.$el.find(opts.defaultComboBoxSelector),
                $defaultComboBoxEl = $defaultComboBox.find(':selected:not(.advisor--empty-text)'),
                $radioPriceEl = me.$el.find(opts.priceRadioSelector).filter(':checked'),
                $priceComboBox = me.$el.find(opts.priceComboBoxSelector),
                $priceComboBoxEl = $priceComboBox.find(':selected:not(.advisor--empty-text)'),
                $minInput = $(opts.priceMinInputSelector),
                $maxInput = $(opts.priceMaxInputSelector);

            $radioDefaultEl.each(function () {
                me.addRadioAnswer($(this));
            });

            $checkBoxEl.each(function () {
                me.addCheckboxAnswer($(this));
            });

            $defaultComboBoxEl.each(function () {
                me.addComboBoxAnswer($(this).parents(opts.defaultComboBoxSelector));
            });

            $priceComboBoxEl.each(function () {
                me.addPriceComboAnswer($(this));
            });

            $radioPriceEl.each(function () {
                me.addPriceRadioAnswer($(this));
            });

            if ($minInput.length > 0 && $minInput.val() != '') {
                me.addPriceSliderAnswer($minInput);
            }

            if ($maxInput.length > 0 && $maxInput.val() != '') {
                me.addPriceSliderAnswer($maxInput);
            }

            me.checkStatus();
        },

        /**
         * Helper method to fill the answer-array with default-answers.
         * @param {string} answerName The name of the answer
         * @param {string} value The actual value
         * @param {boolean} isSingle If an answer should be overwritten (e.g. not needed for combobox)
         */
        addAnswer: function (answerName, value, isSingle) {
            var me = this,
                answerArr = me.answers[answerName];

            /**
             * This prevents an issue with the combo-box-reset.
             * When resetting an combo-box, it will trigger the change-event and therefore add a new answer,
             * which in this case has the value "null".
             */
            if (value === null) {
                return;
            }

            if (!answerArr) {
                answerArr = me.answers[answerName] = [];
            }

            // Overwrite older values
            if (isSingle) {
                answerArr = [];
            }

            if (answerArr.indexOf(value) === -1) {
                answerArr.push(value);
            }
            me.answers[answerName] = answerArr;

            me.checkStatus();
        },

        /**
         * Helper method to fill the answer-array with price-answers.
         * @param {string} answerName The name of the answer
         * @param {string} key 'min' or 'max'
         * @param {string} value The actual value
         */
        addPriceAnswer: function (answerName, key, value) {
            var me = this,
                answerObj = me.answers[answerName];

            if (!answerObj) {
                answerObj = {};
            }

            answerObj[key] = value;
            me.answers[answerName] = answerObj;

            me.checkStatus();
        },

        /**
         * Helper method to remove an answer again.
         * E.g. needed for a combobox-value.
         * @param {string} answerName
         * @param {string} value
         */
        removeAnswer: function (answerName, value) {
            var me = this,
                answerArr = me.answers[answerName],
                index;

            if (!answerArr) {
                return;
            }

            if ((index = answerArr.indexOf(value)) !== -1) {
                answerArr.splice(index, 1);
            }

            if (!answerArr.length && me.answers.hasOwnProperty(answerName)) {
                delete me.answers[answerName];
                me.findResetButtonByValue(value).hide();
            }

            me.checkStatus();
        },

        /**
         * Helper method to remove a price-answer.
         * @param {string} answerName
         */
        removePriceAnswer: function (answerName) {
            var me = this,
                answerObj = me.answers[answerName];

            if (!answerObj) {
                return;
            }

            if (!answerObj.length && me.answers.hasOwnProperty(answerName)) {
                delete me.answers[answerName];
            }

            me.checkStatus();
        },

        /**
         * Helper method to check if the form is actually valid, considering required-fields.
         */
        checkFormValidity: function () {
            var me = this,
                $form = me.$el.find(me.opts.sideBarFormSelector);

            return me.checkCheckboxValidity() && $form.get(0).checkValidity();
        },

        /**
         * Helper method to check if all required checkbox-groups are valid.
         */
        checkCheckboxValidity: function () {
            var me = this,
                requiredCheckboxWrappers = me.$el.find(me.opts.requiredCheckboxesSelector),
                valid = true;

            requiredCheckboxWrappers.each(function (i, item) {
                var $item = $(item);

                if ($item.find(':checkbox:checked').length <= 0) {
                    valid = false;
                }
            });

            return valid;
        },

        /**
         * This method checks if a button should be enabled or disabled due to the count of the answers.
         */
        checkStatus: function () {
            var me = this;
            me.checkForMinimumAnswers();
            me.checkForResetButton();

            if (Object.keys(me.answers).length >= me.opts.minQuestions && me.checkFormValidity()) {
                me.enableButton();
                return;
            }

            me.disableButton();
        },

        /**
         * Checks if the "minimum-answers" warning should be shown.
         */
        checkForMinimumAnswers: function () {
            var me = this;

            if (Object.keys(me.answers).length >= me.opts.minQuestions) {
                me.hideMessage();
                return;
            }

            me.showMessage();
        },

        /**
         * Simply hides the "minimum-answers" warning.
         */
        hideMessage: function () {
            var me = this;

            me.minimumAnswersMessage.hide();
        },

        /**
         * Shows and updates the "minimum-answers" warning.
         */
        showMessage: function() {
            var me = this,
                minimumAnswerWarning = me.minimumAnswersMessage.find(me.opts.minimumAnswersWarningSelector),
                difference = me.opts.minQuestions - Object.keys(me.answers).length;

            minimumAnswerWarning.html(difference);

            me.minimumAnswersMessage.show();
        },

        /**
         * Helper method to enable the submit-button.
         */
        enableButton: function () {
            this.sidebarSearchButton.removeAttr('disabled');
        },

        /**
         * Helper method to disable the submit button.
         */
        disableButton: function () {
            this.sidebarSearchButton.attr('disabled', 'disabled');
        },

        /**
         * Checks if the general reset-button should be enabled/disabled depending on the amount of answers given
         */
        checkForResetButton: function () {
            var me = this;
            if (Object.keys(me.answers).length >= 1) {
                me.enableResetButton();
                return;
            }
            me.disableResetButton();
        },

        /**
         * Enables the general reset-button
         */
        enableResetButton: function () {
            this.sidebarResetButton.removeClass('is--disabled');
        },

        /**
         * Disables the general reset-button
         */
        disableResetButton: function () {
            this.sidebarResetButton.addClass('is--disabled');
        },

        /**
         * Helper method to reset a single question.
         * @param {Object} $element
         */
        resetQuestion: function ($element) {
            var me = this,
                questionCt = $element.parents(me.opts.sidebarQuestionCtSelector),
                template = $element.attr('data-question-template'),
                type = $element.attr('data-question-type'),
                inputs;

            switch (template) {
                case 'radio':
                    inputs = questionCt.find('input[type=radio]:checked');
                    inputs.each(function (index, element) {
                        var $inputElement = $(element);

                        $inputElement.prop('checked', false);

                        if (type === 'price') {
                            me.removePriceAnswer($inputElement.attr('name'));
                            return;
                        }
                        me.removeAnswer($inputElement.attr('name'), $inputElement.val());
                    });
                    break;
                case 'checkbox':
                    inputs = questionCt.find('input[type=checkbox]:checked');
                    inputs.each(function (index, element) {
                        var $inputElement = $(element);
                        var name = $inputElement.attr('name');
                        $inputElement.prop('checked', false);
                        name = name.substring(0, name.length - 2);
                        me.removeAnswer(name, $inputElement.val());
                    });
                    break;
                case 'combobox':
                    var select = questionCt.find('select'),
                        selectedOption = select.find(':selected');

                    select.prop('selectedIndex', 0).change();
                    if (type === 'price') {
                        me.removePriceAnswer(select.attr('name'));
                        return;
                    }

                    me.removeAnswer(select.attr('name'), selectedOption.val());
                    break;
            }
            $element.hide();
        },

        /**
         * Finds the reset-button by the value of an input.
         *
         * @param {string} value
         */
        findResetButtonByValue: function (value) {
            var me = this,
                selectedInput = me.$el.find('input[value=' + value + ']'),
                answerCt = selectedInput.parents(me.opts.questionAnswerCtSelector);

            return answerCt.find(me.opts.resetQuestionButtonSelector);
        },

        /**
         * Is called when the plugin gets destroyed
         */
        destroy: function () {
            this._destroy();
        }
    });

    /**
     * Small plugin to read the modal-content from a container instead of a data-attribute.
     */
    $.plugin('swAdvisorModalBox', {

        defaults: {

            /**
             * Optional selector for a container to contain the content for the modal box.
             *
             * @property contentSelector
             * @type {string}
             */
            contentSelector: '.additional-info--hidden-info',

            /**
             * The window title of the modal box.
             * If empty, the header will be hidden.
             *
             * @property title
             * @type {string}
             */
            title: '',

            /**
             * Optional class you can add to the modal-box.
             *
             * @property additionalClass
             * @type {string}
             */
            additionalClass: 'question-ct--info-modal'
        },

        /**
         * Initializes the necessary event for the plugin
         */
        init: function () {
            var me = this;

            me.applyDataAttributes();

            me._on(me.$el, 'click', $.proxy(me.onClick, me));
        },

        /**
         * Opens the modal-box on click.
         * @param event
         */
        onClick: function (event) {
            var me = this;
            event.preventDefault();

            $.modal.open(me.$el.find(me.opts.contentSelector).html(), {
                mode: 'local',
                sizing: 'auto',
                additionalClass: me.opts.additionalClass,
                title: me.opts.title
            });
        }
    });

    $(function () {
        StateManager.addPlugin('*[data-advisor-sidebar=true]', 'swProductAdvisorSidebar');
        StateManager.addPlugin(
            '.question-ct--additional-info',
            'swAdvisorModalBox',
            {},
            ['xl', 'l', 'm']
        );

        StateManager.addPlugin(
            '.question-ct--additional-info',
            'swOffcanvasMenu',
            {
                fullscreen: true,
                direction: 'fromRight',
                closeButtonSelector: '.close--off-canvas',
                offCanvasSelector: '.question-ct--off-canvas-info'
            },
            ['s', 'xs']
        );
    });
})(jQuery);