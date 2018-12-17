;(function ($, window) {
    $.plugin('swAboCommerce', {

        /**
         * The default options
         * Can be set by using a data-attribute, e.g. "data-singleDeliveryRadioSelector" in HTML
         */
        defaults: {
            /**
             * The selector for the radio, that has to be clicked to change the delivery-type to "single delivery".
             * @type {string}
             */
            singleDeliveryRadioSelector: '.abo--single-delivery-input',

            /**
             * Opposite of the radio mentioned above.
             * The selector of the radio that has to be clicked to change the delivery-type to "abo delivery".
             * @type {string}
             */
            aboDeliveryRadioSelector: '.abo--delivery-input',

            /**
             * Selector for a button which opens the abo-information on click.
             * @type {string}
             */
            openAboInfoSelector: '.abo--delivery-info-icon',

            /**
             * Selector for the label to be clicked for the price-info to be displayed.
             * @type {string}
             */
            openAboDiscountLabel: '.abo--discount-label',

            /**
             * Selectors of the delivery-, duration- and quantity-select-fields.
             * @type {string}
             */
            selectFieldSelector: '.abo--duration-interval, .abo--delivery-interval, .abo--quantity-select',

            /**
             * Selector for duration interval
             * @type {string}
             */
            durationIntervalSelector: '.abo--duration-interval',

            /**
             * Selector for delivery interval
             * @type {string}
             */
            deliveryIntervalSelector: '.abo--delivery-interval',

            /**
             * Selector for quantity selection
             * @type {string}
             */
            quantitySelectSelector: '.abo--quantity-select',

            /**
             * Selector for buybox element
             * @type {string}
             */
            buyBoxSelector: '.buybox--inner',

            /**
             * Selector of the container, that contains all the important AboCommerce data as a JSON-string.
             * @type {string}
             */
            aboCommerceDataSelector: '.abo--commerce-data',

            /**
             * Selector of the container, that contains the AboCommerce block prices as a JSON-string.
             * @type {string}
             */
            aboCommerceBlockPricesDataSelector: '.abo--block-prices-data',

            /**
             * Selector of the container, which contains the various delivery-selects
             * @type {string}
             */
            deliveryContainerSelector: '.abo--delivery-interval-container',

            /**
             * Selector for the delivery-information container
             * @type {string}
             */
            aboDeliveryInfoSelector: '.product--buybox .abo--info-wrapper',

            /**
             * Selector for the product quantity select box
             * @type {string}
             */
            aboQuantitySelectBoxSelector: 'select.abo--quantity-select',

            /**
             * Selector for the CustomProducts option manager plugin element
             * @type {string}
             */
            customProductsOptionManagerSelector: '*[data-swag-custom-products-option-manager="true"]',

            /**
             * Selector for the pseudo price of the subscription price
             * @type {string}
             */
            aboPseudoPriceSelector: '.abo--pseudo-price',

            /**
             * Selector for the percentage of the pseudo price
             * @type {string}
             */
            aboPseudoPricePercentageSelector: '.abo--percentage-container span.percent',

            /**
             * Selector for the subscription price
             * @type {string}
             */
            aboPriceSelector: '.delivery-price--price',

            /**
             * Selector for normal product price without subscription
             * @type {string}
             */
            normalPriceSelector: '.abo--single-delivery-price span',

            /**
             * Class name to hide elements
             * @type {string}
             */
            hiddenClass: 'is--hidden'
        },

        /**
         * Plugin constructor
         */
        init: function () {
            var me = this,
                $aboCommerceData,
                $el,
                $quantity,
                $blockPrices;

            me.applyDataAttributes();

            $aboCommerceData = $(me.opts.aboCommerceDataSelector);
            if ($aboCommerceData.length) {
                me.registerEvents();

                me.aboData = window.JSON.parse($aboCommerceData.html());
                me.$deliveryIntervalContainer = $(me.opts.deliveryContainerSelector);
                me.$productBuybox = $(me.opts.aboDeliveryInfoSelector);

                me.checkForRadio();
                me.refreshDurationOptions();
            }

            $blockPrices = $(me.opts.aboCommerceBlockPricesDataSelector);
            if ($blockPrices.length) {
                me.blockPrices = window.JSON.parse($blockPrices.html());
            }

            $el = $(me.opts.singleDeliveryRadioSelector);
            $quantity = $el.parents(me.opts.buyBoxSelector).find(me.opts.aboQuantitySelectBoxSelector);

            if ($quantity) {
                me.changeQuantity($quantity);
            }
        },

        /**
         * Method to register all needed events
         */
        registerEvents: function () {
            var me = this,
                $body = $('body');

            $body.on(me.getEventName('click'), me.opts.singleDeliveryRadioSelector, $.proxy(me.onSingleDeliveryClick, me));
            $body.on(me.getEventName('click'), me.opts.aboDeliveryRadioSelector, $.proxy(me.onDeliveryClick, me));
            $body.on(me.getEventName('click'), me.opts.openAboInfoSelector, $.proxy(me.onOpenAboInfo, me));
            $body.on(me.getEventName('click'), me.opts.openAboDiscountLabel, $.proxy(me.onOpenAboInfo, me));
            $body.on(me.getEventName('change'), me.opts.selectFieldSelector, $.proxy(me.onChangeAboSelects, me));
        },

        /**
         * This event is triggered once the user changes either the delivery-interval- or the duration-interval-selection
         *
         * @param { Event } event
         */
        onChangeAboSelects: function (event) {
            var me = this,
                $el = $(event.target),
                // need the delivery interval
                deliveryInterval = window.parseInt($(me.opts.deliveryIntervalSelector).val(), 10),
                // need the duration interval
                $durationInterval = $(me.opts.durationIntervalSelector),
                durationInterval = window.parseInt($durationInterval.val(), 10),
                // need the product quantity
                $quantity = $el.parents(me.opts.buyBoxSelector).find(me.opts.aboQuantitySelectBoxSelector),
                quantity = window.parseInt($quantity.val(), 10),
                customProductsOptionManager = $(me.opts.customProductsOptionManagerSelector).data('plugin_optionManager'),
                deliveryTextValue,
                durationTextValue,
                options,
                $durationIntervalSelect,
                $deliveryIntervalUnit;

            // when delivery interval is changed
            if ($el.hasClass('abo--delivery-interval')) {
                options = me.getOptions(deliveryInterval);

                durationInterval = options['minPossibleDuration'];

                quantity = me.changeQuantity($quantity, $el);

                // set the select values
                $durationInterval.html(options['durations']);
                $durationIntervalSelect = $('.abo--duration-interval-select .js--fancy-select-text');
                $deliveryIntervalUnit = $('.abo--commerce-delivery-interval-unit span');

                if (deliveryInterval === 1) {
                    $durationIntervalSelect.html((durationInterval + 1) + $deliveryIntervalUnit.html());
                } else {
                    $durationIntervalSelect.html(durationInterval + $deliveryIntervalUnit.html());
                }
            }

            // Set the new values into the hidden fields
            $('input[name=sDeliveryInterval]').val(deliveryInterval);
            $('input[name=sDurationInterval]').val(durationInterval);

            // Refresh the price and the percentage
            me.refreshPriceDisplay(durationInterval);

            // calculate values for delivery duration text
            durationTextValue = (durationInterval / deliveryInterval) + 1;
            deliveryTextValue = durationTextValue * quantity;

            // change the display
            $('.abo--info .abo--info-delivery').html(deliveryTextValue);
            $('.abo--info .abo--info-duration').html(durationTextValue);
            $('.abo--info-additional .abo--info-delivery-additional-following').html(durationTextValue - 1);

            if (customProductsOptionManager) {
                customProductsOptionManager['_data']['swagAboCommerceDuration'] = durationInterval;
            }

            $.publish('plugin/swAboCommerce/onChangeAboSelects');
        },

        /**
         * This event is triggered when the user wants to open the additional information about the abo-prices
         *
         * @param { Event } event
         */
        onOpenAboInfo: function (event) {
            var me = this,
                $popup = $('.abo--price-separation-popup'),
                method = ($popup.hasClass(me.opts.hiddenClass)) ? 'removeClass' : 'addClass';

            event.preventDefault();

            $popup[method](me.opts.hiddenClass);

            $.publish('plugin/swAboCommerce/onOpenAboInfo');
        },

        /**
         * This event is triggered when the user clicks on the "Single delivery"-radio
         *
         * @param { Event } event
         */
        onSingleDeliveryClick: function (event) {
            var me = this,
                customProductsOptionManager = $(me.opts.customProductsOptionManagerSelector).data('plugin_optionManager'),
                $el,
                $quantity;

            if (event) {
                $el = $(event.target);
                $quantity = $el.parents(me.opts.buyBoxSelector).find(me.opts.aboQuantitySelectBoxSelector);
            }

            // Hide the abo-comboboxes
            me.$deliveryIntervalContainer.addClass(me.opts.hiddenClass);
            me.$productBuybox.addClass(me.opts.hiddenClass);

            $('.abo--hidden-values').attr('disabled', true);

            if ($quantity) {
                me.changeQuantity($quantity);
            }

            if (customProductsOptionManager) {
                delete customProductsOptionManager['_data']['swagAboCommerceDuration'];
            }

            $.publish('plugin/swAboCommerce/onSingleDeliveryClick');
        },

        /**
         * This event is triggered when the user clicks on the "Abo"-radio
         *
         * @param { Event } event
         */
        onDeliveryClick: function (event) {
            var me = this,
                customProductsOptionManager = $(me.opts.customProductsOptionManagerSelector).data('plugin_optionManager'),
                $el,
                $quantity;

            if (event) {
                $el = $(event.target);
                $quantity = $el.parents(me.opts.buyBoxSelector).find(me.opts.aboQuantitySelectBoxSelector);
            }

            // Show the abo-comboboxes
            me.$deliveryIntervalContainer.removeClass(me.opts.hiddenClass);
            me.$productBuybox.removeClass(me.opts.hiddenClass);

            me.setDurationOptions();

            $('.abo--hidden-values').attr('disabled', false);

            if ($quantity) {
                me.changeQuantity($quantity, $(me.opts.selectFieldSelector));
            }

            if (customProductsOptionManager) {
                customProductsOptionManager['_data']['swagAboCommerceDuration'] = window.parseInt($(me.opts.durationIntervalSelector).val(), 10);
            }

            $.publish('plugin/swAboCommerce/onDeliveryClick');
        },

        /**
         * Helper method to initially fix the duration-options
         */
        refreshDurationOptions: function () {
            $(this.opts.deliveryIntervalSelector).change();
        },

        /**
         * Refreshes the price and the percent discount display
         * in the AboCommerce component.
         *
         * @param {number} duration - Selected abo duration
         * @return void
         */
        refreshPriceDisplay: function (duration) {
            var me = this,
                price = me.getPrice(duration),
                discountPrice = price.discountPrice,
                $aboPseudoPriceContainer = $(me.opts.aboPseudoPriceSelector),
                percent,
                formattedOriginalPrice;

            if (typeof price === 'undefined') {
                return;
            }

            percent = price.discountPercentage;
            $.publish('plugin/swAboCommerce/onPercentageChange', [ percent ]);

            $aboPseudoPriceContainer[(percent > 0) ? 'removeClass' : 'addClass'](me.opts.hiddenClass);

            $(me.opts.aboPseudoPricePercentageSelector).html(me.numberFormat(percent, 2, ',', '.'));
            $(me.opts.aboPriceSelector).html(me.formatPrice(discountPrice));

            if (price.blockPrice) {
                formattedOriginalPrice = me.formatPrice(price.blockPrice);
                $aboPseudoPriceContainer.find('.original--price').html(formattedOriginalPrice);
                $(me.opts.normalPriceSelector).html(formattedOriginalPrice);
            }
        },

        /**
         * Helper method to set the quantity relative to the selected delivery-interval
         */
        changeQuantity: function(quantitySelect, intervalSelect) {
            var me = this,
                packUnit = quantitySelect.attr('data-packUnit'),
                maxPurchaseCore = window.parseInt(quantitySelect.attr('data-maxQuantity'), 10),
                maxQuantityPerWeek,
                isLimited,
                durationUnit,
                intervalValue,
                quantity,
                maxQuantity;

            if ($(me.opts.singleDeliveryRadioSelector).is(':checked')) {
                quantitySelect = me.createOptions(quantitySelect, maxPurchaseCore, packUnit);
                quantitySelect.val(1).change();
            } else if (intervalSelect) {
                quantity = window.parseInt(quantitySelect.val(), 10);
                isLimited = quantitySelect.attr('data-isLimited') === 'true';

                if (isLimited) {
                    maxQuantityPerWeek = window.parseInt(quantitySelect.attr('data-maxQuantityPerWeek'), 10);
                    durationUnit = quantitySelect.attr('data-durationUnit');
                    intervalValue = window.parseInt(intervalSelect.val(), 10);
                    maxQuantity = maxQuantityPerWeek * intervalValue;

                    // If it is set to month, we need to multiply with 4
                    if (durationUnit !== 'weeks') {
                        maxQuantity *= 4;
                    }

                    if (maxQuantity > maxPurchaseCore) {
                        maxQuantity = maxPurchaseCore;
                    }

                    quantitySelect = me.createOptions(quantitySelect, maxQuantity, packUnit);
                    quantitySelect.val(quantity).change();

                    // If the old chosen quantity is higher than the actual new max-quantity
                    if (quantity > maxQuantity) {
                        quantitySelect.val(maxQuantity).change();

                        return maxQuantity;
                    }
                }
            }

            $.publish('plugin/aboCommerce/onChangeQuantity');

            return quantity;
        },

        /**
         * Helper method to fill a select-box with new options
         */
        createOptions: function (select, counter, packUnit) {
            var options = [],
                i;

            for (i = 1; i < counter + 1; i++) {
                options.push("<option value='" + i + "'>" + i + '&nbsp;' + packUnit + '</option>');
            }

            select.html(options);

            $.publish('plugin/aboCommerce/onCreateOptions');
            return select;
        },

        /**
         * Helper method to create the options of a select-box dynamically
         * @param deliveryInterval
         * @returns {Object}
         */
        getOptions: function (deliveryInterval) {
            var me = this,
                durations = '',
                $intervalUnit = $('.abo--commerce-delivery-interval-unit span'),
                minPossibleDuration,
                i = me.aboData.minDuration;

            for (i; i < me.aboData.maxDuration + 1; i++) {
                // when the variable i modulo delivery interval is not zero, continue
                if (i % deliveryInterval) {
                    continue;
                }

                if (i >= me.aboData.minDuration && !minPossibleDuration) {
                    minPossibleDuration = i;
                }

                durations += '<option value=' + i + '>' + i + $intervalUnit.html() + '</option>';
            }

            $.publish('plugin/aboCommerce/onGetOptions');
            return { 'durations': durations, 'minPossibleDuration': minPossibleDuration };
        },

        /**
         * Helper method to trigger the "onDeliveryClick"-method when the radio is already selected on page-load (e.g. browser cache)
         */
        checkForRadio: function () {
            var me = this;
            if ($(me.opts.aboDeliveryRadioSelector).is(':checked')) {
                me.onDeliveryClick();
            }
        },

        /**
         * Helper method
         * @param {Number} n
         * @param {Number} prec
         * @returns {string}
         */
        toFixedFix: function(n, prec) {
            var k = Math.pow(10, prec);
            return (Math.round(n * k) / k).toString();
        },

        /**
         * Helper method to format a number to a price-format
         * @param {Number} number
         * @param {Number} decimals
         * @param {String} decPoint
         * @param {String} thousandsSep
         * @returns {String}
         */
        numberFormat: function (number, decimals, decPoint, thousandsSep) {
            var me = this,
                n = number,
                prec = decimals,
                sep = (typeof thousandsSep === 'undefined') ? ',' : thousandsSep,
                dec = (typeof decPoint === 'undefined') ? '.' : decPoint,
                s = (prec > 0) ? me.toFixedFix(n, prec) : me.toFixedFix(Math.round(n), prec),
                abs = me.toFixedFix(Math.abs(n), prec),
                _,
                i,
                decPos;

            n = !isFinite(+n) ? 0 : +n;
            prec = !isFinite(+prec) ? 0 : Math.abs(prec);
            if (abs >= 1000) {
                _ = abs.split(/\D/);
                i = _[0].length % 3 || 3;
                _[0] = s.slice(0, i + (n < 0)) + _[0].slice(i).replace(/(\d{3})/g, sep + '$1');
                s = _.join(dec);
            } else {
                s = s.replace('.', dec);
            }
            decPos = s.indexOf(dec);
            if (prec >= 1 && decPos !== -1 && (s.length - decPos - 1) < prec) {
                s += new Array(prec - (s.length - decPos - 1)).join(0) + '0';
            } else if (prec >= 1 && decPos === -1) {
                s += dec + new Array(prec).join(0) + '0';
            }
            return s;
        },

        /**
         * Determines the price for the selected duration
         *
         * @param {number} duration - User selected duration (optional)
         * @return {Object} - terminated price array
         * @private
         */
        getPrice: function (duration) {
            var me = this,
                prices = me.aboData.prices,
                result = prices[0],
                blockPrice = 0,
                tmpPrice,
                i,
                quantity,
                j,
                tmpBlockPrice;

            duration = duration || $('input[name=sDurationInterval]').val();
            duration = window.parseInt(duration, 10);
            quantity = $(me.opts.aboQuantitySelectBoxSelector).val() * 1;

            // Loop through the price array to determine the price
            // or percent discount
            for (i in prices) {
                tmpPrice = prices[i];
                tmpPrice.duration = window.parseInt(tmpPrice.duration, 10);

                if (duration >= tmpPrice.duration && me.blockPriceHelper(quantity, tmpPrice)) {
                    result = tmpPrice;
                }
            }

            // Check if we're dealing with block prices
            if (me.blockPrices && me.blockPrices.length) {
                for (j in me.blockPrices) {
                    tmpBlockPrice = me.blockPrices[j];

                    if (quantity >= (1 * tmpBlockPrice.from)) {
                        blockPrice = tmpBlockPrice;
                    }
                }

                result.discountPrice = blockPrice.price - result.discountAbsolute;
                result.blockPrice = blockPrice.price;
            }

            return result;
        },

        /**
         * Private Helper Mehthod wich determines right tempPrice
         * by checking the Quantity
         */
        blockPriceHelper: function (quantity, tmpPrice) {
            if (quantity >= tmpPrice.fromQuantity && (quantity <= tmpPrice.toQuantity || typeof tmpPrice.toQuantity === 'string')) {
                return true;
            }

            return false;
        },

        /**
         * Private helper method which will convert
         * a string to a floating number
         *
         * @private
         * @param {String|Number} str - String which needs to be converted
         * @param {Number} defaultVal
         * @return {Number} - Float based on the passed string
         */
        toFixed: function(str, defaultVal) {
            defaultVal = defaultVal || 1;
            str = str.replace(',', '.');

            if (isFinite(str)) {
                str = (1 * str);
            }
            return !isNaN(str) ? str : defaultVal;
        },

        /**
         * Private helper method which will convert a {number}
         * into a formatted price.
         *
         * @private
         * @param {Number} price - The price which needs to be formatted
         * @param {String} template - The template which should be used (optional)
         * @return {String|boolean}
         */
        formatPrice: function (price, template) {
            var me = this,
                newPrice;

            template = template || $('.abo--price-template-data').html();

            if (!price) {
                return false;
            }
            newPrice = Math.round(price * 100) / 100;
            if (isNaN(newPrice)) {
                newPrice = Math.round(me.toFixed(price) * 100) / 100;
            }
            newPrice = newPrice.toFixed(2);

            // we replace the price
            if (template.search('0,00') !== -1) {
                newPrice = template.replace('0,00', newPrice);
            } else {
                newPrice = template.replace('0.00', newPrice);
            }
            newPrice = newPrice.replace('.', ',');

            return newPrice;
        },

        /**
         * Helper method to set the options of the select-box dynamically
         */
        setDurationOptions: function () {
            var me = this,
                container = $(me.opts.deliveryContainerSelector),
                deliverySelect = container.find(me.opts.deliveryIntervalSelector),
                durationSelect = container.find(me.opts.durationIntervalSelector);

            var durations = me.getOptions(deliverySelect.find('option:selected').val());
            durationSelect.html(durations['durations']);
        },

        /**
         * Destroys the plugin
         */
        destroy: function () {
            var me = this,
                $body = $('body');

            $body.off(me.getEventName('click'), me.opts.singleDeliveryRadioSelector);
            $body.off(me.getEventName('click'), me.opts.aboDeliveryRadioSelector);
            $body.off(me.getEventName('click'), me.opts.openAboInfoSelector);
            $body.off(me.getEventName('click'), me.opts.openAboDiscountLabel);
            $body.off(me.getEventName('change'), me.opts.selectFieldSelector);

            me._destroy();
        }
    });

    $('.content.product--details').swAboCommerce();

    $.subscribe('plugin/swQuickview/onProductLoaded', function() {
        StateManager.addPlugin('*[data-view=main] .content.product--details', 'swAboCommerce');
    });

    $.subscribe('plugin/swAjaxVariant/onBeforeRequestData', function() {
        $('.content.product--details').data('plugin_swAboCommerce').destroy();
    });

    $.subscribe('plugin/swAjaxVariant/onRequestData', function() {
        $('.content.product--details').swAboCommerce();
    });
})(jQuery, window);
