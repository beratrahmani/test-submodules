/**
 * Disable anchor elements with a preloading indicator.
 *
 * Usage:
 * <a data-preloader-anchor="true">Anchor</>
 */
$.plugin('preloaderAnchor', {
    defaults: {
        loaderCls: 'js--loading',

        disabledCls: 'is--disabled'
    },

    init: function() {
        var me = this;

        me.applyDataAttributes();

        me._on(me.$el, 'click', $.proxy(me.onAnchorClick, me));
    },

    onAnchorClick: function(event) {
        var me = this;

        if(me.$el.hasClass(me.opts.disabledCls)) {
            event.preventDefault();
            return;
        }

        me.$el.html(me.$el.text() + '<div class="' + me.opts.loaderCls + '"></div>').addClass(me.opts.disabledCls);
    },

    destroy: function() {
        var me = this;
        me._destroy();
    }
});

$('*[data-preloader-anchor="true"]').preloaderAnchor();
