/**
 * A valid ajax panel trigger element (a, form) can issue a reload on a different panel.
 *
 * <form [...] data-ajax-panel-trigger-reload="another-panel-id">
 *
 * Will after submit trigger a reload of the other panel. If the panel id equals `_WINDOW_` the whole page is reloaded.
 */
$.plugin('b2bAjaxPanelTriggerReload', {

    defaults: {
        panelSelector: '.b2b--ajax-panel',

        sourceTriggerDataKey: 'ajaxPanelTriggerReload',

        targetDataKey: 'reloadNext',

        windowReloadKey: '_WINDOW_'
    },

    init: function () {
        var me = this;

        this.applyDataAttributes();

        me._on(
            document,
            'b2b--ajax-panel_loading',
            function (event, eventData) {
                var $panel = $(eventData.panel),
                    $source = $(eventData.source),
                    reloadTrigger = $source.data(me.defaults.sourceTriggerDataKey);

                if(!reloadTrigger) {
                    return;
                }

                $panel.data(me.defaults.targetDataKey, reloadTrigger);
            });

        me._on(
            document,
            'b2b--ajax-panel_loaded',
            function (event, eventData) {
                var $panel = $(eventData.panel),
                    reloadTrigger = $panel.data(me.defaults.targetDataKey);

                if(!reloadTrigger) {
                    return;
                }

                if(-1 !== reloadTrigger.indexOf(me.defaults.windowReloadKey)) {
                    window.location.reload();
                    return;
                }

                $panel.removeData(me.defaults.targetDataKey);
                var panelIds = reloadTrigger.split(',');

                for(var i = 0; i < panelIds.length; i++) {
                    $(me.defaults.panelSelector + '[data-id=' + panelIds[i] + ']').trigger('b2b--ajax-panel_refresh');
                }
            });
    },

    destroy: function() {
        var me = this;
        me._destroy();
    }
});

$(document).b2bAjaxPanelTriggerReload();