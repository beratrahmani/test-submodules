;(function ($) {
    $.plugin('swAboReferencePrice', {

        defaults: {
            /**
             * Selector for the subscription delivery radiobutton
             * @type { string }
             */
            radioAboSelector: '.abo--delivery-input',

            /**
             * Selector for the single delivery radiobutton
             * @type { string }
             */
            radioSingleSelector: '.abo--single-delivery-input',

            /**
             * Selector for the reference price label
             * @type { string }
             */
            labelSelector: '.reference--price',

            /**
             * Selector for the duration select
             * @type { string }
             */
            durationIntervalSelector: '.abo--duration-interval',

            /**
             * Data-attribute for the widget controller URL
             * @type { string }
             */
            url: '',

            /**
             * Old not changing reference price
             * @type { float }
             */
            referencePrice: 0.0,

            /**
             * JSON object containing all subscription prices
             * @type { object|null }
             */
            prices: null,

            /**
             * Product subscription exclusive as boolean value
             * @type { boolean }
             */
            isExclusive: false,

            /**
             * Placeholder default needed in several locations
             * @type { float }
             */
            discountPercentage: 0.0,

            /**
             * Placeholder default needed in several locations
             * @type { float }
             */
            regularReferencePrice: 0.0
        },

        /**
         * Plugin Constructor
         */
        init: function () {
            var me = this;

            me.applyDataAttributes();
            me.registerElements();
            me.registerEvents();
            me.determineDiscountPercentage(me.opts.prices);
            me.callAjax(true, me.opts.isExclusive);
        },

        /**
         * Method to register all needed elements
         */
        registerElements: function () {
            var me = this;

            me.$singleRadio = me.$el.find(me.opts.radioSingleSelector);
            me.$aboRadio = me.$el.find(me.opts.radioAboSelector);
            me.$label = $(me.opts.labelSelector);
            me.$duration = $(me.opts.durationIntervalSelector);
        },

        /**
         * Method to register all needed events
         */
        registerEvents: function () {
            var me = this;

            me.$singleRadio.on('change', $.proxy(me.onSingleRadioChange, me));

            me.$aboRadio.on('change', $.proxy(me.onAboRadioChange, me));

            $.subscribe('plugin/swAboCommerce/onPercentageChange', $.proxy(me.onPercentageChange, me));
        },

        /**
         * Helper method to call Ajax if subscription discount percentage is changed
         * due to select fields
         */
        onPercentageChange: function (event, percent) {
            var me = this;

            if (me.opts.discountPercentage !== (100 - percent) / 100) {
                me.opts.discountPercentage = (100 - percent) / 100;
                me.callAjax(false);
            }
        },

        /**
         * Method used to set the label back to none discounted reference price
         * on single delivery selection
         */
        onSingleRadioChange: function () {
            var me = this;

            me.$label.html(me.opts.regularReferencePrice);
        },

        /**
         * Method to call Ajax on subscription delivery selection
         */
        onAboRadioChange: function () {
            var me = this;

            me.callAjax(false);
        },

        /**
         * Actual Ajax call to widget controller
         *
         * @param { boolean } isInit
         * @param { boolean } aboOnly
         */
        callAjax: function (isInit, aboOnly) {
            var me = this;
            var aboReferencePrice = '';

            $.ajax({
                url: me.opts.url,
                type: 'POST',
                data: {
                    referencePrice: me.opts.referencePrice,
                    discountPercentage: me.opts.discountPercentage
                }
            }).done(function (data) {
                me.opts.regularReferencePrice = data.regularReferencePrice;
                aboReferencePrice = data.aboReferencePrice;

                if (aboReferencePrice === 0) {
                    aboReferencePrice = data.regularReferencePrice;
                }

                if (!isInit || aboOnly) {
                    me.$label.html(aboReferencePrice);
                }
            });
        },

        /**
         * Helper method to determine right percentage for actual selected duration
         *
         * @param { object } prices
         */
        determineDiscountPercentage: function (prices) {
            var me = this;

            /**
             * For a subscription with no runtime limitations, we set the first discount available
             * because the rest never gets used anyway.
             */
            if (me.$duration.length === 0) {
                me.opts.discountPercentage = (100 - prices[0].discountPercentage) / 100;
                return;
            }

            /**
             * For a subscription with runtime limitation we iterate over each discount
             * and check if the duration mapped to that discount matches the selected one.
             */
            $.each(prices, function (index) {
                if (prices[index].duration <= (me.$duration.val()) * 1) {
                    me.opts.discountPercentage = (100 - prices[index].discountPercentage) / 100;
                }
            });
        }

    });

    $(function () {
        $('*[data-swAboReferencePrice="true"]').swAboReferencePrice();

        $.subscribe('plugin/swAjaxVariant/onRequestData', function () {
            $('*[data-swAboReferencePrice="true"]').swAboReferencePrice();
        });

        $.subscribe('plugin/swQuickview/onProductLoaded', function () {
            $('*[data-swAboReferencePrice="true"]').swAboReferencePrice();
        });
    });
})(jQuery);
