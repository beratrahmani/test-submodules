;(function($, window) {
    'use strict';

    $.plugin('swagBundleProductSelection', {

        /** The default options */
        defaults: {

            /** number */
            bundleProductId: -1,

            /** number */
            bundleId: -1
        },

        /**
         * Initializes the plugin
         */
        init: function() {
            var me = this;

            me.name = me.$el.attr('name');

            // Applies HTML data attributes to the default options
            me.applyDataAttributes();

            me.updateProperties();
            me.registerEventHandler();
            me.loadSettings();
        },

        /**
         * Collects and sets properties
         */
        updateProperties: function() {
            var me = this;

            me.storageManager = window.StorageManager.getSessionStorage();
        },

        /**
         * Registers all event listeners
         */
        registerEventHandler: function() {
            var me = this;

            me._on(me.$el, 'click', $.proxy(me.onClick, me));
        },

        /**
         * Load the settings from the storage
         */
        loadSettings: function() {
            var me = this,
                state = me.storageManager.getItem(
                    me.name
                );

            if (state === 'false') {
                me.$el.trigger('click');
            }
        },

        /**
         * On click on me.$el (Checkbox) collects necessary data and publish the change event
         */
        onClick: function() {
            var me = this,
                eventArguments = [
                    this,
                    me.$el.is(':checked'),
                    me.opts.bundleProductId,
                    me.opts.bundleId
                ];

            me.storageManager.setItem(
                me.name,
                me.$el.is(':checked')
            );

            $.publish('swagBundle/productSelection/change', eventArguments);
        },

        destroy: function() {
            this._destroy();
        }
    });

    /** Plugin starter */
    $(function() {
        StateManager.addPlugin('*[data-bundleProductSelection="true"]', 'swagBundleProductSelection');
    });
}(jQuery, window));
