;(function($, window) {
    'use strict';

    /**
     * Shopware Live shopping Plugin
     *
     * The Plugin change the price, change the countdown and add some effects for live shoppping
     *
     */
    $.plugin('swLiveShopping', {
        alias: 'swagLiveShopping',

        /** Your default options */
        defaults: {

            /**
             * How long the live shopping is valid
             *
             * Is set via data attribute in html
             */
            validTo: null,

            /**
             * The live shopping id
             *
             * Is set via data attribute in html
             */
            liveShoppingId: null,

            /**
             * The data url
             *
             * Is set via data attribute in html
             */
            dataUrl: null,

            /**
             * The live shopping type
             *
             * Is set via data attribute in html
             */
            liveShoppingType: 1,

            /**
             * The star item
             *
             * Is set via data attribute in html
             */
            star: null,

            /**
             * The initial sells
             *
             * Is set via data attribute in html
             */
            initialSells: null,

            /**
             * Helper for Currency calculations
             *
             * Is set via data attribute in html
             */
            currencyHelper: null
        },

        /**
         * Initializes the plugin
         *
         * @returns {Plugin}
         */
        init: function () {
            var me = this;

            // Applies HTML data attributes to the current options
            me.applyDataAttributes();

            // get the needed elements
            me.parentDetail = me.$el.hasClass('liveshopping--details');
            me.parentListing = me.$el.hasClass('liveshopping--listing');

            me.dayElement = me.$el.find('.liveshopping--days');
            me.hourElement = me.$el.find('.liveshopping--hours');
            me.minuteElement = me.$el.find('.liveshopping--minutes');
            me.secondElement = me.$el.find('.liveshopping--seconds');

            me.elapseElement = me.$el.find('.elapse--inner');

            me.currentPriceElement = me.$el.find('.liveshopping--price');
            me.currentListingPriceElement = me.$el.find('.liveshopping--price');

            me.referenceUnitPriceElement = me.$el.find('.unit--unit-price');
            me.referenceListingUnitPriceElement = me.$el.find('.price--unit-price');
            me.stockWrapperElement = me.$el.find('.counter--stock');

            me.bonusPointElement = $('.bonussystem--info');

            me.priceElement = me.$el.parent().find('.product--price');
            me.listingElements = me.$el.find('.badge--liveshopping, .liveshopping--container');

            // Initially hide the bonus point element
            // Will be shown again once the ajax request returned the current price
            if (me.bonusPointElement.length && me.parentDetail) {
                me.bonusPointElement.fadeOut('fast');
            }

            // create time runner
            var date = new Date(),
                newDate = (date.getTime() / 1000) - 1;

            me.timeRunner = new window.TimeRunner(newDate, me.timerCallback, me);

            me.afterInit();
        },

        afterInit: function () {
            var me = this;

            if (window.CSRF.checkToken()) {
                me.refreshData();
            } else {
                $.subscribe('plugin/swCsrfProtection/init', function() {
                    me.refreshData();
                });

                // Additional subscriber for emotions
                $.subscribe('plugin/swEmotionLoader/onLoadEmotionFinished', function() {
                    me.refreshData();
                });
            }
        },

        /**
         * Shutdowns the live shopping timer and displays the regular prices
         */
        shutdown: function() {
            var me = this;
            me.timeRunner.shutdown();

            if (me.parentDetail) {
                me.$el.hide();
            } else if (me.parentListing) {
                me.listingElements.hide();
            }

            me.priceElement.show();
        },

        /**
         * Refresh the live shopping data
         *
         * @param diff
         */
        refreshDates: function (diff) {
            var me = this,
                percentage = diff.s / 60 * 100;

            me.dayElement.html(diff.d);
            me.hourElement.html(diff.h);
            me.minuteElement.html(diff.m);
            me.secondElement.html(diff.s);
            me.elapseElement.css('width', percentage + '%');

            diff.s = parseInt(diff.s, 10);
            diff.h = parseInt(diff.h, 10);
            diff.m = parseInt(diff.m, 10);
            diff.d = parseInt(diff.d, 10);

            if ((me.opts.liveShoppingType === 1 && diff.s % 20 === 0) || diff.s === 0) {
                me.refreshData();
            }
        },

        /**
         * Plugin function to refresh the live shopping data on detail page or in the listing.
         *
         * @returns {boolean}
         */
        refreshData: function() {
            var me = this;

            if (me.parentDetail) {
                return me.refreshDetailData();
            } else if (me.parentListing) {
                return me.refreshListingData();
            }
        },

        /**
         * Plugin function to refresh the live shopping data on detail page.
         *
         * This function is used for "discount/surcharge per minute" live shopping
         * products. It sends an ajax request to the data url which is placed
         * in a hidden input field within the live shopping container.
         *
         * @returns {boolean}
         */
        refreshDetailData: function() {
            var me = this;

            $.ajax({
                url: me.opts.dataUrl,
                dataType: 'json',
                type: 'GET',
                success: function(record) {
                    if (record.success === false) {
                        me.shutdown();
                        return;
                    }

                    me.$el.show();
                    me.priceElement.hide();

                    me.currentPriceElement.fadeOut('fast');

                    me.currentPriceElement.html(
                        me.formatCurrency(record.data.currentPrice) + ' ' + me.opts.star
                    );

                    me.updateBonusPoints(record.data.currentPrice);

                    window.setTimeout(function() {
                        me.currentPriceElement.fadeIn('slow');
                    }, 150);

                    me.referenceUnitPriceElement.html(
                        me.formatCurrency(record.data.referenceUnitPrice)
                    );

                    me.stockWrapperElement.find('.stock--quantity-number').text(
                        record.data.quantity
                    );
                }
            });

            return true;
        },

        refreshListingData: function() {
            var me = this;

            $.ajax({
                url: me.opts.dataUrl,
                dataType: 'json',
                type: 'GET',
                success: function(record) {
                    if (record.success === false) {
                        me.shutdown();
                        return;
                    }

                    me.priceElement.hide();
                    me.listingElements.show();

                    me.currentListingPriceElement.fadeOut('fast');

                    me.currentListingPriceElement.html(
                        me.formatCurrency(record.data.currentPrice) + ' ' + me.opts.star
                    );

                    window.setTimeout(function() {
                        me.currentListingPriceElement.fadeIn('slow');
                    }, 150);

                    me.referenceListingUnitPriceElement.html(
                        me.formatCurrency(record.data.referenceUnitPrice)
                    );
                }
            });

            return true;
        },

        /**
         * Helper function to set the bonus points depending on the current live shopping price
         *
         * @param currentPrice
         */
        updateBonusPoints: function(currentPrice) {
            var me = this;

            if (!me.bonusPointElement.length) {
                return;
            }

            // Get the bonus system conversion factor
            var bonusConversionFactor = $('.is--ctl-detail #bonus_point_conversion_factor').val();

            // Set a new value for points per unit
            $('.is--ctl-detail #earning_points_per_unit').val(currentPrice / bonusConversionFactor);
            // Force recalculation of bonus points
            $('.is--ctl-detail #sQuantity').trigger('change');

            // Fade In the element
            me.bonusPointElement.fadeIn('slow');
        },

        /**
         * Helper to format the currency
         *
         * @param value
         * @returns {string}
         */
        formatCurrency: function (value) {
            var me = this;
            var currencyFormat = me.opts.currencyHelper;

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

        /**
         * Helper function to get diff between valid to date and current date
         *
         * @param timeNow
         */
        timerCallback: function(timeNow) {
            var me = this;

            if (isNaN(timeNow)) {
                timeNow = new Date();
            }

            var date = new Date(timeNow);
            var validTo = new Date(me.opts.validTo * 1000);

            var diff = me.getTimestampDiff(validTo.getTime(), date.getTime());

            if (diff === false) {
                me.shutdown();
                return;
            }

            me.refreshDates(diff);
        },

        /**
         * Gets the difference between two timestamps
         * which is used by the live shopping module
         *
         * @param d1
         * @param d2
         * @returns {*}
         */
        getTimestampDiff: function(d1, d2) {
            var me = this;
            if (d1 < d2) {
                return false;
            }
            var d = Math.floor((d1 - d2) / (24 * 60 * 60 * 1000));
            var h = Math.floor(((d1 - d2) - (d * 24 * 60 * 60 * 1000)) / (60 * 60 * 1000));
            var m = Math.floor(((d1 - d2) - (d * 24 * 60 * 60 * 1000) - (h * 60 * 60 * 1000)) / (60 * 1000));
            var s = Math.floor(((d1 - d2) - (d * 24 * 60 * 60 * 1000) - (h * 60 * 60 * 1000) - (m * 60 * 1000)) / 1000);

            return {
                'd': me.formatNumber(d),
                'h': me.formatNumber(h),
                'm': me.formatNumber(m),
                's': me.formatNumber(s)
            };
        },

        /**
         * Helper to format a number
         *
         * @param number
         * @returns {*}
         */
        formatNumber: function(number) {
            var tmp = number + '';
            if (tmp.length === 1) {
                return '0' + number;
            } else {
                return number;
            }
        },

        /** Destroys the plugin */
        destroy: function () {
            this._destroy();
        }
    });
})(jQuery, window);

/**
 * Need a additional plugin to change the time runner to our own time runner
 */
;(function($, window, document, undefined) {
    'use strict';

    function Server(timeNow, timerCallback, scope) {
        this.timeNow = timeNow * 1000;
        this.timerCallback = timerCallback;
        this.callbackScope = scope;
        this.init();
    }

    Server.prototype = {
        timeNow: undefined,
        interval: undefined,
        timerCallback: undefined,

        init: function() {
            var me = this;
            this.interval = window.setInterval(function() {
                me.setTime();
            }, 1000);
        },

        setTime: function() {
            this.timeNow += 1000;
            if ($.isFunction(this.timerCallback)) {
                this.timerCallback.apply(this.callbackScope, [ this.timeNow ]);
            }
        },
        shutdown: function() {
            window.clearInterval(this.interval);
            this.interval = undefined;
        }
    };
    window.TimeRunner = Server;
})(jQuery, window);

;(function($, window, document) {
    $(document).ready(function() {
        $('*[data-live-shopping="true"]').swLiveShopping();
    });

    $.subscribe('plugin/swEmotionLoader/onInitEmotion', function(event, emotionWrapper) {
        emotionWrapper.$el.find('*[data-live-shopping="true"]').swLiveShopping();
    });

    $.subscribe('plugin/swProductSlider/onLoadItemsSuccess', function(event, emotionWrapper) {
        emotionWrapper.$el.find('*[data-live-shopping="true"]').swLiveShopping();
    });

    $.subscribe('plugin/swProductSlider/onInitSlider', function(event, plugin) {
        plugin.$el.find('*[data-live-shopping="true"]').swLiveShopping();
    });

    // Refresh the plugin after a variant was loading using AJAX
    $.subscribe('plugin/swAjaxVariant/onRequestData', function() {
        $('*[data-live-shopping="true"]').swLiveShopping();
    });

    $.subscribe('plugin/swInfiniteScrolling/onFetchNewPageFinished', function() {
        $('*[data-live-shopping="true"]').swLiveShopping();
    });

    $.subscribe('plugin/swListingActions/onGetFilterResultFinished', function() {
        $('*[data-live-shopping="true"]').swLiveShopping();
    });

    $.subscribe('plugin/swQuickview/onProductLoaded', function(event, emotionWrapper) {
        emotionWrapper.$quickView.find('*[data-live-shopping="true"]').swLiveShopping();
    });
})(jQuery, window);
