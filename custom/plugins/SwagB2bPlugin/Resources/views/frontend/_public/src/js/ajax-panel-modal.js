/**
 * Handles modal box views:
 *
 * If an ajax panel has the additional class `b2b-modal-panel` the content will not get wrapped inside a modal box.
 *
 * <div class="b2b--ajax-panel b2b-modal-panel" data-id="role-detail">
 *
 * WARNING: this is done through html cloning, do not depend on its event handlers and properties
 */
$.plugin('b2bAjaxPanelModal', {

    defaults: {
        panelSelector: '.b2b--ajax-panel',

        panelCloseClassSelector: '.b2b--close-modal-box',

        modalModifierClass: 'b2b-modal-panel',

        normalWidth: 1000,

        normalHeight: 711
    },

    init: function () {
        var me = this;

        this.applyDataAttributes();

        me._on(
            document,
            'b2b--ajax-panel_loading',
            function (event, eventData) {
                var $panel = $(eventData.panel),
                    ajaxData = eventData.ajaxData;

                if(!$panel.hasClass(me.defaults.modalModifierClass)) {
                    return;
                }

                var height = me.defaults.normalHeight;
                var width = me.defaults.normalWidth;

                event.preventDefault();

                $.overlay.close();

                var $newPanel = $.modal.open($panel[0].outerHTML, {
                    width: width,
                    height: height,
                    sizing: 'fixed'
                })._$content.find(me.defaults.panelSelector);
                
                $('.modal--close').addClass('b2b--modal-close');
                
                $newPanel
                    .data('payload', ajaxData.data)
                    .data('url', ajaxData.url)
                    .removeClass(me.defaults.modalModifierClass)
                    .data('b2bModalAjaxPanel', true);

                $newPanel.trigger('b2b--ajax-panel_register');
            });

        me._on(
            document,
            'b2b--ajax-panel_loaded',
            function (event, eventData) {
                var $panel = $(eventData.panel),
                    hasCloseTrigger = Boolean($panel.find(me.defaults.panelCloseClassSelector).length);

                if(!hasCloseTrigger) {
                    return;
                }

                $.modal.close();
            }
        );
    },

    destroy: function() {
        var me = this;
        me._destroy();

        $.overlay.close();
    }
});

$(document).b2bAjaxPanelModal();