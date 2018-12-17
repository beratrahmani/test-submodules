;(function($, window, document, undefined) {
    'use strict';

    /**
     * Local private variables.
     */
    var $body = $('body'),
        $document = $(document);

    /**
     * Helper methods for changing the state of the view elements.
     */
    $.fn.extend({

        /**
         * Sets the data state attribute of the element.
         *
         * @param viewState
         * @returns {*}
         */
        setView: function(viewState) {
            return this.each(function() {
                $(this).attr('data-view', viewState);
            });
        },

        /**
         * Clears the data state attribute of the element.
         *
         * @returns {*}
         */
        clearView: function() {
            return this.each(function() {
                $(this).removeAttr('data-view');
            });
        }
    });

    /**
     * Emotion QuickView Plugin
     *
     * This plugin searches for all valid product links in an emotion world
     * and creates a QuickView element for every product. All links will open
     * the new QuickView element instead of routing to the detail page. In the
     * QuickView the detail information of the product are loaded via ajax.
     * The plugin saves the order of the products in the emotion world so the user
     * can navigate through the different products directly in the QuickView.
     */
    $.plugin('swQuickView', {

        defaults: {

            /**
             * The controller url for the ajax request.
             *
             * @property ajaxUrl
             * @type {string}
             */
            ajaxUrl: '',

            /**
             * The selector for products in the emotion world.
             *
             * @property productSelector
             * @type {string}
             */
            productSelector: '[data-ordernumber]',

            /**
             * Selector for the "Details" button if the "buy from listing" option is activated
             * and the product is not directly purchasable
             * Only needed if config option "additionalQuickViewMode" is set to 'Only "Details" button'
             *
             * @property detailBtnSelector
             * @type {string}
             */
            detailBtnSelector: '',

            /**
             * The attribute name containing the product number
             *
             * @property productNumberAttribute
             * @type {string}
             */
            productNumberAttribute: 'data-ordernumber',

            /**
             * The selector for product links in the emotion world.
             *
             * @property productLinkSelector
             * @type {string}
             */
            productLinkSelector: '.product--box a, .banner--mapping-link',

            /**
             * The base css class for the quickview element.
             *
             * @property quickViewCls
             * @type {string}
             */
            quickViewCls: 'quick-view',

            /**
             * The base css class for a single product view in the quickview.
             *
             * @property viewCls
             * @type {string}
             */
            viewCls: 'quick-view--view',

            /**
             * The css class for the overlay.
             *
             * @property overlayCls
             * @type {string}
             */
            overlayCls: 'quick-view--overlay',

            /**
             * The css class for managing active states.
             *
             * @property activeCls
             * @type {string}
             */
            activeCls: 'is--active',

            /**
             * The name of the main state.
             *
             * @property mainViewState
             * @type {string}
             */
            mainViewState: 'main',

            /**
             * The name of the state for the previous view.
             *
             * @property prevViewState
             * @type {string}
             */
            prevViewState: 'prev',

            /**
             * The name of the state for the next view.
             *
             * @property nextViewState
             * @type {string}
             */
            nextViewState: 'next',

            /**
             * The html content for the loading indicator.
             *
             * @property loadingIndicator
             * @type {string}
             */
            loadingIndicator: '<span class="quick-view--loader"></span>',

            /**
             * selector for the product configurator form
             *
             * @property configuratorFormSelector
             * @type {string}
             */
            configuratorFormSelector: '.configurator--form',

            /**
             * selector for the variant selection box
             *
             * @property variantSelectBoxSelector
             * @type {string}
             */
            variantSelectBoxSelector: '*[data-ajax-select-variants="true"]',

            /**
             * selector for the variant selection reset button
             *
             * @property resetConfigurationButtonSelector
             * @type {string}
             */
            resetConfigurationButtonSelector: '.reset--configuration',

            /**
             * selector for the quick view image slider
             *
             * @property quickViewImageSliderSelector
             * @type {string}
             */
            quickViewImageSliderSelector: '*[data-image-slider]',

            /**
             * selector for the buy button form
             *
             * @property buyButtonFormSelector
             * @type {string}
             */
            buyButtonFormSelector: '*[data-add-article="true"]',

            /**
             * selector for the image
             *
             * @property imageSelector
             * @type {string}
             */
            imageSelector: '[data-image-gallery=true]',

            /**
             * selector for the compare button
             *
             * @property compareButtonSelector
             * @type {string}
             */
            compareButtonSelector: '*[data-compare-ajax="true"]',

            /**
             * CSS class for disabling scrolling
             *
             * @property noScrollCls
             * @type {string}
             */
            noScrollCls: 'no--scroll'
        },

        /**
         * Plugin constructor
         */
        init: function() {
            var me = this;

            me.applyDataAttributes();

            if (me.opts.ajaxUrl.length < 1) {
                return;
            }

            me.$quickView = $('<div>', { 'class': me.opts.quickViewCls });
            me.$overlay = $('<div>', { 'class': me.opts.overlayCls });

            me.viewSelector = '.' + me.opts.viewCls;
            me.overlaySelector = '.' + me.opts.overlayCls;

            me.trackProducts();

            me.$quickView.appendTo($body);

            me.initImageSlider();
            me.initWishList();
            me.registerEvents();
        },

        /**
         * Registers the image slider plugin for the quick view.
         */
        initImageSlider: function() {
            var me = this;

            window.StateManager.addPlugin(me.opts.quickViewImageSliderSelector, 'swImageSlider', {
                thumbnails: false
            }, ['xs', 's', 'm', 'l']);

            window.StateManager.addPlugin(me.opts.quickViewImageSliderSelector, 'swImageSlider', {
                thumbnails: true
            }, ['xl']);
        },

        /**
         * Calls the ajax wish list for the quick view.
         */
        initWishList: function() {
            var me = this;

            me.$quickView.swAjaxWishlist({
                'iconCls': 'icon--check is--large'
            });
        },

        /**
         * Registers all necessary events.
         */
        registerEvents: function() {
            var me = this;

            $.subscribe(me.getEventName('plugin/swProductSlider/onLoadItemsSuccess'), $.proxy(me.trackProducts, me));
            $.subscribe(me.getEventName('plugin/swInfiniteScrolling/onFetchNewPageFinished'), $.proxy(me.trackProducts, me));
            $.subscribe(me.getEventName('plugin/swInfiniteScrolling/onLoadPreviousFinished'), $.proxy(me.trackProducts, me));
            $.subscribe(me.getEventName('plugin/swListingActions/onGetFilterResultFinished'), $.proxy(me.trackProducts, me));
            $.subscribe(me.getEventName('plugin/swLastSeenProducts/onCreateProductList'), $.proxy(me.trackProducts, me));

            me.$el.on(me.getEventName('click'), me.opts.productLinkSelector, $.proxy(me.onProductLink, me));

            me._on($document, 'keydown', $.proxy(me.onKeyPress, me));

            me.$quickView.on(me.getEventName('click'), '[data-view="' + me.opts.nextViewState + '"]', $.proxy(me.showNext, me));
            me.$quickView.on(me.getEventName('click'), '[data-view="' + me.opts.prevViewState + '"]', $.proxy(me.showPrev, me));

            me.$quickView.on(me.getEventName('click'), me.overlaySelector, $.proxy(me.hideQuickView, me));
            me.$quickView.on(me.getEventName('touchstart'), me.overlaySelector, $.proxy(me.hideQuickView, me));
            me.$quickView.on(me.getEventName('touchmove'), me.overlaySelector, function(event) {
                event.preventDefault();
                event.stopPropagation();
                event.cancelBubble = true;
            });
        },

        /**
         * Searches the emotion world for valid product links.
         */
        trackProducts: function() {
            var me = this;

            me.$quickView.empty();
            me.$overlay.appendTo(me.$quickView);

            if (me.opts.detailBtnSelector && me.opts.detailBtnSelector.length) {
                me.$products = me.$el.find(me.opts.productSelector + ' ' + me.opts.detailBtnSelector).parents(me.opts.productSelector);
                // parents() collects the elements in reverse order, so we have to bring them back into the right order
                me.$products = $(me.$products.get().reverse());
            } else {
                me.$products = me.$el.find(me.opts.productSelector);
            }

            me.products = {};
            me.activeProduct = false;

            $.each(me.$products, function(index, el) {
                if (!$(el).is(me.opts.productSelector)) {
                    return;
                }

                var $el = $(el),
                    $view = $('<div>'),
                    number = $el.attr(me.opts.productNumberAttribute);

                $view.addClass(me.opts.viewCls)
                    .addClass('view--' + index)
                    .addClass('product--' + number)
                    .appendTo(me.$quickView);

                me.products[index] = {
                    '$el': $el,
                    '$view': $view,
                    'index': index,
                    'number': number,
                    'loaded': false
                };
            });

            $.publish('plugin/swQuickview/onTrackProducts', [me]);
        },

        /**
         * Loads the product details of the product at the given index.
         *
         * @param {integer} index
         * @param {boolean} withoutConfiguration
         * @param {boolean} isVariant
         */
        loadProduct: function(index, withoutConfiguration, isVariant) {
            var me = this;

            isVariant = isVariant || false;

            if (me.products[index] === undefined) {
                return;
            }

            var product = me.products[index],
                orderNumber = product.number,
                values = { sOrderNumber: orderNumber };

            if (!withoutConfiguration) {
                values = $.extend(values, me.getConfiguratorFormValues(product.$view));
            }

            if (isVariant) {
                // set height, if a variant changes, to prevent size changing
                product.$view.height(product.$view.height());
            }

            me.resetProductView(product.$view);

            $.publish('plugin/swQuickview/onBeforeRequestData', [me, values]);

            $.ajax({
                url: me.opts.ajaxUrl,
                data: values,
                method: 'POST',
                success: function(response) {
                    if (!response.length) {
                        me.hideQuickView();
                        return;
                    }

                    product.loaded = true;
                    product.$view.html(response);

                    me.updatePluginsAndEvents(product.$view, index);

                    $.publish('plugin/swQuickview/onProductLoaded', [me, product.$view, response, values]);
                }
            });

            $.publish('plugin/swQuickview/onLoadProduct', [me]);
        },

        resetProductView: function($productView) {
            var me = this;

            $productView
                .off(me.getEventName('change'), me.opts.variantSelectBoxSelector)
                .off(me.getEventName('click'), me.opts.resetConfigurationButtonSelector);

            $productView.html(me.opts.loadingIndicator);

            $.publish('plugin/swQuickview/onResetProductView', [me, $productView]);
        },

        /**
         * @param $productView
         * @param {int} quickViewIndex
         */
        updatePluginsAndEvents: function($productView, quickViewIndex) {
            var me = this;

            window.StateManager.updatePlugin(me.opts.quickViewImageSliderSelector, 'swImageSlider');
            window.StateManager.updatePlugin(me.opts.buyButtonFormSelector, 'swAddArticle');
            window.StateManager.updatePlugin(me.opts.imageSelector, 'swImageGallery');
            window.StateManager.updatePlugin(me.opts.compareButtonSelector, 'swProductCompareAdd');

            $productView
                .on(me.getEventName('change'), me.opts.variantSelectBoxSelector, $.proxy(me.onVariantChange, me, quickViewIndex, false))
                .on(me.getEventName('click'), me.opts.resetConfigurationButtonSelector, $.proxy(me.onVariantChange, me, quickViewIndex, true));

            $.publish('plugin/swQuickview/onUpdatePluginsAndEvents', [me, $productView, quickViewIndex]);
        },

        /**
         * reload the product on variant change
         *
         * @param {integer} quickViewIndex
         * @param {boolean} resetConfiguration
         * @param {Event} event
         */
        onVariantChange: function(quickViewIndex, resetConfiguration, event) {
            var me = this;

            event.preventDefault();

            me.loadProduct(quickViewIndex, resetConfiguration, true);
        },

        /**
         * @param $productView
         * @return {object}
         */
        getConfiguratorFormValues: function($productView) {
            var me = this,
                $configuratorForm = $($productView.find(me.opts.configuratorFormSelector)),
                configValues = $configuratorForm.serializeArray(),
                tempParams = [];

            $.each(configValues, function(index, item) {
                if (item['value']) {
                    tempParams[item['name']] = item['value'];
                }
            });

            return tempParams;
        },

        /**
         * Event handler for the product links.
         *
         * @param event
         */
        onProductLink: function(event) {
            var me = this,
                $currentTarget = $(event.currentTarget),
                $product = ($currentTarget.is(me.opts.productSelector)) ? $currentTarget : $currentTarget.parents(me.opts.productSelector);

            if (!$product.length) {
                return;
            }
            event.preventDefault();

            me.showQuickView($product);

            $.publish('plugin/swQuickview/onProductLink', [me]);
        },

        onKeyPress: function(event) {
            var me = this;

            if (me.activeProduct === false) {
                return;
            }

            if (event.keyCode === 37) {
                me.showPrev();
            }
            if (event.keyCode === 39) {
                me.showNext();
            }

            $.publish('plugin/swQuickview/onKeyPress', [me]);
        },

        /**
         * Shows the quickview and stets the correct state for all views.
         *
         * @param $product
         */
        showQuickView: function($product) {
            if (!$product.is(this.opts.productSelector)) {
                return;
            }

            $('html').addClass('no--scroll');

            var me = this,
                index = me.$products.index($product),
                product = me.products[index];

            if (!product.loaded) {
                me.loadProduct(index);
            }

            me.activeProduct = index;

            product.$view.nextAll(me.viewSelector).setView('right');
            product.$view.prevAll(me.viewSelector).setView('left');

            me.$quickView.addClass(me.opts.activeCls);

            setTimeout(function() {
                product.$view.setView(me.opts.mainViewState);
                product.$view.prev(me.viewSelector).setView(me.opts.prevViewState);
                product.$view.next(me.viewSelector).setView(me.opts.nextViewState);
            }, 100);

            $.publish('plugin/swQuickview/onShowQuickView', [me]);
        },

        /**
         * Hides the quickview and clears all states.
         */
        hideQuickView: function(event) {
            var me = this;

            event.preventDefault();

            me.$quickView.removeClass(me.opts.activeCls);

            $('html').removeClass('no--scroll');

            $.each(me.products, function(index, product) {
                product.$view.clearView();
            });

            me.activeProduct = false;

            $.publish('plugin/swQuickview/onHideQuickView', [me]);
        },

        /**
         * Slides the quickview to the next product view.
         */
        showNext: function() {
            var me = this,
                product = me.products[me.activeProduct],
                index = product.index,
                nextIndex = index + 1;

            if (!me.products[nextIndex].loaded) {
                me.loadProduct(nextIndex);
            }

            product.$view.setView(me.opts.prevViewState);
            product.$view.prev(me.viewSelector).setView('left');
            product.$view.next(me.viewSelector).setView(me.opts.mainViewState);
            product.$view.next(me.viewSelector).next(me.viewSelector).setView(me.opts.nextViewState);

            me.activeProduct = nextIndex;

            $.publish('plugin/swQuickview/onShowNext', [me]);
        },

        /**
         * Slides the quickview to the previous product view.
         */
        showPrev: function() {
            var me = this,
                product = me.products[me.activeProduct],
                index = product.index,
                prevIndex = index - 1;

            if (!me.products[prevIndex].loaded) {
                me.loadProduct(prevIndex);
            }

            product.$view.setView(me.opts.nextViewState);
            product.$view.next(me.viewSelector).setView('right');
            product.$view.prev(me.viewSelector).setView(me.opts.mainViewState);
            product.$view.prev(me.viewSelector).prev(me.viewSelector).setView(me.opts.prevViewState);

            me.activeProduct = prevIndex;

            $.publish('plugin/swQuickview/onShowPrev', [me]);
        },

        /**
         * Destroys the plugin.
         */
        destroy: function() {
            var me = this;

            me.$el.off(me.getEventName('click'), me.opts.productLinkSelector);

            $.unsubscribe(me.getEventName('plugin/swProductSlider/onLoadItemsSuccess'));
            $.unsubscribe(me.getEventName('plugin/swInfiniteScrolling/onFetchNewPageFinished'));
            $.unsubscribe(me.getEventName('plugin/swListingActions/onGetFilterResultFinished'));
            $.unsubscribe(me.getEventName('plugin/swLastSeenProducts/onCreateProductList'));

            me.$quickView.off(me.getEventName('click'), '[data-view="' + me.opts.nextViewState + '"]');
            me.$quickView.off(me.getEventName('click'), '[data-view="' + me.opts.prevViewState + '"]');

            me.$quickView.remove();

            me._destroy();
        }
    });

    $(function() {
        StateManager.addPlugin('*[data-quickview="true"]', 'swQuickView');
    });
})(jQuery, window, document);
