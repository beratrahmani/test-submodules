;(function ($, window, document, StateManager) {
    'use strict';

    /**
     * Private vars.
     */
    var $window = $(window),
        $document = $(document),
        $html = $('html'),
        $body = $('body'),

        /**
         * The css property for transitions with the correct browser prefix.
         */
        transitionProperty = StateManager.getVendorProperty('transition', true),

        /**
         * The css property for transformations with the correct browser prefix.
         */
        transformProperty = StateManager.getVendorProperty('transform', true),

        /**
         * The correct mousewheel event for cross browser support.
         */
        mouseWheelEvent = (/Firefox/i.test(navigator.userAgent)) ? 'MozMousePixelScroll' : 'mousewheel';

    /**
     * Small helper functions.
     */
    $.fn.extend({

        /**
         * Returns true if the selector is a parent of the element.
         *
         * @param selector
         * @returns {boolean}
         */
        hasParent: function (selector) {
            return ($(this[0]).parents(selector).length > 0);
        }
    });

    /**
     * Clamps a value between a min and max value.
     *
     * @param value
     * @param min
     * @param max
     * @returns {number}
     */
    function clamp(value, min, max) {
        return Math.max(min, Math.min(max, value));
    }

    /**
     * Returns the transformation matrix as an array from a css transformation string value.
     *
     * @param transformationString
     * @returns {Array|{index: number, input: string}|*}
     */
    function matrixToArray(transformationString) {
        return transformationString.match(/(-?[0-9\.]+)/g); // eslint-disable-line
    }

    /**
     * Wrapper function for the setTimeout to delay different actions.
     * Especially used for applying css classes with transitions.
     *
     * @param callback
     * @param delay
     */
    function delay(callback, delay) {
        var me = this,
            time = delay || 1;

        window.setTimeout($.proxy(callback, me), time);
    }

    /**
     * Emotion StoryTelling Plugin
     *
     * This plugin extends the functionality of a normal emotion world.
     * It separates the emotion world into sections to let the user walk
     * through the emotion world step by step. Enables different navigation
     * possibilities to provide a smooth way to navigate through the sections.
     * The user can move through the sections by moving the mousewheel,
     * by pressing the arrow keys on the keyboard, or by dragging or swiping
     * the site via touch gestures. There is also a section navigator on the left
     * to move back and forward or to jump directly to a specific section.
     */
    $.plugin('swStoryTelling', {

        defaults: {

            /**
             * The number of rows in each section.
             *
             * @property rowsPerSection
             * @type {number}
             */
            rowsPerSection: 4,

            /**
             * The DOM selector for the page wrapper.
             *
             * @property pageWrapSelector
             * @type {string}
             */
            pageWrapSelector: '.page-wrap',

            /**
             * The DOM selector for elements in the shopping world.
             *
             * @property elementSelector
             * @type {string}
             */
            elementSelector: '.emotion--element',

            /**
             * The DOM selector for the navigation links of the section navigator.
             *
             * @property sectionNavLinkSelector
             * @type {string}
             */
            sectionNavLinkSelector: '.section-nav--link',

            /**
             * The DOM selectors for elements that need horizontal scrolling.
             *
             * @property horizontalSliderSelector
             * @type {string}
             */
            horizontalSliderSelector: '[data-orientation="horizontal"], .product-slider--content, .manufacturer--slider',

            /**
             * The DOM selectors for elements that need vertical scrolling.
             *
             * @property verticalSliderSelector
             * @type {string}
             */
            verticalSliderSelector: '[data-orientation="vertical"], [data-view="main"]',

            /**
             * The css class for the body to active the storytelling mode.
             *
             * @property storyTellingCls
             * @type {string}
             */
            storyTellingCls: 'is--storytelling',

            /**
             * The css class for the body to active hardware acceleration.
             *
             * @property hardwareAccelerationCls
             * @type {string}
             */
            hardwareAccelerationCls: 'is--hardware-accelerated',

            /**
             * The css class for the previous button in the section navigator.
             *
             * @property prevLinkCls
             * @type {string}
             */
            prevLinkCls: 'section-nav--link link--prev',

            /**
             * The css class for the next button in the section navigator.
             *
             * @property nextLinkCls
             * @type {string}
             */
            nextLinkCls: 'section-nav--link link--next',

            /**
             * The css class for the start button in the section navigator.
             *
             * @property startLinkCls
             * @type {string}
             */
            startLinkCls: 'section-nav--link link--start',

            /**
             * The css class for the section buttons in the section navigator.
             *
             * @property sectionLinkCls
             * @type {string}
             */
            sectionLinkCls: 'section-nav--link link--section',

            /**
             * The icon for the previous button.
             *
             * @property iconPrev
             * @type {string}
             */
            iconPrev: '<i class="icon--arrow-up"></i>',

            /**
             * The icon for the next button.
             *
             * @property iconNext
             * @type {string}
             */
            iconNext: '<i class="icon--arrow-down"></i>',

            /**
             * The icon for the start button.
             *
             * @property iconStart
             * @type {string}
             */
            iconStart: '<i class="icon--house"></i>',

            /**
             * The icon for the section buttons.
             *
             * @property iconSection
             * @type {string}
             */
            iconSection: '<i class="icon--record"></i>',

            /**
             * The url hash prefix for navigating directly to a specific section.
             *
             * @property urlHashPrefix
             * @type {string}
             */
            urlHashPrefix: '#emotion--',

            /**
             * The distance the user has to swipe to move to the next section on touch devices.
             *
             * @property swipeTolerance
             * @type {number}
             */
            swipeTolerance: 10,

            /**
             * The animation speed of the slide animation.
             *
             * @property animationSpeed
             * @type {number}
             */
            animationSpeed: 800,

            /**
             * The css transition for the slide animation.
             *
             * @property transition
             * @type {string}
             */
            transition: 'all 0.8s cubic-bezier(0.19, 1, 0.22, 1)'
        },

        /**
         * Plugin constructor.
         */
        init: function () {
            var me = this,
                opts = me.opts;

            me.applyDataAttributes();

            me.$pageWrap = $(opts.pageWrapSelector);

            me.$elements = me.$el.find(opts.elementSelector);

            me.$bannerElements = me.$el.find('[data-coverImage="true"]');
            me.$videoElements = me.$el.find('.emotion--video');

            me.$header = $('.header-main, .navigation-main');
            me.$footer = $('.footer-main');
            me.$advancedMenu = $('.advanced-menu');

            me.bufferedCall = false;
            me.isAnimating = false;
            me.isOffCanvas = false;
            me.isQuickView = false;
            me.isAdvancedMenu = false;
            me.touchMove = false;
            me.activeIndex = 0;
            me.scrollTop = 0;
            me.clientY = 0;

            me.initStorytelling();
            me.checkUrlHash();
            me.registerEvents();

            $.publish('plugin/swStoryTelling/onInit', [me]);
        },

        /**
         * Plugin update method.
         * Called by the StateManager and on window resize event.
         * Sets the new dimensions for the StoryTelling.
         */
        update: function () {
            var me = this;

            me.resetWindowScroll();

            me.viewportHeight = window.innerHeight;

            $html.height(me.viewportHeight);
            $body.height(me.viewportHeight);

            me.trackElements();

            me.scrollTop = me.getScrollTop();

            me.offsetTop = me.$el.offset().top + me.scrollTop;

            if (me.viewportHeight * 1 > me.$footer.height() * 1) {
                me.offsetBottom = me.$pageWrap.height() - me.viewportHeight;
            } else {
                me.offsetBottom = me.$pageWrap.height() - me.$footer.height();
            }

            me.createNavigation();

            me.$bannerElements.trigger('emotionResize');
            me.$videoElements.trigger('emotionResize');

            me.scrollTo(me.activeIndex, true);

            $.publish('plugin/swStoryTelling/onUpdate', [me]);
        },

        /**
         * Initializes the StoryTelling and sets all necessary css classes and styles.
         * Resets the normal window scroll position to null and updates the plugins
         * inside the emotion world.
         */
        initStorytelling: function () {
            var me = this;

            /**
             * Reset the normal window scroll position to null.
             */
            me.resetWindowScroll();

            /**
             * Getting the viewport height from window object for correct height in iOS browsers.
             */
            me.viewportHeight = window.innerHeight;

            /**
             * Setting the html and body height manually for iOS browsers.
             */
            $html.height(me.viewportHeight);
            $body.height(me.viewportHeight);

            /**
             * Add necessary classes for storytelling
             */
            $html.addClass(me.opts.storyTellingCls);
            $body.addClass(me.opts.storyTellingCls)
                .addClass(me.opts.hardwareAccelerationCls);

            /**
             * Set the necessary style on the pageWrap element.
             */
            me.$pageWrap.css(transformProperty, 'translateY(0)');
            me.$pageWrap.css(transitionProperty, me.opts.transition);

            me.trackElements();

            me.scrollTop = me.getScrollTop();

            me.offsetTop = me.$el.offset().top + me.scrollTop;
            if (me.viewportHeight * 1  > me.$footer.height() * 1) {
                me.offsetBottom = me.$pageWrap.height() - me.viewportHeight;
            } else {
                me.offsetBottom = me.$pageWrap.height() - me.$footer.height();
            }

            me.createNavigation();

            me.$bannerElements.trigger('emotionResize');
            me.$videoElements.trigger('emotionResize');

            StateManager.updatePlugin('*[data-product-slider="true"]', 'swProductSlider');
            StateManager.updatePlugin('*[data-image-slider="true"]', 'swImageSlider');

            window.picturefill();

            $.publish('plugin/swStoryTelling/onInitStorytelling', [me]);
        },

        trackElements: function () {
            var me = this,
                state = window.StateManager.getCurrentState(),
                $sizer = me.$el.find('.emotion--sizer-' + state),
                clsPrefix = '-' + state,
                i = 1;

            if ($sizer.length <= 0) {
                $sizer = me.$el.find('.emotion--sizer');
                clsPrefix = '';
            }

            me.rows = ~~$sizer.attr('data-rows');
            me.sections = Math.ceil(me.rows / me.opts.rowsPerSection);

            /**
             * Fix rows if the last section is not completely filled.
             */
            me.rows = me.sections * me.opts.rowsPerSection;

            me.emotionHeight = me.viewportHeight * me.sections;

            /**
             * Reset the sizer to stretch the shopping world to the correct size.
             */
            $sizer.height(me.emotionHeight);

            for (i; i <= me.rows; i++) {
                var height = 100 / me.rows * i,
                    top = 100 / me.rows * (i - 1);

                me.$elements.filter('.row' + clsPrefix + '-' + i).css('height', height + '%');
                me.$elements.filter('.start-row' + clsPrefix + '-' + i).css('top', top + '%');
            }

            $.publish('plugin/swStoryTelling/onTrackElements', [me]);
        },

        /**
         * Registers all necessary event listener.
         */
        registerEvents: function () {
            var me = this;

            $body.on(me.getEventName('click'), me.opts.sectionNavLinkSelector, $.proxy(me.onLinkClick, me));

            me._on(me.$pageWrap, 'touchstart', $.proxy(me.onTouchStart, me));
            me._on(me.$pageWrap, 'touchmove', $.proxy(me.onTouchMove, me));
            me._on(me.$pageWrap, 'touchend', $.proxy(me.onTouchEnd, me));

            me._on($document, 'keydown', $.proxy(me.onKeyPress, me));
            me._on($document, 'mousedown', $.proxy(me.onMouseDown, me));

            me._on($window, 'resize', $.proxy(me.buffer, me, me.update, 800));

            if (document.addEventListener) {
                me.mouseWheelListener = $.proxy(me.onMouseWheel, me);
                document.addEventListener(mouseWheelEvent, me.mouseWheelListener);
            }

            $.subscribe(me.getEventName('plugin/swAdvancedMenu/onOpenMenu'), $.proxy(me.onOpenAdvancedMenu, me));
            $.subscribe(me.getEventName('plugin/swAdvancedMenu/onCloseMenu'), $.proxy(me.onCloseAdvancedMenu, me));
            $.subscribe(me.getEventName('plugin/swAdvancedMenu/onCloseWithButton'), $.proxy(me.onCloseAdvancedMenu, me));

            $.subscribe(me.getEventName('plugin/swOffcanvasMenu/onBeforeOpenMenu'), $.proxy(me.onOpenOffCanvas, me));
            $.subscribe(me.getEventName('plugin/swOffcanvasMenu/onCloseMenu'), $.proxy(me.onCloseOffCanvas, me));

            $.subscribe(me.getEventName('plugin/swQuickview/onShowQuickView'), $.proxy(me.onOpenQuickView, me));
            $.subscribe(me.getEventName('plugin/swQuickview/onHideQuickView'), $.proxy(me.onCloseQuickView, me));

            $.subscribe(me.getEventName('plugin/swCollapsePanel/onOpen'), $.proxy(me.onFooterPanel, me));
            $.subscribe(me.getEventName('plugin/swCollapsePanel/onClose'), $.proxy(me.onFooterPanel, me));

            $.publish('plugin/swStoryTelling/onRegisterEvents', [me]);
        },

        /**
         * Called when the offCanvas menu gets opened.
         * Disables the features of the StoryTelling while the menu is open.
         *
         * @param event
         * @param offCanvas
         */
        onOpenOffCanvas: function (event, offCanvas) {
            var me = this;

            me.isOffCanvas = true;

            $body.removeClass(me.opts.hardwareAccelerationCls);
            me.$pageWrap.removeAttr('style').css('height', me.viewportHeight);

            /**
             * Set the positioning of the offCanvas element to absolute for a short time
             * to apply the position fixed again without the boundaries of transformed
             * parent elements. This provides the correct reflow of the element without the
             * issue of fixed elements inside transformed elements.
             */
            offCanvas.$offCanvas.css('position', 'absolute');
            delay(function () {
                offCanvas.$offCanvas.css('position', 'fixed');
            }, 10);
        },

        /**
         * Called when the offCanvas menu gets closed.
         * Resets the state for the StoryTelling.
         */
        onCloseOffCanvas: function () {
            var me = this;

            me.isOffCanvas = false;

            $body.addClass(me.opts.hardwareAccelerationCls);
            me.$pageWrap.removeAttr('style').css(transformProperty, 'translateY(0)');

            me.scrollTo(me.activeIndex, true);
        },

        /**
         * Called by opening or closing a panel in the footer on the mobile viewport.
         * Sets the new bottom offset and slides down the footer end.
         */
        onFooterPanel: function () {
            var me = this;

            me.scrollTop = me.getScrollTop();
            if (me.viewportHeight * 1 > me.$footer.height() * 1) {
                me.offsetBottom = me.$pageWrap.height() - me.viewportHeight;
            } else {
                me.offsetBottom = me.$pageWrap.height() - me.$footer.height();
            }

            me.scroll(me.offsetBottom);
        },

        /**
         * Called on opening the QuickView element.
         * Hides the section navigator.
         */
        onOpenQuickView: function () {
            var me = this;

            me.isQuickView = true;
            me.hideNavigation();
        },

        /**
         * Called on closing the QuickView element.
         * Shows the sections navigator back again.
         */
        onCloseQuickView: function () {
            var me = this;

            me.isQuickView = false;
            me.showNavigation();
        },

        /**
         * Called on touchstart event.
         * Initializes the drag & drop feature on touch devices.
         *
         * @param event
         */
        onTouchStart: function (event) {
            var me = this;

            if (me.isOffCanvas) {
                return;
            }

            me.$pageWrap.css(transitionProperty, 'none');

            me.scrollTop = me.getScrollTop();
            me.clientY = event.targetTouches[0].clientY;
            me.clientX = event.targetTouches[0].clientX;
        },

        /**
         * Called on touchmove event.
         * Handles the movement for the drag & drop feature on touch devices.
         *
         * @param event
         */
        onTouchMove: function (event) {
            var me = this;

            if (me.isOffCanvas) {
                return;
            }

            if (me.isQuickView) {
                event.preventDefault();
                return;
            }

            var $target = $(event.target),
                clientY = event.targetTouches[0].clientY,
                clientX = event.targetTouches[0].clientX,
                deltaY = me.clientY - clientY,
                deltaX = me.clientX - clientX,
                newScrollTop = clamp(me.scrollTop + deltaY, 0, me.$pageWrap.height() - me.viewportHeight);

            if (me.touchMove === false) {
                me.touchMove = (Math.abs(deltaX) > Math.abs(deltaY) && !me.touchMove) ? 'X' : 'Y';
            }

            if ($target.hasParent(me.opts.horizontalSliderSelector) && me.touchMove !== 'Y') {
                return;
            }

            if ($target.hasParent(me.opts.verticalSliderSelector) && me.touchMove !== 'X') {
                return;
            }

            // HTML element with scrollbars handling
            if ($target.parents('.html--content').height() != null || $target[0].className === 'html--content') {
                var scrollMax = $target.parents('.emotion--html')[0].scrollHeight;
                var scrollBottomOffset = $target.parents('.emotion--html').scrollTop() + $target.parents('.emotion--html').height();

                // Scroll shopping world if reached bottom of the html element
                if (scrollBottomOffset === scrollMax && deltaY > 0) {
                    event.preventDefault();
                    if (me.touchMove === 'Y') {
                        me.transform(me.$pageWrap, 'translateY(' + -newScrollTop + 'px)');
                    }
                } else if (scrollBottomOffset === scrollMax && deltaY < 0) {
                    return;
                    // (NEXT else if) Scroll shopping world if reached top of the html element
                } else if (scrollBottomOffset * 1 === $target.parents('.emotion--html').height() * 1 && deltaY < 0) {
                    if (me.touchMove === 'Y') {
                        me.transform(me.$pageWrap, 'translateY(' + -newScrollTop + 'px)');
                    }
                } else {
                    return;
                }
            }

            event.preventDefault();

            if (me.touchMove === 'Y') {
                me.transform(me.$pageWrap, 'translateY(' + -newScrollTop + 'px)');
            }
        },

        /**
         * Called on touchend event.
         * Slides to the next or previous section if the user dragged the site enough.
         */
        onTouchEnd: function () {
            var me = this,
                touchMove = me.touchMove;

            me.touchMove = false;

            if (touchMove === 'X' || me.isQuickView || me.isOffCanvas) {
                return;
            }

            var scrollTop = me.getScrollTop(),
                deltaY = Math.abs(scrollTop - me.scrollTop),
                method = (scrollTop < me.scrollTop) ? 'scrollPrev' : 'scrollNext';

            if (scrollTop >= me.offsetBottom) {
                me.scroll(scrollTop, true);
                return;
            }

            (deltaY > me.opts.swipeTolerance) ? me[method]() : me.scrollTo(me.activeIndex);
        },

        /**
         * Called on keydown event.
         * Enables the user to slide through the sections via arrow keys.
         *
         * @param event
         */
        onKeyPress: function (event) {
            var me = this,
                prev = [33, 38],
                next = [34, 40];

            if (me.isQuickView || me.isOffCanvas) {
                return;
            }

            if (prev.indexOf(event.keyCode) !== -1) {
                me.scrollPrev();
                event.preventDefault();
            } else if (next.indexOf(event.keyCode) !== -1) {
                me.scrollNext();
                event.preventDefault();
            }
        },

        /**
         * Called on mousedown event.
         * Prevents the standard behaviour from pressing down the mousewheel
         * to trigger the fast scroll feature in several browsers.
         *
         * @param event
         */
        onMouseDown: function (event) {
            if (event.button === 1) {
                event.preventDefault();
            }
        },

        /**
         * Called on mousewheel event.
         * Prevents the normal scrolling functionality in the browser and
         * lets the user slide through the sections via mousewheel.
         *
         * @param event
         */
        onMouseWheel: function (event) {
            var me = this,
                delta = (event.wheelDelta) ? event.wheelDelta / -120 : event.detail,
                direction = (delta < 0) ? -1 : +1;

            if (me.isQuickView) {
                return;
            }

            event.preventDefault();

            if (me.isAnimating || me.isOffCanvas || Math.abs(delta) < 0.7) {
                return;
            }

            if (me.isAdvancedMenu && me.viewportHeight < me.$advancedMenu.outerHeight()) {
                if (mouseWheelEvent === 'MozMousePixelScroll') {
                    window.scrollBy(0, event.detail);
                } else {
                    window.scrollBy(0, event.deltaY);
                }
                return;
            }

            me.scrollTo(me.activeIndex + direction);
        },

        /**
         * Called on click event.
         * Handles the navigation links in the section navigator.
         *
         * @param event
         */
        onLinkClick: function (event) {
            var me = this,
                $link = $(event.currentTarget),
                target = $link.attr('href'),
                index = me.activeIndex;

            event.preventDefault();

            if (target === '#start') index = 0;
            if (target === '#prev') index = me.activeIndex - 1;
            if (target === '#next') index = me.activeIndex + 1;
            if (target.indexOf('#section--') !== -1) index = parseInt(target.split('--')[1], 10);

            me.scrollTo(index, Math.abs(me.activeIndex - index) > 1);
        },

        /**
         * Sets the correct active state on a link in the section navigator.
         *
         * @param index
         */
        setActiveLink: function (index) {
            var me = this;

            me.$navLinks.removeClass('is--active');

            if (index <= 0) {
                me.$navLinks.filter('.link--start').addClass('is--active');
                return;
            }

            me.$navLinks.filter('.link--' + index).addClass('is--active');

            $.publish('plugin/swStoryTelling/onSetActiveLink', [me, index]);
        },

        /**
         * Checks the url for a hash link to navigate directly to a given section.
         */
        checkUrlHash: function () {
            var me = this,
                index,
                hash = window.location.hash.replace(me.opts.urlHashPrefix, '');

            if (!hash.length || hash === 'start') {
                me.scrollToTop(true);
                return;
            }

            if (hash === 'bottom') {
                me.scrollToBottom(true);
                return;
            }

            index = parseInt(hash, 10);

            if (window.isFinite(index)) {
                me.scrollTo(index, true);
            }

            $.publish('plugin/swStoryTelling/onCheckUrlHash', [me, hash]);
        },

        /**
         * Sets the url hash with the correct prefix.
         *
         * @param index
         */
        setUrlHash: function (index) {
            var me = this;

            window.location.hash = me.opts.urlHashPrefix + index;

            $.publish('plugin/swStoryTelling/onSetUrlHash', [me, index]);
        },

        /**
         * Resets the standard window scroll position to null.
         */
        resetWindowScroll: function () {
            $('html, body').scrollTop(0);
            $window.scrollTop(0);
        },

        /**
         * Returns the current position matrix of the page wrapper element to get the current position.
         *
         * @returns {number}
         */
        getScrollTop: function () {
            var me = this,
                matrix = matrixToArray(me.getTransformation(me.$pageWrap)) || [0, 0, 0, 0, 0, 0];

            return Math.abs(parseInt(matrix[5], 10));
        },

        /**
         * Slides the StoryTelling to the next section.
         */
        scrollNext: function () {
            var me = this;

            me.scrollTo(me.activeIndex + 1);

            $.publish('plugin/swStoryTelling/onScrollNext', [me]);
        },

        /**
         * Slides the StoryTelling to the previous section.
         */
        scrollPrev: function () {
            var me = this;

            me.scrollTo(me.activeIndex - 1);

            $.publish('plugin/swStoryTelling/onScrollPrev', [me]);
        },

        /**
         * Slides the StoryTelling back to the top.
         *
         * @param noAnimation
         */
        scrollToTop: function (noAnimation) {
            var me = this;

            me.scrollTo(0, noAnimation);

            $.publish('plugin/swStoryTelling/onScrollTop', [me]);
        },

        /**
         * Slides the StoryTelling down to the bottom.
         *
         * @param noAnimation
         */
        scrollToBottom: function (noAnimation) {
            var me = this;

            // Add 1, otherwise it will scroll to the last section and not to the footer
            me.scrollTo(me.sections + 1, noAnimation);

            $.publish('plugin/swStoryTelling/onScrollBottom', [me]);
        },

        /**
         * Slides the StoryTelling to the given index.
         *
         * @param index
         * @param noAnimation
         */
        scrollTo: function (index, noAnimation) {
            var me = this,
                noAnim = noAnimation || false,
                startIndex = (index <= 0),
                endIndex = (index > me.sections),
                i = (startIndex) ? 0 : (endIndex) ? me.sections + 1 : index,
                scroll = (startIndex) ? 0 : (endIndex) ? me.offsetBottom : me.getSectionOffset(index),
                hash = (startIndex) ? 'start' : (endIndex) ? 'bottom' : index;

            me.activeIndex = i;
            me.scroll(scroll, noAnim);
            me.setActiveLink(i);
            me.setUrlHash(hash);

            (noAnim) ? me.setVisibleContent(i) : delay($.proxy(me.setVisibleContent, me, i), 100);

            $.publish('plugin/swStoryTelling/onScrollTo', [me, index, noAnimation]);
        },

        /**
         * Slides the page wrapper element to the given position.
         * If the noAnimation parameter is set, the page will directly
         * jump to the section without transition.
         *
         * @param position
         * @param noAnimation
         */
        scroll: function (position, noAnimation) {
            var me = this,
                scroll = position || 0,
                scrollTop = me.getScrollTop(),
                noAnim = noAnimation || false,
                transition = (noAnim) ? 'none' : me.opts.transition;

            if (scroll === scrollTop) {
                return;
            }

            me.$pageWrap.css(transitionProperty, transition);

            delay(function () {
                me.isAnimating = true;
                me.transform(me.$pageWrap, 'translateY(' + -scroll + 'px)');
            });

            delay(function () {
                me.isAnimating = false;
            }, me.opts.animationSpeed);

            $.publish('plugin/swStoryTelling/onScroll', [me, position, noAnimation]);
        },

        getSectionOffset: function (sectionIndex) {
            var me = this;

            return me.offsetTop + ((sectionIndex - 1) * me.viewportHeight);
        },

        /**
         * Sets the visibility state of the page elements.
         * Elements which are not relevant for the current viewport
         * are set to be invisible for better painting performance.
         *
         * @param index
         */
        setVisibleContent: function (index) {
            var me = this,
                state = window.StateManager.getCurrentState(),
                section, i = 1, elements, isVisible;

            for (i; i <= me.rows; i++) {
                section = Math.ceil(i / me.opts.rowsPerSection);
                elements = me.$elements.filter('.start-row-' + state + '-' + i);
                isVisible = section >= me.activeIndex - 1 && section <= me.activeIndex + 1;

                elements[isVisible ? 'removeClass' : 'addClass']('is--invisible');
            }

            $.publish('plugin/swStoryTelling/onSetVisibleContent', [me, index]);
        },

        onOpenAdvancedMenu: function () {
            var me = this;

            me.isAdvancedMenu = true;
            me.hideNavigation();
        },

        onCloseAdvancedMenu: function () {
            var me = this;

            if (me.isAdvancedMenu) {
                me.isAdvancedMenu = false;
                me.resetWindowScroll();
                me.showNavigation();
            }
        },

        /**
         * Shows the section navigator.
         */
        showNavigation: function () {
            var me = this;

            me.$sectionNav.show();

            $.publish('plugin/swStoryTelling/onShowNavigation', [me]);
        },

        /**
         * Hides the section navigator.
         */
        hideNavigation: function () {
            var me = this;

            me.$sectionNav.hide();

            $.publish('plugin/swStoryTelling/onHideNavigation', [me]);
        },

        /**
         * Creates the template for the section navigator.
         */
        createNavigation: function () {
            var me = this,
                i = 1;

            if (me.$sectionNav) {
                me.$sectionNav.remove();
            }

            me.$sectionNav = $('<div>', {
                'class': 'emotion--section-nav'
            });

            $('<a>', {
                'href': '#prev',
                'class': me.opts.prevLinkCls,
                'html': me.opts.iconPrev
            }).appendTo(me.$sectionNav);

            $('<a>', {
                'href': '#start',
                'class': me.opts.startLinkCls,
                'html': me.opts.iconStart
            }).appendTo(me.$sectionNav);

            for (i; i <= me.sections; i++) {
                $('<a>', {
                    'href': '#section--' + i,
                    'class': me.opts.sectionLinkCls + ' link--' + i,
                    'html': me.opts.iconSection
                }).appendTo(me.$sectionNav);
            }

            $('<a>', {
                'href': '#next',
                'class': me.opts.nextLinkCls,
                'html': '<i class="icon--arrow-down"></i>'
            }).appendTo(me.$sectionNav);

            me.$sectionNav.appendTo($body);
            me.$navLinks = $(me.opts.sectionNavLinkSelector);

            $.publish('plugin/swStoryTelling/onCreateNavigation', [me]);
        },

        /**
         * Sets the given css transformation on the element.
         *
         * @param $el
         * @param transformation
         */
        transform: function ($el, transformation) {
            $el.css(transformProperty, transformation);
        },

        /**
         * Returns the current css transformation property of the element.
         *
         * @param $el
         * @returns {*}
         */
        getTransformation: function ($el) {
            return $el.css(transformProperty);
        },

        /**
         * Buffers the call of a function by a given amount of time.
         * Prevents a function from being called to often in a short time period.
         *
         * @param call
         * @param bufferTime
         */
        buffer: function (call, bufferTime) {
            var me = this;

            window.clearTimeout(me.bufferedCall);

            me.bufferedCall = window.setTimeout($.proxy(call, me), bufferTime);
        },

        /**
         * Destroy the plugin and remove all event listeners and css styles.
         */
        destroy: function () {
            var me = this;

            window.location.hash = '';
            window.clearTimeout(me.bufferedCall);

            me.$header.removeClass('is--invisible');
            me.$footer.removeClass('is--invisible');

            $body.removeClass(me.opts.storyTellingCls)
                .removeClass(me.opts.hardwareAccelerationCls)
                .removeAttr('style');

            $html.removeClass(me.opts.storyTellingCls)
                .removeAttr('style');

            me.$pageWrap.removeAttr('style');

            if (document.addEventListener) document.removeEventListener(mouseWheelEvent, me.mouseWheelListener);

            $.unsubscribe(me.getEventName('plugin/swAdvancedMenu/onOpenMenu'));
            $.unsubscribe(me.getEventName('plugin/swAdvancedMenu/onCloseMenu'));
            $.unsubscribe(me.getEventName('plugin/swAdvancedMenu/onCloseWithButton'));
            $.unsubscribe(me.getEventName('plugin/swOffcanvasMenu/onBeforeOpenMenu'));
            $.unsubscribe(me.getEventName('plugin/swOffcanvasMenu/onCloseMenu'));
            $.unsubscribe(me.getEventName('plugin/swQuickview/onShowQuickView'));
            $.unsubscribe(me.getEventName('plugin/swQuickview/onHideQuickView'));
            $.unsubscribe(me.getEventName('plugin/swCollapsePanel/onOpen'));
            $.unsubscribe(me.getEventName('plugin/swCollapsePanel/onClose'));

            me.$sectionNav.remove();

            me._destroy();

            $.publish('plugin/swStoryTelling/onDestroy', [me]);
        }
    });
})(jQuery, window, document, StateManager);
