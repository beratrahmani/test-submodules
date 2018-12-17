;(function($, window, StateManager, undefined) {
    'use strict';
    $.plugin('swagBundleIsPanelForVariantAvailable', {

        /** The default options */
        defaults: {
            /**
             * Holds the class of the information panel, which will be displayed if the container is not available
             *
             * @type String
             */
            noBundleAvailableContainerClass: '.bundle-panel--no-bundle-available',

            /**
             * @type String
             */
            isHiddenClass: 'is--hidden',

            /**
             * URL for ajax request to check if the bundle is available
             *
             * @type String
             */
            isBundleAvailableUrl: '',

            /**
             * Selector for the swagBundle jQuery plugin
             *
             * @type String
             */
            swagBundlePluginSelector: '*[data-swagBundle="true"]',

            /**
             * @type Number
             */
            bundleId: -1,

            /**
             * @type Number
             */
            mainProductId: -1,

            /**
             * @type String
             */
            bundleJQueryPluginName: 'plugin_swBundle',

            /**
             * @type String
             */
            bundleContainerHeaderSelector: '.content--bundle-header',

            /**
             * @type String
             */
            bundleProductsContainerSelector: '.bundle--detail-container',

            /**
             * @type String
             */
            bundleContainerSelector: '.bundle--panel',

            /**
             * @type String
             */
            bundleIdAttributeSelector: 'data-bundleId',

            /**
             * @type String
             */
            bundleVariantConfigurationPluginSelector: '*[data-swagBundleVariantConfiguration="true"]',

            /**
             * @type String
             */
            bundleVariantConfigurationPluginName: 'swagBundleVariantConfigurationSave'
        },

        /**
         * Initializes the plugin
         */
        init: function() {
            var me = this;

            // Applies HTML data attributes to the default options
            me.applyDataAttributes();

            me.$swagBundlePluginElement = $(me.opts.swagBundlePluginSelector);
            me.swagBundlePlugin = me.$swagBundlePluginElement.data(me.opts.bundleJQueryPluginName);

            me.findElements();
            me.registerEventListener();
        },

        /**
         * Collects and sets properties which are jQuery elements
         */
        findElements: function() {
            var me = this;

            me.$messageContainer = $(me.opts.noBundleAvailableContainerClass);
        },

        /**
         * Registers all event listeners
         */
        registerEventListener: function() {
            var me = this;

            $.subscribe('plugin/swAjaxVariant/onRequestData', $.proxy(me.updateView, me));
            $.subscribe('plugin/swAjaxVariant/onBeforeRequestData', $.proxy(me.removeBundleVariantConfigPlugin, me));

            if (!window.CSRF.checkToken()) {
                $.subscribe('plugin/swCsrfProtection/init', $.proxy(me.updateView, me));
                return;
            }

            me.updateView();
        },

        /**
         * Update the view with the response from the AJAX request
         *
         * @param {Event} event
         * @param {Plugin} plugin
         * @param {String} response
         */
        updateView: function(event, plugin, response) {
            var me = this,
                requestData = {
                    number: $('input[name=sAdd]').val(),
                    bundleId: me.opts.bundleId,
                    mainProductId: me.opts.mainProductId
                };

            if (response !== undefined) {
                me.updateBundleContainer(response);
            }

            $.ajax({
                'url': me.swagBundlePlugin.setCurrentProtocol(me.opts.isBundleAvailableUrl),
                'data': requestData,
                'dataType': 'json',
                'type': 'GET'
            }).done(function(result) {
                me.handleBundleVisibility(result.data.isAvailable, result.data.isVariantProduct);
            }).fail(function() {
                me.handleBundleVisibility(false, false);
            });
        },

        /**
         * Shows and hides the bundle-panel and the messageContainer
         *
         * @param {Boolean} isAvailable
         * @param {Boolean} isVariantProduct
         */
        handleBundleVisibility: function(isAvailable, isVariantProduct) {
            var me = this,
                eventArguments = [
                    me,
                    isAvailable,
                    isVariantProduct
                ],
                numberVisibleBundles;

            me.$el[isAvailable ? 'removeClass' : 'addClass'](me.opts.isHiddenClass);

            numberVisibleBundles = $(me.opts.swagBundlePluginSelector + ':visible').length;

            if (numberVisibleBundles < 1 && isVariantProduct) {
                me.$messageContainer.removeClass(me.opts.isHiddenClass);
                return;
            }

            me.$messageContainer.addClass(me.opts.isHiddenClass);

            $.publish('swagBundle/bundleVisibility/change', eventArguments);
        },

        /**
         * Updates the bundle containers and re-initializes the Bundle jQuery plugins
         *
         * @param {String} response
         */
        updateBundleContainer: function(response) {
            var me = this,
                $response = $($.parseHTML(response, document, true)),
                $bundleContainerHeaders = me.$el.find(me.opts.bundleContainerHeaderSelector),
                $bundleProductsContainers = me.$el.find(me.opts.bundleProductsContainerSelector);

            me.updateBundleElements($response, $bundleContainerHeaders, me.opts.bundleContainerHeaderSelector);
            me.updateBundleElements($response, $bundleProductsContainers, me.opts.bundleProductsContainerSelector);

            StateManager.updatePlugin('select:not([data-no-fancy-select="true"])', 'swSelectboxReplacement');

            StateManager.destroyPlugin(me.opts.bundleVariantConfigurationPluginSelector, me.opts.bundleVariantConfigurationPluginName);
            StateManager.destroyPlugin('*[data-swagBundleSlider="true"]', 'swagBundleSlider');
            StateManager.destroyPlugin('*[data-swagBundlePriceHandler="true"]', 'swagBundlePriceHandler');
            StateManager.destroyPlugin('*[data-bundleProductSelection="true"]', 'swagBundleProductSelection');

            StateManager.addPlugin('*[data-swagBundleSlider="true"]', 'swagBundleSlider');
            StateManager.addPlugin('*[data-swagBundlePriceHandler="true"]', 'swagBundlePriceHandler');
            StateManager.addPlugin('*[data-bundleProductSelection="true"]', 'swagBundleProductSelection');
            StateManager.addPlugin(me.opts.bundleVariantConfigurationPluginSelector, me.opts.bundleVariantConfigurationPluginName);

            $.publish('swagBundle/updateBundleContainer', [me, $response, $bundleContainerHeaders, $bundleProductsContainers]);
        },

        /**
         * Iterates through each bundle element and exchanges the content
         *
         * @param {Object} $response
         * @param {Object} $bundleElements
         * @param {String} selector
         */
        updateBundleElements: function($response, $bundleElements, selector) {
            var me = this,
                $bundleElement,
                $newBundleElement,
                bundleId;

            $bundleElements.each(function(index, bundleContainerHeader) {
                $bundleElement = $(bundleContainerHeader);
                bundleId = $bundleElement.parents(me.opts.bundleContainerSelector).attr(me.opts.bundleIdAttributeSelector);
                $newBundleElement = $response.find(me.opts.bundleContainerSelector + '[' + me.opts.bundleIdAttributeSelector + '=' + bundleId + '] ' + selector);
                $bundleElement.html($newBundleElement.html());
            });
        },

        /**
         * Removes the variant configuration plugin
         */
        removeBundleVariantConfigPlugin: function() {
            var me = this,
                $bundleContainers = $(me.opts.bundleVariantConfigurationPluginSelector),
                $bundleContainer,
                $variantConfigPlugin;

            $bundleContainers.each(function(index, bundleContainer) {
                $bundleContainer = $(bundleContainer);
                $variantConfigPlugin = $bundleContainer.data('plugin_' + me.opts.bundleVariantConfigurationPluginName);

                if ($variantConfigPlugin !== undefined) {
                    $variantConfigPlugin.destroy();
                }
            });
        }
    });

    /** Plugin starter */
    $(function() {
        StateManager.addPlugin('*[data-swagBundle="true"]', 'swagBundleIsPanelForVariantAvailable');
    });
}(jQuery, window, StateManager));
