;(function ($) {
    'use strict';

    /**
     * Emotion SideView Plugin
     *
     * This plugin handles the SideView emotion widget.
     * It handles the opening and closing of the side view slider
     * and controls the automatic scrolling of the slider plugin.
     */
    $.plugin('swSideView', {

        defaults: {

            /**
             * Turn automatic scrolling of the slider on and off.
             *
             * @property autoScroll
             * @type {Boolean}
             */
            autoScroll: false,

            /**
             * The DOM selector of the banner element.
             *
             * @property bannerSelector
             * @type {String}
             */
            bannerSelector: '.side-view--banner',

            /**
             * The DOM selector of the side view element.
             *
             * @property viewSelector
             * @type {String}
             */
            viewSelector: '.side-view--view',

            /**
             * The DOM selector for the trigger button element.
             *
             * @property triggerSelector
             * @type {String}
             */
            triggerSelector: '.side-view--trigger',

            /**
             * The DOM selector for the close button element.
             *
             * @property closerSelector
             * @type {String}
             */
            closerSelector: '.side-view--closer',

            /**
             * The DOM selector for the product slider element.
             *
             * @property sliderSelector
             * @type {String}
             */
            sliderSelector: '.product-slider',

            /**
             * The css class for the active state.
             *
             * @property activeCls
             * @type {String}
             */
            activeCls: 'is--active'
        },

        /**
         * Plugin constructor
         */
        init: function () {
            var me = this;

            me.applyDataAttributes();

            me.$banner = me.$el.find(me.opts.bannerSelector);
            me.$view = me.$el.find(me.opts.viewSelector);
            me.$trigger = me.$el.find(me.opts.triggerSelector);
            me.$closer = me.$el.find(me.opts.closerSelector);
            me.$slider = me.$el.find(me.opts.sliderSelector);

            me.slider = me.$slider.data('plugin_swProductSlider');

            me.registerEvents();
        },

        /**
         * Registers all necessary event handler.
         */
        registerEvents: function () {
            var me = this;

            me._on(me.$banner, 'click', $.proxy(me.onClick, me));
            me._on(me.$trigger, 'click', $.proxy(me.onClick, me));
            me._on(me.$closer, 'click', $.proxy(me.onClick, me));
        },

        /**
         * Event handler for the click event.
         * Opens and closes the side view.
         * Starts and stops the automatic scrolling of the slider.
         *
         * @param event
         */
        onClick: function (event) {
            var me = this;

            event.preventDefault();

            me.slider.update();
            me.$view.toggleClass(me.opts.activeCls);

            if (me.opts.autoScroll && me.$view.hasClass(me.opts.activeCls)) {
                setTimeout(function () {
                    me.slider.autoScroll();
                }, 800);
            } else {
                me.slider.stopAutoScroll();
            }

            $.publish('plugin/swSideView/onClick', [me]);
        },

        /**
         * Destroys the plugin.
         */
        destroy: function () {
            var me = this;

            me._destroy();
        }
    });
})(jQuery);
