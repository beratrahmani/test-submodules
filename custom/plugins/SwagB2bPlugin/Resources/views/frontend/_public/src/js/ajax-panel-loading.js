/**
 * This plugin adds loading indicators to panels which are flagged by the 'is--loading' class
 */
$.plugin('b2bAjaxPanelLoading', {
    defaults: {

        loadingIndicatorParentCls: 'content--loading',

        loadingIndicatorCls: 'icon--loading-indicator',

        disableCls: 'has--no-indicator'
    },

    init: function () {
        var me = this;

        this.applyDataAttributes();

        me._on(
            document,
            'b2b--ajax-panel_loading',
            function (event, eventData) {
                var $panel = $(eventData.panel);

                if ($panel.hasClass(me.defaults.disableCls)) {
                    return;
                }

                $panel.html('<div class="' + me.defaults.loadingIndicatorParentCls +'"><i class="' + me.defaults.loadingIndicatorCls + '"></i></div>');
            });
    },

    destroy: function() {
        var me = this;
        me._destroy();
    }
});

$(document).b2bAjaxPanelLoading();