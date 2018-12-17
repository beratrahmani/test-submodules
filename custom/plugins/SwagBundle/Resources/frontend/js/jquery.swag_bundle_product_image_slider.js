;(function($, window) {
    'use strict';

    $.plugin('swagBundleSlider', {

        /** The default options */
        defaults: {
            /** string */
            imageSliderContainerClass: '.bundle--image-slider-container',

            /** string */
            imageContainerClass: '.bundle--container-item',

            /** string */
            nextButtonClass: '.bundle--arrow-next',

            /** string */
            prevButtonClass: '.bundle--arrow-prev',

            /** string */
            productImageProductIdSelector: 'data-bundleImageProductId',

            /** number */
            imageContainerMargin: 30,

            /** number */
            slideTime: 300,

            /** number */
            windowResizeTimeOut: 200,

            /** number */
            showAndHideDuration: 200
        },

        /**
         * Initializes the plugin
         */
        init: function() {
            var me = this;

            // Applies HTML data attributes to the default options
            me.applyDataAttributes();

            me.findElements();
            me.handleNavigationButtonVisibility();
            me.updateSliderProperties();
            me.registerEventListeners();

            me.$sliderContainer.css('width', me.sliderContainerWidth);
            me.hiddenImagesContainer = [];

            me.reconfigureView();
        },

        /**
         * collects and sets properties
         */
        updateSliderProperties: function() {
            var me = this;

            me.sliderContainerWidth = me.calculateSliderContainerWidth();
            me.sliderStepWith = me.sliderContainerWidth / me.imageContainerElements.length;
            me.sliderMaxRightPos = me.sliderContainerWidth - me.$el.width();
        },

        /**
         * collects and sets properties which are jQuery elements
         */
        findElements: function() {
            var me = this;

            me.$nextButton = me.$el.find(me.opts.nextButtonClass);
            me.$prevButton = me.$el.find(me.opts.prevButtonClass);
            me.$sliderContainer = me.$el.find(me.opts.imageSliderContainerClass);
            me.$window = $(window);
            me.imageContainerElements = me.getImageContainers();
        },

        /**
         * Registers all event listeners
         */
        registerEventListeners: function() {
            var me = this;

            me._on(me.$window, 'resize', $.proxy(me.onWindowResize, me));
            me._on(me.$nextButton, 'click', $.proxy(me.onNextButtonClick, me));
            me._on(me.$prevButton, 'click', $.proxy(me.onPrevButtonClick, me));
            me._on(me.$sliderContainer, 'touchstart mousedown', $.proxy(me.onTouchStart, me));
            me._on(me.$sliderContainer, 'touchmove mousemove', $.proxy(me.onTouchMove, me));
            me._on(me.$sliderContainer, 'touchend mouseup mouseleave', $.proxy(me.onTouchEnd, me));

            $.subscribe('swagBundle/productSelection/change', $.proxy(me.onProductSelectionChange, me));
            $.subscribe('swagBundle/bundleVisibility/change', $.proxy(me.onBundleVisibilityChange, me));
        },

        /**
         * Updates the properties and reconfigures the view
         */
        onBundleVisibilityChange: function() {
            var me = this;

            me.updateSliderProperties();
            me.reconfigureView();
        },

        /**
         * OnTouchStart get the current x position
         *
         * @param {TouchEvent} event
         */
        onTouchStart: function(event) {
            var me = this;

            if (!event.changedTouches) {
                return;
            }

            me.xCurrentPosition = event.changedTouches[0].pageX;
        },

        /**
         * OnTouchMove get the current position, calculate the differences
         * and move the slider to the new position.
         *
         * @param {TouchEvent} event
         */
        onTouchMove: function(event) {
            var me = this,
                leftPosition = parseInt(me.$sliderContainer.css('left'), 10),
                newPosition, direction, differenceLeft, differenceRight;

            if (!event.changedTouches) {
                return;
            }

            newPosition = event.changedTouches[0].pageX;
            direction = me.getDirection(newPosition);
            differenceLeft = me.xCurrentPosition - newPosition;
            differenceRight = -1 * differenceLeft;

            me.xCurrentPosition = newPosition;

            if (direction) {
                me.moveSliderLeft(leftPosition, differenceLeft);
                return;
            }

            me.moveSliderRight(leftPosition, differenceRight);
        },

        /**
         * OnTouchEnd update the properties and button visibility.
         */
        onTouchEnd: function(event) {
            var me = this;

            if (!event.changedTouches) {
                return;
            }

            me.updateSliderProperties();
            me.handleNavigationButtonVisibility();
        },

        /**
         * Moves the slider to the right by the difference
         *
         * @param {Number} leftPosition
         * @param {Number} differenceRight
         */
        moveSliderRight: function(leftPosition, differenceRight) {
            var me = this;

            if (leftPosition > 0) {
                return;
            }

            me.$sliderContainer.css(
                { left: '+=' + differenceRight }
            );
        },

        /**
         * Moves the slider to the left by the difference
         *
         * @param {Number} leftPosition
         * @param {Number} differenceLeft
         */
        moveSliderLeft: function(leftPosition, differenceLeft) {
            var me = this;

            if (leftPosition < '-' + me.sliderMaxRightPos) {
                return;
            }

            me.$sliderContainer.css(
                { left: '-=' + differenceLeft }
            );
        },

        /**
         * Compares the startPosition with the newPosition to get the direction of the move
         * returns false for right
         * returns true for left
         *
         * @param {Number} newPosition
         * @returns {Boolean}
         */
        getDirection: function(newPosition) {
            var me = this;

            return me.xCurrentPosition > newPosition;
        },

        /**
         * On window resize trigger a timeout for a later reconfiguration of the view
         */
        onWindowResize: function() {
            var me = this;

            if (me.timeOut) {
                clearTimeout(me.timeOut);
            }

            me.timeOut = setTimeout($.proxy(me.reconfigureView, me), me.windowResizeTimeOut);
        },

        /**
         * Reconfigure the view
         */
        reconfigureView: function() {
            var me = this;

            me.$prevButton[me.$el.width() >= me.sliderContainerWidth ? 'hide' : 'show']();
            me.$nextButton[me.$el.width() >= me.sliderContainerWidth ? 'hide' : 'show']();

            me.$sliderContainer.css('left', 0);
            me.timeOut = null;

            if (me.$el.width() >= me.sliderContainerWidth) {
                return;
            }

            me.updateSliderProperties();
            me.handleNavigationButtonVisibility();
        },

        /**
         * OnProductSelectionChange handle the displaying images
         *
         * @param {Event} event
         * @param {Plugin} plugin
         * @param {Boolean} newValue
         * @param {Number} bundleProductId
         */
        onProductSelectionChange: function(event, plugin, newValue, bundleProductId) {
            var me = this,
                elementList = newValue ? me.hiddenImagesContainer : me.imageContainerElements,
                targetList = newValue ? me.imageContainerElements : me.hiddenImagesContainer,
                container = me.getImageContainer(elementList, bundleProductId);

            if ($.isEmptyObject(container)) {
                return;
            }

            container.element.stop()[newValue ? 'show' : 'hide']({
                duration: me.opts.showAndHideDuration
            }, $.proxy(me.updateSlider(elementList, targetList, container)));
        },

        /**
         * Updates the slider after the show / hide
         *
         * @param {Array} elementList
         * @param {Array} targetList
         * @param {Object} container
         */
        updateSlider: function(elementList, targetList, container) {
            var me = this;

            elementList.splice(container.index, 1);
            targetList.push(container.element);
            me.updateSliderProperties();
            me.reconfigureView();
        },

        /**
         * $nextButton click handler
         * checks if a animation is in progress
         * and triggers a new animation
         */
        onNextButtonClick: function() {
            var me = this,
                leftPos = parseInt(me.$sliderContainer.css('left'), 10),
                positiveLeftPos = leftPos === 0 ? 0 : -1 * leftPos,
                endPointDifference = me.sliderMaxRightPos - positiveLeftPos;

            if (leftPos > '-' + me.sliderMaxRightPos && !me.isAnimating) {
                me.isAnimating = true;

                me.$sliderContainer.animate(
                    { left: '-=' + (endPointDifference < me.sliderStepWith ? endPointDifference : me.sliderStepWith) },
                    me.opts.slideTime,
                    $.proxy(me.afterAnimation, me)
                );
            }
        },

        /**
         * $prevButton click handler
         * checks if a animation is in progress
         * and triggers a new animation
         */
        onPrevButtonClick: function() {
            var me = this,
                leftPos = parseInt(me.$sliderContainer.css('left'), 10),
                positiveLeftPos = -1 * leftPos;

            if (leftPos >= 0 || me.isAnimating) {
                return;
            }

            me.isAnimating = true;
            me.$sliderContainer.animate(
                { left: '+=' + (positiveLeftPos < me.sliderStepWith ? positiveLeftPos : me.sliderStepWith) },
                me.opts.slideTime,
                $.proxy(me.afterAnimation, me)
            );
        },

        /**
         * After animation set the isAnimating property to false,
         * so we can indicate if a animation is in progress
         */
        afterAnimation: function() {
            var me = this;

            me.isAnimating = false;
            me.handleNavigationButtonVisibility();
        },

        /**
         * calculates the slider container width with the amount of image containers and his margin
         *
         * @returns {Number}
         */
        calculateSliderContainerWidth: function() {
            var me = this,
                width = 0;

            $.each(me.imageContainerElements, function(index, element) {
                width += element.width();
            });

            return width + (me.opts.imageContainerMargin * me.imageContainerElements.length);
        },

        /**
         * Select and collects all imageContainer as array for a later calculation
         *
         * @returns {Array}
         */
        getImageContainers: function() {
            var me = this, elements = [],
                htmlElements;

            htmlElements = me.$el.find(me.opts.imageContainerClass);
            $.each(htmlElements, function(index, element) {
                elements.push($(element));
            });

            return elements;
        },

        /**
         * finds a specific imageContainer element
         *
         * @param {Array} elements
         * @param {Number} bundleProductId
         * @returns {Object}
         */
        getImageContainer: function(elements, bundleProductId) {
            var me = this,
                imageProductId,
                result = {};

            $.each(elements, function(index, $container) {
                imageProductId = parseInt($container.attr(me.opts.productImageProductIdSelector), 10);
                if (imageProductId === bundleProductId) {
                    result = {
                        index: index,
                        element: $container
                    };

                    return false;
                }
            });

            return result;
        },

        /**
         * Handles the navigation button visibility
         */
        handleNavigationButtonVisibility: function() {
            var me = this,
                leftPos = parseInt(me.$sliderContainer.css('left'), 10),
                maxRightPosAsString = '' + me.sliderMaxRightPos,
                maxRightPos = maxRightPosAsString.indexOf('-') >= 0 ? me.sliderMaxRightPos : '-' + me.sliderMaxRightPos;

            me.$prevButton[leftPos >= 0 ? 'hide' : 'show']();
            me.$nextButton[leftPos <= maxRightPos ? 'hide' : 'show']();
        },

        destroy: function() {
            this._destroy();
        }
    });

    /** Plugin starter */
    $(function() {
        StateManager.addPlugin('*[data-swagBundleSlider="true"]', 'swagBundleSlider');
    });
}(jQuery, window));
