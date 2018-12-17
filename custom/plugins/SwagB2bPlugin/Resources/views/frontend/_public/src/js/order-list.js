/**
 * This plugin shows the off-canvas cart and refresh the Cart Widget in the header
 */
$.plugin('b2bOrderList', {
    defaults: {
        orderListRemoteAddButtonSelector: '.orderlist--add',

        orderListGridAddToCartSelector: '.order-list--add-to-cart',

        orderListDetailQuantitySelector: '#sQuantity',

        alertHideDelay: 2000,

        snippets: {
            createPlaceholder: null,
            createError: null
        },

        inputContainerCls: 'b2b-orderlist-input-container',

        notificationContainerCls: 'b2b-orderlist-notification-container',

        orderlistDropdownSelector: '.b2b--orderlist-dropdown',

        keyMap: {
            esc: 27
        },

        inProgress: false
    },

    init: function () {
        var me = this,
            $orderlistDropdown = me.$el.find(me.opts.orderlistDropdownSelector);

        me.applyDataAttributes();

        me._on(me.$el.find(me.opts.orderListGridAddToCartSelector), 'submit', me.handleOrderListCreateOrder);

        if(!$orderlistDropdown.length) {
            return;
        }

        me.opts.snippets.createPlaceholder = $orderlistDropdown.data('new-placeholder');
        me.opts.snippets.createError = $orderlistDropdown.data('new-error');

        me._on(me.opts.orderlistDropdownSelector, 'change', $.proxy(me.changeOrderList, me));

        me._on(me.opts.orderListDetailQuantitySelector, 'change', $.proxy(me.handleDetailQuantity));

        me.$el.on(me.getEventName('click'), '.orderlist-create-abort', $.proxy(me.onOrderListCreateAbort, me));

        me.$el.on(me.getEventName('click'), '.orderlist-create-save', $.proxy(me.onOrderListCreateSubmit, me));

        me.$el.on(me.getEventName('keyup'), '.form--orderlist-add input', $.proxy(me.onOrderListCreateKeyUp, me));

        me.$el.on(me.getEventName('focus'), '.form--orderlist-add input', $.proxy(me.onOrderListCreateFocusIn, me));

        me.$el.on(me.getEventName('focusout'), '.form--orderlist-add input', $.proxy(me.onOrderListCreateFocusOut, me));

        me.removeAlertsDelayed();
    },

    removeAlertsDelayed: function() {
        var me = this,
            $alerts = me.$el.find('.alert'),
            $select = me.$el.find(me.opts.orderlistDropdownSelector),
            errorExists = me.$el.find('.is--error').length;

        if(errorExists) {
            $select.val($select.find('option:first').val());
            return;
        }

        if($alerts.length) {
            $select.attr('disabled', 'disabled');

            setTimeout(function() {
                $select.val($select.find('option:first').val());
                $alerts.slideUp('fast');
                $select.removeAttr('disabled');
            }, me.opts.alertHideDelay);
        }
    },

    changeOrderList: function (event) {
        var me = this,
            $select = $(event.currentTarget);

        if($select.val() == '_new_') {
            me.enableOrderListCreate($select);
        } else {
            $select.closest('form').submit();
        }
    },

    onOrderListCreateAbort: function(event) {
        var me = this,
            $select = me.$el.find(me.opts.orderlistDropdownSelector);

        event.preventDefault();

        me.disableOrderListCreate($select);
    },

    onOrderListCreateSubmit: function(event) {
        var me = this,
            $select = me.$el.find(me.opts.orderlistDropdownSelector),
            $submitButton = $(event.currentTarget),
            $form = $submitButton.closest('.form--orderlist-add'),
            $parentForm = $select.closest('form'),
            orderList = null;

        event.preventDefault();

        if(me.opts.inProgress) {
            return;
        }

        me.opts.inProgress = true;

        $.ajax({
            url: $form.attr('action'),
            method: $form.attr('method'),
            data: $form.serialize(),
            success: function(jsonResponse) {
                orderList = JSON.parse(jsonResponse);

                if(!orderList.orderListId) {
                    me.opts.inProgress = false;
                    me.showOrderListCreateError();
                    return;
                }

                $select.remove();
                $parentForm.append($('<input>', {
                    type: 'hidden',
                    name: 'orderlist',
                    value: orderList.orderListId
                }));

                $parentForm.submit();
            },
            error: function () {
                me.opts.inProgress = false;
                me.showOrderListCreateError();
            }
        });
    },

    onOrderListCreateKeyUp: function(event) {
        var me = this,
            $select = me.$el.find(me.opts.orderlistDropdownSelector);

        me.hideOrderListCreateError();

        if (event.keyCode !== me.defaults.keyMap.esc) {
            return;
        }

        me.disableOrderListCreate($select);
    },

    onOrderListCreateFocusIn: function(event) {
        var me = this,
            $abortButton = me.$el.find('.orderlist-create-abort');

        $abortButton.addClass('is--active');
    },

    onOrderListCreateFocusOut: function(event) {
        var me = this,
            $abortButton = me.$el.find('.orderlist-create-abort');

        $abortButton.removeClass('is--active');
    },

    enableOrderListCreate: function($select) {
        var me = this;

        $select.closest('.select-field').hide();
        $select.closest('.select-field').after($('<div>', {
            class: me.opts.inputContainerCls,
            html: $('<form>', {
                action: $select.attr('data-action-create'),
                method: 'post',
                class: 'form--inline form--orderlist-add',
                html: [
                    $('<input>', {
                        type: 'text',
                        name: 'name',
                        maxlength: 50,
                        required: true,
                        placeholder: me.opts.snippets.createPlaceholder
                    }),
                    $('<button>', {
                        class: 'btn is--default orderlist-create-abort',
                        type: 'button',
                        html: $('<i>', {
                            class: 'icon--cross'
                        })
                    }),
                    $('<button>', {
                        class: 'btn is--primary orderlist-create-save',
                        type: 'submit',
                        html: $('<i>', {
                            class: 'icon--arrow-right'
                        })
                    })
                ]
            })
        }));

        $('.form--orderlist-add').find('input').focus();

        CSRF.updateForms();
    },

    disableOrderListCreate: function($select) {
        var me = this;

        $select.val($select.find('option:first').val());
        $select.closest('.select-field').show();
        $select.closest('.group--actions').find('.' + me.opts.inputContainerCls).remove();
    },

    showOrderListCreateError: function() {
        var me = this;

        me.$el.find('.form--orderlist-add input').addClass('is--error');

        me.$el.find('.b2b-orderlist-input-container').append($('<div>', {
            class: 'orderlist-create-error',
            html: $('<i>', {
                class: 'icon--warning',
                title: me.opts.snippets.createError
            })
        }));
    },

    hideOrderListCreateError: function() {
        var me = this,
            $createInput = me.$el.find('.form--orderlist-add input');

        if(!$createInput.hasClass('is--error')) {
            return;
        }

        $createInput.removeClass('is-error');
        me.$el.find('.orderlist-create-error').remove();
    },

    handleDetailQuantity: function () {
        var $select = $(this),
            $remoteBox = $('[data-id="order-list-remote-box"]');

        if ($remoteBox.length) {
            $remoteBox.find('[name="products[0][quantity]"]').val($select.find(':selected').val());
        }
    },

    handleOrderListCreateOrder: function(event) {
        event.preventDefault();

        var $form = $(this);

        $.ajax({
            type: $form.attr('method'),
            url: $form.attr('action'),
            data: $form.serialize(),
            success: function() {
                var $collapseCart = $('*[data-collapse-cart="true"]');

                if ($collapseCart) {
                    $collapseCart.data('plugin_swCollapseCart').onMouseEnter(event);
                    $.publish('plugin/swAddArticle/onAddArticle');
                }
            }
        });
    },

    destroy: function() {
        var me = this;

        me.$el.off(me.getEventName('submit'), me.$el.find(me.opts.orderListGridAddToCartSelector));
        me.$el.off(me.getEventName('change'), me.opts.orderlistDropdownSelector);
        me.$el.off(me.getEventName('change'), me.opts.orderListDetailQuantitySelector);
        me.$el.off(me.getEventName('click'), '.orderlist-create-abort');
        me.$el.off(me.getEventName('click'), '.orderlist-create-save');
        me.$el.off(me.getEventName('keyup'), '.form--orderlist-add input');
        me.$el.off(me.getEventName('focus'), '.form--orderlist-add input');
        me.$el.off(me.getEventName('focusout'), '.form--orderlist-add input');

        me._destroy();
    }
});