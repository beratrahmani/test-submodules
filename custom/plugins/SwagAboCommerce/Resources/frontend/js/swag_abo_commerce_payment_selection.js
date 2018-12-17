;(function ($, window) {
    'use strict';

    $.plugin('swAboPaymentSelection', {

        defaults: {
            /**
             * Class to indicate an element to be hidden.
             *
             * @property hiddenClass
             * @type {String}
             */
            hiddenClass: 'is--hidden',

            /**
             * Class to indicate that an element has an error.
             *
             *
             * @type {String}
             */
            errorClass: 'has--error',

            /**
             * Element selector for error message container
             *
             * @property errorContainerSelector
             * @type {String}
             */
            errorContainerSelector: '.abo-payment-selection-error',

            /**
             * Selector for a input field.
             *
             * @property inputSelector
             * @type {String}
             */
            inputSelector: '.is--required',

            /**
             * @string _name
             */
            _name: 'aboPaymentSelection',

            /**
             * @string
             */
            "abo-payment-selection-url": '',

            /**
             * @string
             */
            "abo-id": '',

            /**
             * @string
             */
            "abo-payment-id": '',

            /**
             * Form selector for each payment method
             *
             * @string formSelector
             */
            formSelector: '.abo-commerce-payment--selection-form',

            /**
             * Selector for the payment field set.
             *
             * @property paymentFieldSelector
             * @type {String}
             */
            paymentFieldSelector: '.payment--content',

            /**
             * Selector for the payment method select fields.
             *
             * @property paymentMethodSelector
             * @type {String}
             */
            paymentMethodSelector: '.payment--method',

            /**
             * Selector for the payment selection radio button.
             *
             * @property paymentInputSelector
             * @type {String}
             */
            paymentInputSelector: '.payment--selection-input input',

            /**
             * Selector for the forms submit button.
             *
             * @property submitBtnSelector
             * @type {String}
             */
            submitBtnSelector: '.abo-payment-selection-button',

            /**
             * Width of the selection
             *
             * @string width
             */
            width: '80%',

            /**
             * Height of the selection
             *
             * @string height
             */
            height: '80%',

            /**
             * Modal sizing
             *
             * @string sizing
             */
            sizing: 'content'
        },

        /**
         * Initializes the plugin
         *
         * @returns {Plugin}
         */
        init: function() {
            var me = this;

            me.applyDataAttributes(true);

            me._on(me.$el, 'click', $.proxy(me.onClick, me));

            $.publish('plugin/swAboPaymentSelection/onRegisterEvents', [ me ]);
        },

        /**
         * Click callback
         * @param event
         */
        onClick: function(event) {
            var me = this;

            event.preventDefault();

            me.open(me.opts);
        },

        /**
         *
         * @param options
         */
        open: function(options) {
            var me = this,
                sizing,
                maxHeight = 0;

            me.opts = $.extend({}, me.defaults, options);

            sizing = me.opts.sizing;

            me._previousOptions = Object.create(me.opts);

            if (window.StateManager._getCurrentDevice() === 'mobile') {
                sizing = 'auto';
            } else {
                maxHeight = me.opts.height;
            }

            // reset modal
            $.modal.close();
            $.loadingIndicator.open();

            $.publish('plugin/swAboPaymentSelection/onBeforePaymentMethodsFetch', [ me ]);

            // Ajax request to fetch available addresses
            $.ajax({
                'url': me.opts["abo-payment-selection-url"],
                'data': {
                    id: me.opts.id,
                    subscriptionId: me.opts["abo-id"],
                    selectedPaymentId: me.opts["abo-payment-id"]
                },
                'success': function(data) {
                    $.loadingIndicator.close(function() {
                        $.subscribe(me.getEventName('plugin/swModal/onOpen'), $.proxy(me._onSetContent, me));

                        $.modal.open(data, {
                            width: me.opts.width,
                            maxHeight: maxHeight,
                            sizing: sizing
                        });

                        CSRF.updateForms();

                        $.unsubscribe(me.getEventName('plugin/swModal/onOpen'));
                    });

                    $.publish('plugin/swAboPaymentSelection/onPaymentMethodFetchSuccess', [ me, data ]);
                }
            });
        },

        /**
         * Re-register plugins to enable them in the modal
         * @private
         */
        _registerPlugins: function() {
            window.StateManager
                .addPlugin('*[data-panel-auto-resizer="true"]', 'swPanelAutoResizer')
                .addPlugin('*[data-preloader-button="true"]', 'swPreloaderButton');

            $.publish('plugin/swAboPaymentSelection/onRegisterPlugins', [ this ]);
        },

        _onSetContent: function(event, $modal) {
            var me = this;

            me._registerPlugins();

            me.$inputs = $($modal._$content).find(me.opts.inputSelector);
            me.$paymentMethods = $($modal._$content).find(me.opts.paymentMethodSelector);
            me.$form = $($modal._$content).find(me.opts.formSelector);
            me.$submitBtn = $($modal._$content).find(me.opts.submitBtnSelector);

            me._on(me.$submitBtn, 'click', $.proxy(me.onSubmitBtn, me));
            me._on(me.$paymentMethods, 'change', $.proxy(me.onPaymentChanged, me));
            me._on(me.$form, 'focusout', $.proxy(me.onValidateInput, me));
        },

        /**
         * Called when another payment method was selected.
         * Depending on the selection, the payment field set will be toggled.
         *
         * @public
         * @method onPaymentChanged
         */
        onPaymentChanged: function () {
            var me = this,
                opts = me.opts,
                inputClass = opts.inputSelector,
                hiddenClass = opts.hiddenClass,
                inputSelector = opts.paymentInputSelector,
                paymentSelector = opts.paymentFieldSelector,
                errorElement = $(opts.errorContainerSelector),
                requiredMethod,
                $fieldSet,
                isChecked,
                radio,
                $el;

            errorElement.html('');

            $.each(me.$paymentMethods, function (index, el) {
                $el = $(el);

                radio = $el.find(inputSelector);
                isChecked = radio[0].checked;

                requiredMethod = (isChecked) ? me.setHtmlRequired : me.removeHtmlRequired;

                requiredMethod($el.find(inputClass));

                $fieldSet = $el.find(paymentSelector);
                $fieldSet[((isChecked) ? 'removeClass' : 'addClass')](hiddenClass);
            });

            $.publish('plugin/swAboPaymentSelection/onPaymentChanged', [ me ]);
        },

        /**
         * Will be called when the submit button was clicked.
         * Loops through all input fields and checks if they have a value.
         * When no value is available, the field will be marked with an error.
         *
         * @public
         * @method onSubmitBtn
         */
        onSubmitBtn: function(event) {
            var me = this,
                errors = false,
                $input;

            event.preventDefault();

            me.$inputs.each(function () {
                $input = $(this);

                if (!$($input).is(":hidden") && !$input.val()) {
                    me.setFieldAsError($input);
                    errors = true;
                }
            });

            if (!errors) {
                $.ajax({
                    method: 'POST',
                    url: me.$submitBtn.attr('data-handle-payment-selection-url'),
                    data: me.$form.serialize(),
                    success: function(response) {
                        if (response.errors.length > 0) {
                            $(me.opts.submitBtnSelector).removeAttr('disabled');
                            me.appendErrors(response.errors, response.errorFlag);
                        } else {
                            me.$form.submit();
                        }
                    }
                });
            }

            $.publish('plugin/swAboPaymentSelection/onSubmitButton', [ me ]);
        },

        appendErrors: function(errors, errorFlag) {
            var me = this,
                errorElement = $(me.opts.errorContainerSelector),
                messageContainer,
                iconElement,
                listElement;

            messageContainer = $('<div>', {
                'class': 'alert is--error is--rounded',
                'html': '<div class="alert is--error"></div>'
            }).appendTo(errorElement);

            iconElement = $('<div>', {
                'class': 'alert--icon',
                'html': '<i class="icon--element icon--cross"></i>'
            }).appendTo(messageContainer);

            listElement = $('<ul>', {
                'class': 'alert--list'
            }).appendTo(messageContainer);

            $.each(errors, function(index, error) {
                 $('<li>', {
                     'class': 'list--entry',
                     'html': '<div class="alert--content">' + error + '</div>'
                 }).appendTo(listElement)
            });

            $.each(errorFlag, function(index, error) {
                var $field = $("input[name=" + index + "]");
                me.setFieldAsError($field);
            });
        },

        /**
         * Adds additional attributes to the given elements to indicate
         * the elements to be required.
         *
         * @private
         * @method setHtmlRequired
         * @param {jQuery} $elements
         */
        setHtmlRequired: function ($elements) {
            $elements.attr({
                'required': 'required',
                'aria-required': 'true'
            });

            $.publish('plugin/swAboPaymentSelection/onSetHtmlRequired', [ this, $elements ]);
        },

        /**
         * Removes addition attributes that indicate the input as required.
         *
         * @public
         * @method removeHtmlRequired
         * @param {jQuery} $inputs
         */
        removeHtmlRequired: function ($inputs) {
            $inputs.removeAttr('required aria-required');

            $.publish('plugin/swAboPaymentSelection/onRemoveHtmlRequired', [ this, $inputs ]);
        },

        /**
         * Called when a input field lost its focus.
         * Depending on the elements id, the corresponding method will be called.
         * billing ust id, emails and passwords will be validated via AJAX.
         *
         * @public
         * @method onValidateInput
         * @param {jQuery.Event} event
         */
        onValidateInput: function (event) {
            var me = this,
                $el = $(event.target),
                id = $el.attr('id'),
                action,
                relatedTarget = event.relatedTarget || document.activeElement;

            me.$targetElement = $(relatedTarget);

            if (!$el.val() && $el.attr('required')) {
                me.setFieldAsError($el);
            } else {
                me.setFieldAsSuccess($el);
            }

            $.publish('plugin/swAboPaymentSelection/onValidateInput', [ me, event, action ]);
        },

        /**
         * Adds the defined error class to the given field.
         *
         * @public
         * @method setFieldAsError
         * @param {jQuery} $el
         */
        setFieldAsError: function ($el) {
            var me = this;

            $el.addClass(me.opts.errorClass);

            $.publish('plugin/swAboPaymentSelection/onSetFieldAsError', [ me, $el ]);
        },

        /**
         * Removes the defined error class from the given field.
         *
         * @public
         * @method setFieldAsSuccess
         * @param {jQuery} $el
         */
        setFieldAsSuccess: function ($el) {
            var me = this;

            $el.removeClass(me.opts.errorClass);

            $.publish('plugin/swAboPaymentSelection/onSetFieldAsSuccess', [ me, $el ]);
        },

        /**
         * add namespace for events
         * @param event
         * @returns {string}
         *
         */
        getEventName: function(event) {
            var me = this;

            return event + '.' + me._name;
        }
    });

    StateManager.addPlugin('*[data-abo-payment-selection="true"]', 'swAboPaymentSelection');
})(jQuery, window);