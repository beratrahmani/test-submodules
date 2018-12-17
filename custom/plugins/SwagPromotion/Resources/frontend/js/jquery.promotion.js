;(function ($, undefined) {
    /**
     * add modal box on detail page for promotion detailed description
     */
    if ($('body').hasClass('is--ctl-detail')) {
        StateManager.addPlugin(
            '.promotion--description-box',
            'swModalbox',
            {
                mode: 'local',
                sizing: 'auto',
                additionalClass: 'promotion--detail-modal'
            },
            ['xl', 'l', 'm']
        );

        $.subscribe('plugin/swAjaxVariant/onRequestData', function () {
            StateManager.addPlugin(
                '.promotion--description-box',
                'swModalbox',
                {
                    mode: 'local',
                    sizing: 'auto',
                    additionalClass: 'promotion--detail-modal'
                },
                ['xl', 'l', 'm']
            );
        });

        /**
         * add off canvas menu on detail page for promotion detailed description
         */
        StateManager.addPlugin(
            '.promotion--description-box',
            'swOffcanvasMenu',
            {
                fullscreen: true,
                direction: 'fromRight',
                closeButtonSelector: '.close--off-canvas',
                offCanvasSelector: '.promotion--detail-offcanvas'
            },
            ['s', 'xs']
        );

        $.subscribe('plugin/swAjaxVariant/onRequestData', function () {
            StateManager.addPlugin(
                '.promotion--description-box',
                'swOffcanvasMenu',
                {
                    fullscreen: true,
                    direction: 'fromRight',
                    closeButtonSelector: '.close--off-canvas',
                    offCanvasSelector: '.promotion--detail-offcanvas'
                },
                ['s', 'xs']
            );
        });
    }

    /**
     * update plugins for detailed description on quick view
     */
    $.subscribe('plugin/swQuickview/onProductLoaded', function() {
        StateManager.addPlugin(
            '.promotion--description-box',
            'swModalbox',
            {
                mode: 'local',
                sizing: 'auto',
                additionalClass: 'promotion--detail-modal'
            }
        );
    });

    /**
     * scrolling animation on checkout cart page if coming from modal buy box on detail page
     */
    $(function () {
        var $body = $('body'),
            promotionSelector = '#promotion-free-goods',
            $promotions = $(promotionSelector),
            hash = top.location.hash;

        if ($body.is('.is--ctl-checkout.is--act-cart') && $promotions.length > 0 && hash === promotionSelector) {
            $body.animate({ 'scrollTop': $promotions.offset().top }, 1500);
        }
    });

    /**
     * slider for free goods promotion in off canvas basket
     */
    $.plugin('promotionFreeGoodsSlider', {

        productContainer: [],

        currentIndex: 0,

        leftButtonClass: '.free_goods-product--toLeft',
        rightButtonClass: '.free_goods-product--toRight',

        $leftButton: undefined,
        $rightButton: undefined,

        init: function () {
            var me = this;
            me.productContainer = [];
            me.currentIndex = 0;
            me.$leftButton = undefined;
            me.$rightButton = undefined;

            me.getProductContainer();
            me.initLeftNavigationButton();
            me.initRightNavigationButton();
            me.check();
        },

        getProductContainer: function () {
            var me = this,
                productContainer = $('.container--product');
            $.each(productContainer, function (key, value) {
                me.productContainer.push($(value).hide());
            });

            me.productContainer[me.currentIndex].show();
        },

        initLeftNavigationButton: function () {
            var me = this;

            me.$leftButton = $(me.leftButtonClass);
            me.$leftButton.on('click', function () {
                me.showPreviousArticle(me);
            });
        },

        initRightNavigationButton: function () {
            var me = this;

            me.$rightButton = $(me.rightButtonClass);
            me.$rightButton.on('click', function () {
                me.showNextArticle(me);
            });
        },

        check: function () {
            var me = this;
            if (me.productContainer.length === 1) {
                me.$leftButton.hide();
                me.$rightButton.hide();
            }

            if (me.productContainer.length === 0) {
                me.$el.hide();
            }
        },

        showNextArticle: function (me) {
            me.productContainer[me.currentIndex].hide();
            me.currentIndex = me.getNextIndex(me);
            me.productContainer[me.currentIndex].show();
        },

        showPreviousArticle: function (me) {
            me.productContainer[me.currentIndex].hide();
            me.currentIndex = me.getPreviousIndex(me);
            me.productContainer[me.currentIndex].show();
        },

        getNextIndex: function (me) {
            var defaultIndex = 0,
                elementCount = me.productContainer.length - 1,
                tempIndex = me.currentIndex + 1;

            if (tempIndex > elementCount) {
                return defaultIndex;
            }

            return tempIndex;
        },

        getPreviousIndex: function (me) {
            var defaultIndex = 0,
                elementCount = me.productContainer.length - 1,
                tempIndex = me.currentIndex - 1;

            if (tempIndex < defaultIndex) {
                return elementCount;
            }
            return tempIndex;
        },

        destroy: function () {
            var me = this;

            me.productContainer = [];
            me.currentIndex = 0;
            me.$leftButton = undefined;
            me.$rightButton = undefined;

            me._destroy();
        }
    });

    /**
     * handle free goods promotion if one is selected for insert in basket
     */
    $.plugin('swagPromotionHandleFreeGoods', {

        buttonName: '.free_goods-product--button',

        init: function () {
            var me = this;
            me.resetEvents();
            me.addButtonEvents();
        },

        addButtonEvents: function () {
            var me = this;
            $(document).on('click', me.buttonName, function () {
                me.callAjax(me, this);
            });
        },

        resetEvents: function () {
            var me = this;
            $(document).off('click', me.buttonName, function () {
                me.callAjax(me, this);
            });
        },

        callAjax: function (me, origin) {
            var url = $('.free_goods-product').attr('data-url'),
                selector = $(origin).attr('data-name'),
                orderNumber = $('[name=' + selector + ']').val(),
                promotionId = $(origin).attr('data-promotionId'),
                data = { orderNumber: orderNumber, promotionId: promotionId },
                ajaxCart = $('.container--ajax-cart').data('plugin_swCollapseCart');

            if (orderNumber === '') {
                return;
            }

            ajaxCart.showLoadingIndicator();

            $.ajax({
                type: 'post',
                url: url,
                data: data
            }).done(function (data) {
                data = $.parseJSON(data);
                if (data.success === true) {
                    ajaxCart.loadCart();
                }
            });
        }
    });
})(jQuery);
