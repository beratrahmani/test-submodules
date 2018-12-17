;(function($, window, document, undefined) {
    'use strict';

    var pluginName = 'swCover',

        defaults = {

            /**
             * The source set for the different image sizes
             */
            srcSet: null,

            /**
             * The position origin for the image
             */
            position: 'center center',

            /**
             * You can decide to use the image ratio for the element size.
             * By default the element stretches to the width and height of its parent.
             */
            useImageRatio: false,

            /**
             * The CSS class for the canvas element
             */
            canvasCls: 'cover--canvas',

            /**
             * The CSS selector for the parent container.
             */
            parentSelector: '.bg--image',

            /**
             * The largest screen size for detection
             */
            largestScreenSize: 5160
        },

        $window = $(window);

    function Plugin(element, options) {
        var me = this;

        me.el = element;
        me.$el = $(element);

        me.opts = $.extend({}, defaults, options);

        me.init();

        return me;
    }

    Plugin.prototype.init = function () {
        var me = this;

        me.applyDataAttributes();

        if (!me.opts.srcSet.length) {
            return false;
        }

        me.sources = {};
        me.currentSrc = null;

        me.image = new Image();
        me.image.onload = $.proxy(me.onImageLoad, me);

        me.createCanvas();
        me.createSources(me.opts.srcSet);
        me.registerEvents();
        me.render();
    };

    Plugin.prototype.applyDataAttributes = function() {
        var me = this, attr;

        $.each(me.opts, function(key) {
            attr = me.$el.attr('data-' + key);

            if (typeof attr === 'undefined') {
                return true;
            }

            me.opts[key] = attr;
        });
    };

    Plugin.prototype.registerEvents = function() {
        var me = this;

        $window.on('resize.' + pluginName, $.proxy(me.render, me));

        $.subscribe('plugin/swEmotionLoader/onShowEmotion', $.proxy(me.render, me));
    };

    Plugin.prototype.createSources = function(sourceSet) {
        var me = this,
            srcSet = sourceSet || me.opts.srcSet,
            sources = srcSet.split(', ');

        $.each(sources, function(index, value) {
            var src = value.split(' '),
                key = (src[1] === 'base') ? 'base' : parseInt(src[1]),
                type = (src[2] && src[2] === '2x') ? '2x' : 'src';

            if (!me.sources[key]) {
                me.sources[key] = {};
            }
            me.sources[key][type] = src[0];
        });

        return me.sources;
    };

    Plugin.prototype.onImageLoad = function () {
        var me = this;

        me.setElementSizeByRatio();
    };

    Plugin.prototype.createCanvas = function() {
        var me = this;

        me.$canvas = $('<div>');

        me.$canvas.addClass(me.opts.canvasCls).appendTo(me.$el);
    };

    Plugin.prototype.render = function() {
        var me = this,
            currentSource = me.getCurrentSource();

        if (currentSource !== me.currentSrc) {
            me.image.src = currentSource;

            me.$canvas.css({
                'background-image': 'url(' + currentSource + ')',
                'background-position': me.opts.position
            });

            me.currentSrc = currentSource;
        }

        me.setElementSizeByRatio();
    };

    Plugin.prototype.setElementSizeByRatio = function () {
        var me = this,
            elWidth = me.$el.innerWidth(),
            imageRatio = me.image.width / me.image.height,
            imageHeight = elWidth / imageRatio;

        if (!me.image || !me.image.width) {
            return false;
        }

        me.$canvas.css('height', imageHeight);
    };

    Plugin.prototype.getCurrentSource = function() {
        var me = this,
            ratio = me.getDevicePixelRatio(),
            elWidth = me.$el.innerWidth(),
            sourceWidth = me.opts.largestScreenSize,
            source;

        $.each(me.sources, function(key) {
            if (key === 'base') {
                return true;
            }

            var width = parseInt(key);

            if (width >= elWidth && width < sourceWidth) {
                sourceWidth = width;
            }
        });

        source = me.sources[sourceWidth] || me.sources['base'];

        return (ratio > 1 && source['2x'] !== undefined) ? source['2x'] : source['src'];
    };

    Plugin.prototype.getDevicePixelRatio = function() {
        if (window.devicePixelRatio !== undefined) {
            return window.devicePixelRatio;
        }

        if (window.screen.systemXDPI !== undefined &&
            window.screen.logicalXDPI !== undefined &&
            window.screen.systemXDPI > window.screen.logicalXDPI) {
            return window.screen.systemXDPI / window.screen.logicalXDPI;
        }

        return 1;
    };

    $.fn[pluginName] = function(options) {
        return this.each(function () {
            var element = this,
                pluginData = $.data(this, 'plugin_' + pluginName);

            if (!pluginData) {
                $.data(element, 'plugin_' + pluginName, new Plugin(element, options));
            }
        });
    };

    $(function() {
        $('*[data-cover="true"]').swCover();

        $.subscribe('plugin/swEmotionLoader/onInitEmotion', function(event, plugin) {
            var mode = plugin.$emotion.attr('data-gridMode');

            $('*[data-cover="true"]').swCover({
                'useImageRatio': mode === 'rows'
            });
        });
    });
})(jQuery, window, document);
