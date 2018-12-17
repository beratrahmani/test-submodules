;(function($) {
    $.plugin('swAboTermination', {

        defaults: {

            /**
             * Text for the modal title
             */
            modalTitle: '',

            /**
             * Text for the modal content
             */
            content: '',

            /**
             * Text for the cancel button
             */
            cancelButtonText: '',

            /**
             * Text for the terminate button
             */
            terminateButtonText: '',

            /**
             * Css class(es) for the cancel button
             */
            cancelButtonCssClass: 'btn is--primary abo-left abo-bottom',

            /**
             * Css class(es) for the terminate button
             */
            terminateButtonCssClass: 'btn is--secondary abo-right abo-bottom',

            /**
             * Css class(es) for the content div
             */
            modalContentWrapperClass: 'abo-modal-content-wrapper'
        },

        /**
         * Plugin constructor
         */
        init: function() {
            var me = this;

            me.applyDataAttributes();

            me.initializeElements();
            me.registerEvents();
        },

        /**
         * Select and assign the required elements
         */
        initializeElements: function() {
            var me = this;

            me.$button = me.$el.children('a');
            me.url = me.$button.attr('href');
        },

        /**
         * Registers the required element events
         */
        registerEvents: function() {
            var me = this;

            me.$button.click($.proxy(me.onTerminationClick, me));
        },

        /**
         * Event listener for the terminate button. Opens the modal
         */
        onTerminationClick: function() {
            var me = this;

            event.preventDefault();

            me.openModal();
        },

        /**
         * Event listener for the cancel button. Close the modal
         */
        onCancelClick: function() {
            var me = this;

            event.preventDefault();

            me.$modal.close();
        },

        /**
         * @return { * | HTMLElement }
         */
        createModalContent: function() {
            var me = this,
                contentWrapper = $('<div class="' + me.opts.modalContentWrapperClass + '">'),
                content = $('<p>'),
                cancelButton = me.createButton(
                    me.opts.cancelButtonText,
                    null,
                    me.opts.cancelButtonCssClass,
                    me.onCancelClick,
                    me
                ),
                terminateButton = me.createButton(
                    me.opts.terminateButtonText,
                    me.url,
                    me.opts.terminateButtonCssClass
                );

            content.html(me.opts.content);

            contentWrapper.append(content);
            contentWrapper.append(cancelButton);
            contentWrapper.append(terminateButton);

            return contentWrapper;
        },

        /**
         * Opens the modal
         */
        openModal: function() {
            var me = this,
                content = me.createModalContent();

            me.$modal = $.modal.open(content, {
                title: me.opts.modalTitle,
                width: 400,
                height: 190
            });
        },

        /**
         * @param { string } text
         * @param { string } target
         * @param { string } cssClass
         * @param { function } callback
         * @param { object } scope
         * @return { * | HTMLElement }
         */
        createButton: function(text, target, cssClass, callback, scope) {
            var button = $('<a>');

            button.html(text);
            button.addClass(cssClass);

            if (target) {
                button.attr('href', target);
            }

            button.click($.proxy(callback, scope));

            return button;
        }
    });

    // call the plugin
    $('*[data-aboTerminationButton="true"]').swAboTermination();
})(jQuery);
