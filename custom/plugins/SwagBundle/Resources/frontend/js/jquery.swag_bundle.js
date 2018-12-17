;
(function($, window, document, undefined) {
    'use strict';

    $.plugin('swBundle', {
        /** Your default options */
        defaults: {
            /**
             * The bundle id
             *
             * @type number
             */
            bundleId: -1,

            /**
             * The wrapper for each product-position in the bundle-overview on the detail-page.
             * This contains the checkboxes for a selectable bundle.
             * @see bundleProductWrapperSelector
             *
             * @type String
             */
            productDetailWrapperSelector: '.detail--wrapper',

            /**
             * A wrapper for each product-position in the bundle-overview.
             * Notice: This does NOT contain the checkboxes in a selectable bundle.
             * @see productDetailWrapperSelector
             *
             * @type String
             */
            bundleProductWrapperSelector: '.bundle--wrapper-product',

            /**
             * The class to mark a product-position in the bundle as "not buyable".
             * Necessary to hide / show the buy-button.
             */
            /**
             * @type String
             */
            notBuyableClass: 'not--buyable',

            /**
             * The selector to fetch every checkbox in a selectable bundle.
             *
             * @type String
             */
            selectableBundleCheckboxSelector: '*[data-bundleProductSelection=true]',

            /**
             * The class of a selective bundle product
             *
             * @type String
             */
            selectiveBundleClass: 'selective--product',

            /**
             * @type String
             */
            basketButtonSelector: '.content--cart-button button',

            /**
             * @type String
             */
            bundleIdAttributeSelector: 'data-bundleId',

            /**
             * @type String
             */
            bundleProductsContentSelector: '.products--content',

            /**
             * @type String
             */
            bundleProductsDetailSelector: '.detail--wrapper',

            /**
             * @type String
             */
            bundleProductsHeaderSelector: 'products--header',

            /**
             * @type String
             */
            bundleProductsFooterSelector: 'products--footer',

            /**
             * @type String
             */
            bundleDescriptionClass: '.bundle--description',

            /**
             * @type String
             */
            bundleHideClass: 'js--hide-bundle',

            /**
             * @type String
             */
            bundleHeaderHideClass: 'js--hide-bundle-header',

            /**
             * @type String
             */
            bundleFooterHideClass: 'js--hide-bundle-footer',

            /**
             * @type String
             */
            bundleProductsAddMarginClass: 'js--bundle-products-add-margin',

            /**
             * @type String
             */
            bundlePanelHeaderSelector: '.bundle--panel-header'
        },

        /**
         * Initializes the plugin
         */
        init: function() {
            var me = this;

            // Applies HTML data attributes to the current options
            me.applyDataAttributes();

            me.findElements();

            // check Bundle Stock
            me.checkBundleStock();

            // hide bundle products if mobile device
            me.setBundleProductsStatus();

            // Init the bundle-teaser, which will truncate the bundle-description in lower resolutions
            me.initBundleTeaser();

            me.registerEvents();
        },

        /**
         * Finds and register the required HTML elements
         */
        findElements: function() {
            var me = this;

            // elements for checkBundleStock()
            me.basketButton = me.$el.find(me.opts.basketButtonSelector); // the basket button
            me.bundleProducts = me.$el.find(me.opts.bundleProductsContentSelector + ' ' + me.opts.bundleProductsDetailSelector + '[' + me.opts.bundleIdAttributeSelector + '=' + me.opts.bundleId + ']'); // bundle products

            me.productsContent = me.$el.find(me.opts.bundleProductsContentSelector);
            // elements for handleShowBundleEvent()
            me.productsHeader = me.$el.find('.' + me.opts.bundleProductsHeaderSelector + '[' + me.opts.bundleIdAttributeSelector + '=' + me.opts.bundleId + ']');
            me.productsFooter = me.$el.find('.' + me.opts.bundleProductsFooterSelector + '[' + me.opts.bundleIdAttributeSelector + '=' + me.opts.bundleId + ']');
        },

        /**
         * Registers the required events
         *
         * "_on" and "$.subscribe" events will be automatically removed when destroy() is called
         */
        registerEvents: function() {
            var me = this;

            me._on(me.productsHeader, 'click', $.proxy(me.handleShowBundleEvent, me));
            me._on(me.productsFooter, 'click', $.proxy(me.handleShowBundleEvent, me));
            $.subscribe('swagBundle/productSelection/change', $.proxy(me.checkBundleStock, me));
        },

        /**
         * This method is called in the init-method to initialize the bundle-description teaser.
         */
        initBundleTeaser: function() {
            StateManager.addPlugin(this.opts.bundleDescriptionClass, 'swOffcanvasHtmlPanel', ['xs', 's']);
        },

        /**
         * Check if the Bundle is buyable or not
         *
         * @param {Event} event
         * @param {Plugin} plugin
         * @param {Boolean} state
         * @param {Number} bundleId
         */
        checkBundleStock: function(event, plugin, state, bundleId) {
            var me = this,
                bundleNotBuyable = false,
                productSelector = me.opts.bundleProductWrapperSelector,
                products,
                $product;

            if (bundleId !== undefined) {
                productSelector += '.bundle--id-' + bundleId;
            }

            products = me.bundleProducts.find(productSelector);
            $.each(products, function(index, product) {
                $product = $(product);

                if (me.checkIfProductIsNotBuyable($product)) {
                    bundleNotBuyable = true;
                }
            });

            if (bundleNotBuyable || me.isNoProductInBundleSelected()) {
                me.basketButton.attr('disabled', 'disabled');
            } else {
                me.basketButton.prop('disabled', false);
            }
        },

        /**
         * Set the bundle products status
         *
         * On mobile devices the products must be hidden on all other devices they must be shown,
         * also the header and footer for the products must be shown or hide
         */
        setBundleProductsStatus: function() {
            var me = this;

            // if smartphone hide bundle products and footer otherwise hide header and footer
            if (StateManager.getCurrentState() === 'xs') {
                me.productsHeader.removeClass(me.opts.bundleHeaderHideClass);
                me.productsContent.removeClass(me.opts.bundleProductsAddMarginClass)
                    .addClass(me.opts.bundleProductsAddMarginClass)
                    .addClass(me.opts.bundleHideClass);

                me.productsHeader.show();
            } else {
                me.productsHeader.addClass(me.opts.bundleHeaderHideClass);
                me.productsContent.addClass(me.opts.bundleProductsAddMarginClass).removeClass(me.opts.bundleHideClass);

                me.productsFooter.hide();
                me.productsHeader.hide();
            }

            // always hide footer, will be shown when needed
            me.productsFooter.addClass(me.opts.bundleFooterHideClass);
        },

        /**
         * Handle the show bundle event
         *
         * If show bundle is clicked the bundle products must be shown
         *
         * @param {Event} event
         */
        handleShowBundleEvent: function(event) {
            var me = this,
                $currentTarget = $(event.currentTarget);

            if ($currentTarget.hasClass(me.opts.bundleProductsHeaderSelector)) {
                me.productsHeader.hide();
                me.productsFooter.show();
            }

            if ($currentTarget.hasClass(me.opts.bundleProductsFooterSelector)) {
                me.productsFooter.hide();
                me.productsHeader.show();
                $('html').scrollTop($(me.$el.find(me.opts.bundlePanelHeaderSelector)).offset().top); // scroll to top in bundle if bundle gets closed
            }

            me.productsContent.toggleClass(me.opts.bundleHideClass);
        },

        /** Update the plugin  */
        update: function() {
            var me = this;

            // set product status at update
            me.setBundleProductsStatus();
        },

        /** Destroys the plugin */
        destroy: function() {
            this._destroy();
        },

        /**
         * Changes the current http protocol to https or http, depending on the protocol which the customer used.
         *
         * @param {String} url
         * @returns {String}
         */
        setCurrentProtocol: function(url) {
            var me = this,
                prefix = me.getUrlPrefix();

            if (url.indexOf('http://') === 0 && prefix === 'https://') {
                url = url.replace('http://', prefix);
            } else if (url.indexOf('https://') === 0 && prefix === 'http://') {
                url = url.replace('https://', prefix);
            }

            return url;
        },

        /**
         * @returns {String}
         */
        getUrlPrefix: function() {
            return window.location.protocol + '//';
        },

        /**
         * Checks if a bundle is buyable due to the state of the products in the bundle
         *
         * @param {Object} $product
         * @returns {Boolean}
         */
        checkIfProductIsNotBuyable: function($product) {
            var me = this,
                productHasNotBuyableClass = $product.hasClass(me.opts.notBuyableClass),
                productIsSelected = $product.parent(me.opts.productDetailWrapperSelector).find(me.opts.selectableBundleCheckboxSelector).is(':checked'),
                bundleIsSelective = $product.hasClass(me.opts.selectiveBundleClass);

            return productHasNotBuyableClass && productIsSelected && bundleIsSelective;
        },

        /**
         * @returns {Boolean}
         */
        isNoProductInBundleSelected: function() {
            var checkboxes = this.$el.find(this.opts.selectableBundleCheckboxSelector),
                checkedCheckboxes = checkboxes.filter(':checked');

            return checkboxes.length >= 1 && checkedCheckboxes.length < 1;
        }
    });

    /**
     * Init the Plugin
     */
    $(function() {
        StateManager.addPlugin('*[data-swagBundle="true"]', 'swBundle');
    });
})(jQuery, window, document);
