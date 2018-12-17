/**
 * global: StateManager
 *
 * Additional modal to add a confirm box
 *
 * The open function has a parameter which can be null or a handler with the function cancel and confirm.
 * If the handler is not null, elements with the classes
 *      "b2b--cancel-action" and
 *      "b2b--confirm-action"
 * in the confirm box trigger the handlers functions by a click event.
 */
;(function ($, window) {
    'use strict';

    var $html = $('html');

    $.b2bConfirmModal = {
        _$modalBox: null,

        _$content: null,

        _$handler: null,

        _$previousOverlay: null,

        defaults: {
            cancelModalSelector: '.b2b--cancel-action',

            confirmModalSelector: '.b2b--confirm-action'
        },

        onButtonCancel: function () {
            var me = this;

            if (!me._$handler) {
                return;
            }

            me._$handler.cancel();
        },

        onButtonConfirm: function () {
            var me = this;

            if (!me._$handler) {
                return;
            }

            me._$handler.confirm();
        },

        open: function (content, handler) {
            var me = this,
                $modalBox = me._$modalBox,
                opts = me.defaults;

            me._$handler = handler;

            if (!$modalBox) {
                me._$previousOverlay = $.overlay.overlay;

                $.overlay.open($.extend({}, {
                    closeOnClick: opts.closeOnOverlay,
                    onClose: $.proxy(me.onOverlayClose, me),
                    overlayCls: 'js--overlay b2bConfirmOverlay'
                }));

                me.initModalBox();
                me.registerEvents();

                $modalBox = me._$modalBox;
            }

            $modalBox.toggleClass('sizing--auto', false);
            $modalBox.toggleClass('sizing--fixed', false);
            $modalBox.toggleClass('sizing--content', true);
            $modalBox.toggleClass('no--header', true);

            $modalBox.css('width', 600);
            $modalBox.css('height', 'auto');
            $modalBox.css('display', 'block');

            me._$content.html(content);

            $modalBox.find(me.defaults.confirmModalSelector).click($.proxy(me.onButtonConfirm, me));
            $modalBox.find(me.defaults.cancelModalSelector).click($.proxy(me.onButtonCancel, me));

            me.center();
            window.setTimeout(me.center.bind(me), 25);

            me.setTransition({opacity: 1}, 500, 'linear');

            $html.addClass('no--scroll');

            return me;
        },

        close: function () {
            var me = this,
                $modalBox = me._$modalBox;

            $.overlay.close();
            $html.removeClass('no--scroll');
            
            if ($modalBox !== null) {
                me.setTransition({
                    opacity: 0
                }, 500, 'linear', function () {
                    $modalBox.css('display', 'none');
                    $modalBox.remove();
                    me._$content = null;
                    me._$modalBox = null;
                    me._$handler = null;
                });
            }

            $.overlay.overlay = me._$previousOverlay;
            me._$previousOverlay = null;
        },

        setTransition: function (css, duration, animation, callback) {
            var me = this,
                $modalBox = me._$modalBox;

            if (!$.support.transition) {
                $modalBox.stop(true).animate(css, duration, animation, callback);
                return;
            }

            $modalBox.stop(true).transition(css, duration, animation, callback);
        },

        initModalBox: function () {
            var me = this;

            me._$modalBox = $('<div>', {
                'class': 'js--modal modal--confirm b2bConfirmModal'
            });

            me._$content = $('<div>', {
                'class': 'content'
            }).appendTo(me._$modalBox);

            $('body').append(me._$modalBox);
        },

        registerEvents: function () {
            var me = this,
                $window = $(window);

            $window.on('keydown.modal', $.proxy(me.onKeyDown, me));
            StateManager.on('resize', me.center(), me);

            StateManager.registerListener({
                state: 'xs',
                enter: function() {
                    me._$modalBox.addClass('is--fullscreen');
                },
                exit: function () {
                    me._$modalBox.removeClass('is--fullscreen');
                }
            });
        },

        onKeyDown: function (event) {
            var me = this,
                keyCode = event.which,
                keys = [27],
                len = keys.length,
                i = 0;

            for (; i < len; i++) {
                if (keys[i] === keyCode) {
                    me.close();
                }
            }
        },

        center: function () {
            var me = this,
                $modalBox = me._$modalBox;

            $modalBox.css('top', ($(window).height() - $modalBox.height()) / 2);
        },

        onOverlayClose: function () {
            var me = this;
            me.close();
        }
    };
})(jQuery, window);

