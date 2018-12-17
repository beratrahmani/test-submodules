;(function ($, window) {
    'use strict';

    $.plugin('swAboAddressSelection', {

        defaults: {
            /**
             * @string _name
             */
            _name: 'aboAddressSelection',

            "abo-selected-address": "",

            /**
             * @string
             */
            "abo-address-selection-url": '',

            /**
             * @string
             */
            "abo-id": '',

            /**
             * @string
             */
            "abo-address-type": '',

            /**
             * Form selector for each address
             *
             * @string formSelector
             */
            formSelector: '.address-manager--selection-form',

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

            $.publish('plugin/swAboAddressSelection/onRegisterEvents', [ me ]);
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

            $.publish('plugin/swAboAddressSelection/onBeforeAddressFetch', [ me ]);

            // Ajax request to fetch available addresses
            $.ajax({
                'url': me.opts["abo-address-selection-url"],
                'data': {
                    selectedAddress: me.opts["abo-selected-address"],
                    subscriptionId: me.opts["abo-id"],
                    subscriptionAddressType: me.opts["abo-address-type"]
                },
                'success': function(data) {
                    $.loadingIndicator.close(function() {
                        $.subscribe(me.getEventName('plugin/swModal/onOpen'), $.proxy(me._onSetContent, me));

                        $.modal.open(data, {
                            width: me.opts.width,
                            maxHeight: maxHeight,
                            additionalClass: 'address-manager--modal address-manager--selection',
                            sizing: sizing
                        });

                        CSRF.updateForms();

                        $.unsubscribe(me.getEventName('plugin/swModal/onOpen'));
                    });

                    $.publish('plugin/swAboAddressSelection/onAddressFetchSuccess', [ me, data ]);
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
                .addPlugin('*[data-address-editor="true"]', 'swAddressEditor')
                .addPlugin('*[data-preloader-button="true"]', 'swPreloaderButton');

            $.publish('plugin/swAboAddressSelection/onRegisterPlugins', [ this ]);
        },

         _onSetContent: function(event, $modal) {
            var me = this;

            me._registerPlugins();
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

    StateManager.addPlugin('*[data-abo-address-selection="true"]', 'swAboAddressSelection');
})(jQuery, window);