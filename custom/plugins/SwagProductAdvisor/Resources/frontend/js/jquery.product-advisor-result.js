;(function ($) {
    'use strict';

    $.plugin('swProductAdvisorResult', {

        defaults: {
            /**
             * This is the selector for the "others"-title in between the product-boxes.
             * It is NOT the title on the top of the page.
             * @type string
             */
            othersTitleSelector: '.advisor--others-title',

            /**
             * This is the selector for the "others" title on top of the page.
             * It gets replaced when the title appears in between the product-boxes.
             * @type string
             */
            mainTitleOthersSelector: '.main-title--others',

            /**
             * This is the selector for the "remaining"-title on top of the page.
             * @type string
             */
            mainTitleFilteredSelector: '.main-title--filtered'
        },

        /**
         * Initializes the necessary events and applies the data-attributes.
         */
        init: function () {
            var me = this;

            me.applyDataAttributes();
            me.registerEvents();
        },

        /**
         * Register all necessary events.
         */
        registerEvents: function () {
            var me = this;
            $.subscribe(me.getEventName('plugin/swInfiniteScrolling/onLoadPreviousFinished'), $.proxy(me.onLoadPreviousFinished, me));
        },

        /**
         * When the main-title has been replaced with the "others" title and you browse previous pages,
         * the title might appear in between the products itself.
         * Therefore we need to remove it from the "main"-title and show the "remaining"-title instead.
         * @param event
         * @param plugin
         */
        onLoadPreviousFinished: function (event, plugin) {
            var me = this;

            // If the "others"-title also appeared in between the products
            if (plugin.$el.find(me.opts.othersTitleSelector).length) {
                me.$el.find(me.opts.mainTitleOthersSelector).remove();
                me.$el.find(me.opts.mainTitleFilteredSelector).show();
            }
        }
    });

    /**
     * Removes the "action"-property from the infinite-scrolling plugin params.
     * This is necessary to prevent the infinite-scrolling plugin to attach ?action=result to the ajax-url.
     */
    $.subscribe('plugin/swInfiniteScrolling/onInit', function (event, plugin) {
        if (!plugin.params) {
            return;
        }
        delete plugin.params.action;
        delete plugin.upperParams.action;
    });

    $(function () {
        StateManager.addPlugin('*[data-advisor-result=true]', 'swProductAdvisorResult');
    });
})(jQuery);