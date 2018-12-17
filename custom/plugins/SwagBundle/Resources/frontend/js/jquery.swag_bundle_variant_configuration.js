;(function($, window, location) {
    'use strict';

    $.plugin('swagBundleVariantConfigurationSave', {

        /** The default options */
        defaults: {
            /**
             * @type String
             */
            saveConfigurationButtonClass: '.bundle--product-configuration-add',

            /**
             * @type String
             */
            configurationRowClass: '.bundle--product-configuration',

            /**
             * @type String
             */
            errorContainerClass: '.bundle--detail-error-container',

            /**
             * @type String
             */
            bundleProductIdDataSelector: 'data-bundleProductId',

            /**
             * @type String
             */
            isHiddenClass: 'is--hidden',

            /**
             * Selector for the swagBundle jQuery plugin
             *
             *  @type String
             */
            swagBundlePluginSelector: '*[data-swagBundle="true"]',

            /**
             * @type String
             */
            groupInputNameSelector: 'name',

            /**
             * @type String
             */
            groupValueInputSelector: 'select',

            /**
             * @type String
             */
            groupPrefix: 'group-',

            /**
             * @type String
             */
            saveProductConfigurationsUrl: '',

            /**
             * @type String
             */
            errorMessage: '',

            /**
             * @type Number
             */
            bundleId: -1,

            /**
             * @type String
             */
            bundleJQueryPluginName: 'plugin_swBundle',

            /**
             * @type String
             */
            bundleProductContainerSelector: '.products--content .detail--wrapper',

            /**
             * @type String
             */
            selectFieldSelector: ' .configuration-selector select',

            /**
             * @type String
             */
            bundleIdAttributeSelector: 'data-bundleId',

            /**
             * @type String
             */
            variantSelectionDefaultValueAttribute: 'data-defaultValue',

            /**
             * @type String
             */
            configuratorFormSelectSelector: 'form.configurator--form select'
        },

        /**
         * Initializes the plugin
         */
        init: function() {
            var me = this;

            // Applies HTML data attributes to the default options
            me.applyDataAttributes();

            me.findElements();

            me.registerEventListener();
        },

        /**
         * Collects and sets properties which are jQuery elements
         */
        findElements: function() {
            var me = this;

            me.$coreSelects = $(me.opts.configuratorFormSelectSelector);
            me.$coreSelects.each(function(index, element) {
                me[element.name] = $(element);
            });

            me.$bundleSelects = me.$el.find(me.opts.configurationRowClass + me.opts.selectFieldSelector);
            me.$bundleSelects.each(function(index, element) {
                me[element.name] = $(element);
            });

            me.$swagBundlePluginElement = $(me.opts.swagBundlePluginSelector);
            me.$errorContainer = me.$el.find(me.opts.errorContainerClass);
            me.$saveConfigurationButtons = me.$el.find(me.opts.saveConfigurationButtonClass);
            me.$saveConfigurationButtonList = me.createElementList(me.$saveConfigurationButtons);
            me.$configurationRowsList = me.createElementList(me.$el.find(me.opts.configurationRowClass));

            /** Gets the SwagBundle jQuery Plugin */
            me.swagBundlePlugin = me.$swagBundlePluginElement.data(me.opts.bundleJQueryPluginName);

            /** SwagBundle products */
            me.bundleProducts = me.$swagBundlePluginElement.find(me.opts.bundleProductContainerSelector + '[' + me.opts.bundleIdAttributeSelector + '=' + me.opts.bundleId + ']');
        },

        /**
         * Registers all event listeners
         */
        registerEventListener: function() {
            var me = this;

            me.$coreSelects.each(function(index, element) {
                me[element.name].on('change', $.proxy(me.onBaseProductChangeVariant, me));
            });

            me.$bundleSelects.each(function(index, element) {
                me[element.name].on('change', $.proxy(me.handleConfigurationSelectionEvent, me));
            });

            if (window.CSRF.checkToken()) {
                me._on(me.$saveConfigurationButtons, 'click', $.proxy(me.onSaveConfiguration, me));
                return;
            }

            $.subscribe('plugin/swCsrfProtection/init', $.proxy(me.registerEventListener, me));
        },

        /**
         * @param {Event} event
         */
        onBaseProductChangeVariant: function(event) {
            var me = this,
                coreSelect = me[event.currentTarget.name],
                bundleSelect = me[me.transformCoreSelectName(event.currentTarget.name)],
                currentConfiguration;

            if (bundleSelect.val() === coreSelect.val()) {
                return;
            }

            bundleSelect.val(coreSelect.val());

            currentConfiguration = me.collectConfigurations();
            me.saveConfiguration(currentConfiguration.groups, currentConfiguration.requestData);
        },

        /**
         * @param {string} coreSelectName
         * @return {string}
         */
        transformCoreSelectName: function(coreSelectName) {
            return coreSelectName.replace('[', '-').replace(']', '');
        },

        /**
         * The save configurationButton click handler.
         * Collects the variant configurations and save them by using
         * a ajax request
         *
         * @param {Event} event
         */
        onSaveConfiguration: function(event) {
            var me = this,
                result = me.collectConfigurations();

            event.preventDefault();

            $.loadingIndicator.open({
                closeOnClick: false
            });

            window.setTimeout(function() {
                me.saveConfiguration(result.groups, result.requestData);
            }, 10);
        },

        /**
         * Collects all productVariantConfigurations in a object
         *
         * The array key is the bundleProductId.
         * RequestData example:
         * Array
         * (
         *  [0] => Array
         *  (
         *      [group-8] => 50
         *      [group-11] => 77
         *  )
         *  [1] => Array
         *  (
         *      [group-6] => 28
         *      [group-8] => 50
         *  )
         *  [3] => Array
         *  (
         *      [group-6] => 15
         *      [group-7] => 27
         *  )
         * )
         *
         * @returns {Object}
         */
        collectConfigurations: function() {
            var me = this,
                requestData = {
                    bundleId: me.opts.bundleId,
                    productConfiguration: {}
                },
                groups = {};

            $.each(me.$configurationRowsList, function(index, $row) {
                var selects = me.createElementList($row.find(me.opts.groupValueInputSelector)),
                    bundleProductId = parseInt($row.attr(me.opts.bundleProductIdDataSelector), 10);

                requestData.productConfiguration[bundleProductId] = {};

                $.each(selects, function(selectsIndex, select) {
                    var name = select.attr(me.opts.groupInputNameSelector),
                        value = select.val();

                    requestData.productConfiguration[bundleProductId][name] = value;
                    groups[name.substring(me.opts.groupPrefix.length)] = value;
                });
            });

            return {groups: groups, requestData: requestData};
        },

        /**
         * Send a ajax request to save the current bundle product configurations
         *
         * @param {Object} groups
         * @param {Object} requestData
         */
        saveConfiguration: function(groups, requestData) {
            var me = this,
                url = me.swagBundlePlugin.setCurrentProtocol(me.opts.saveProductConfigurationsUrl);

            $.ajax({
                'url': url,
                'data': requestData,
                'dataType': 'html',
                'type': 'POST'
            }).done(function() {
                me.reloadPageWithSelectedVariant(groups);
            }).fail(function() {
                me.showErrorMessage();
                $.loadingIndicator.close();
            });
        },

        /**
         * Shows the errorMessageContainer
         */
        showErrorMessage: function() {
            var me = this;

            me.$errorContainer.removeClass(me.opts.isHiddenClass);
        },

        /**
         * Sets new values to the product variant select boxes and triggers the change event
         *
         * @param {Object} groups
         */
        reloadPageWithSelectedVariant: function(groups) {
            var $selectField,
                $currentField;

            $.each(groups, function(groupId, value) {
                $currentField = $('select[name="group[' + groupId + ']"]');
                if ($currentField && $currentField.length > 0) {
                    $selectField = $currentField;

                    if ($selectField.val() === value) {
                        return;
                    }

                    $selectField.val(value);
                }
            });

            if ($selectField) {
                // the variant of a product was changed in the bundle container and the customer is also on this detail page
                $.loadingIndicator.close();
                $selectField.trigger('change');
            } else {
                // the variant of another product was changed
                location.reload();
            }
        },

        /**
         * Creates from a HTML element list a jQuery element list.
         *
         * @param {Array} elements
         * @returns {Array}
         */
        createElementList: function(elements) {
            var elementList = [];

            $.each(elements, function() {
                elementList.push($(this));
            });

            return elementList;
        },

        /**
         * Check if the configuration options are changed or not
         *
         * If the configuration options are changed and are not the default options the basket button is enabled
         * otherwise it will be disabled
         */
        handleConfigurationSelectionEvent: function() {
            var me = this;

            // iterate over all products and get all configuration selection fields
            me.bundleProducts.each(function(index, item) {
                // define variables
                var $option,
                    product = $(item),
                    productConfiguration = product.find(me.opts.configurationRowClass),
                    configurationButton = product.find(me.opts.saveConfigurationButtonClass),
                    optionDefaultValues = [],
                    selectedValues = [];

                // if the product is not configurable continue
                if (!productConfiguration.length) {
                    return;
                }

                // iterate over all configuration options
                productConfiguration.find(me.opts.selectFieldSelector).each(function(index, option) {
                    $option = $(option);
                    // get default and selected options
                    optionDefaultValues[index] = $option.attr(me.opts.variantSelectionDefaultValueAttribute);
                    selectedValues[index] = $option.find(':selected').val();

                    // compare the arrays and enable or disable the button
                    if (!me.compareArray(optionDefaultValues, selectedValues)) {
                        configurationButton.prop('disabled', false);
                    } else {
                        configurationButton.attr('disabled', 'disabled');
                    }
                });
            });
        },

        /**
         * Helper function for handleConfigurationSelectionEvent() to compare 2 arrays
         *
         * @param {Array} arr1
         * @param {Array} arr2
         * @returns {Boolean}
         */
        compareArray: function(arr1, arr2) {
            return $(arr1).not(arr2).length === 0 && $(arr2).not(arr1).length === 0;
        },

        /**
         * Destroys the plugin and removes event listener
         */
        destroy: function() {
            var me = this;

            me.$coreSelects.each(function(index, element) {
                me[element.name].off('change');
            });

            me.$bundleSelects.each(function(index, element) {
                me[element.name].off('change');
            });

            me._destroy();
        }
    });

    /** Plugin starter */
    $(function() {
        StateManager.addPlugin('*[data-swagBundleVariantConfiguration="true"]', 'swagBundleVariantConfigurationSave');
    });
}(jQuery, window, location));
