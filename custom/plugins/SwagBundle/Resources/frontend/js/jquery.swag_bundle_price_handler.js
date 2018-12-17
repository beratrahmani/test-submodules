;(function($, window) {
    'use strict';

    $.plugin('swagBundlePriceHandler', {

        /** The default options */
        defaults: {
            /**
             * @type number
             */
            discountPercentage: -1,

            /**
             * @type string
             */
            currentPriceSpanClass: '.price--value-bundle-price',

            /**
             * @type string
             */
            regularPriceSpanClass: '.price--value-regular-price',

            /**
             * @type string
             */
            regularPriceSpanClassNumber: '.regular-price-total',

            /**
             * @type string
             */
            productPriceContainerClass: '.bundle--product-price',

            /**
             *  @type string
             */
            priceElementBundleProductIdSelector: 'data-bundleProductId',

            /**
             * @type string
             */
            priceElementBundleProductPriceSelector: 'data-bundleProductPrice',

            /**
             * @type string
             */
            currencyHelperSelector: 'div[data-swagBundle=true]',

            /**
             * @type string
             */
            currencyHelperAttributeSelector: 'data-currencyHelper',

            /**
             * @type number
             */
            bundleId: -1,

            /**
             * @type String
             */
            bundleIdAttributeSelector: 'data-bundleId'
        },

        /**
         * Initializes the plugin
         */
        init: function() {
            var me = this;

            // Applies HTML data attributes to the default options
            me.applyDataAttributes();

            me.findElements();
            me.updateProperties();
            me.registerEventHandler();
        },

        /**
         * Collects and sets properties which are jQuery elements
         */
        findElements: function() {
            var me = this;

            me.$currentPriceContainer = me.$el.find(me.opts.currentPriceSpanClass);
            me.$regularPriceContainer = me.$el.find(me.opts.regularPriceSpanClass);
            me.$regularPriceContainerContent = me.$regularPriceContainer.html();
        },

        /**
         * Collects and sets properties
         */
        updateProperties: function() {
            var me = this;

            me.priceElementList = me.createPriceElementList();
        },

        /**
         * Registers all event listeners
         */
        registerEventHandler: function() {
            var me = this;

            $.subscribe('swagBundle/productSelection/change', $.proxy(me.onProductSelectionChange, me));
        },

        /**
         * Update the state of the checkbox, recalculate the price with the new settings
         * and set the new price to the priceContainers.
         *
         * @param {Event} event
         * @param {Plugin} plugin
         * @param {Boolean} newValue
         * @param {Number} bundleProductId
         * @param {Number} bundleId
         */
        onProductSelectionChange: function(event, plugin, newValue, bundleProductId, bundleId) {
            var me = this,
                prices;

            if (bundleId !== me.opts.bundleId) {
                return;
            }

            me.priceElementList[bundleProductId].isSelected = newValue;

            prices = me.calculatePrices();

            me.$currentPriceContainer.html(me.formatCurrency(prices.reducedPride));
            me.$regularPriceContainer.html('');

            if (prices.reducedPride !== prices.defaultPrice) {
                me.$regularPriceContainer.html(me.$regularPriceContainerContent);
                me.$regularPriceContainer.find(me.opts.regularPriceSpanClassNumber).html(me.formatCurrency(prices.defaultPrice));
            }
        },

        /**
         * Create a object with each bundleProductPrice, the reduced price and the current state of the checkbox.
         *
         * @returns {Object}
         */
        createPriceElementList: function() {
            var me = this,
                productIdSelector = me.opts.priceElementBundleProductIdSelector,
                productPriceSelector = me.opts.priceElementBundleProductPriceSelector,
                $elementList = $(me.opts.productPriceContainerClass + '[' + me.opts.bundleIdAttributeSelector + '=' + me.opts.bundleId + ']'),
                list = {};

            $.each($elementList, function(index, priceElement) {
                var $priceElement = $(priceElement),
                    elementPrice = window.parseFloat($priceElement.attr(productPriceSelector)),
                    bundleProductId = $priceElement.attr(productIdSelector);

                list[bundleProductId] = {
                    defaultPrice: elementPrice,
                    reducedPrice: me.calculateReducedPrice(elementPrice),
                    isSelected: true
                };
            });

            return list;
        },

        /**
         * Calculates the bundle prices and return a object with the bundlePrice and the reduced bundle price.
         *
         * @returns {Object}
         */
        calculatePrices: function() {
            var me = this,
                defaultPrice = 0.0,
                reducedPrice = 0.0;

            $.each(me.priceElementList, function(index, priceObject) {
                if (priceObject.isSelected) {
                    defaultPrice += priceObject.defaultPrice;
                    reducedPrice += priceObject.reducedPrice;
                }
            });

            return {
                defaultPrice: defaultPrice.toFixed(2),
                reducedPride: reducedPrice.toFixed(2)
            };
        },

        /**
         * Calculates the reduced price
         *
         * @param {Number} defaultPrice
         * @returns {Number}
         */
        calculateReducedPrice: function(defaultPrice) {
            var me = this,
                discount = window.parseFloat(me.opts.discountPercentage);

            return window.parseFloat((defaultPrice / 100) * (100 - discount));
        },

        /**
         * Helper to format the currency using the given format in the currency helper.
         *
         * @param {String} value
         * @returns {String}
         */
        formatCurrency: function(value) {
            var me = this,
                currencyFormat = $(me.opts.currencyHelperSelector).attr(me.opts.currencyHelperAttributeSelector);

            value = Math.round(value * 100) / 100;
            value = value.toFixed(2);
            if (currencyFormat.indexOf('0.00') > -1) {
                value = currencyFormat.replace('0.00', value);
            } else {
                value = value.replace('.', ',');
                value = currencyFormat.replace('0,00', value);
            }

            return value;
        },

        destroy: function() {
            this._destroy();
        }
    });

    /** Plugin starter */
    $(function() {
        StateManager.addPlugin('*[data-swagBundlePriceHandler="true"]', 'swagBundlePriceHandler');
    });
}(jQuery, window));
