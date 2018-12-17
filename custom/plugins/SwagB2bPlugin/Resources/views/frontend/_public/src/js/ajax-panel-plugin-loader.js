/**
 * starts and destroys plugins on 'b2b--ajax-panel_loaded'. Just add a data attribute to the panel
 *
 * <div [...] data-plugins="b2bGridComponent,b2bAssignmentGrid">
 *
 * translates to:
 *
 * $('the_panel').b2bGridComponent()
 * $('the_panel').b2bAssignmentGrid()
 *
 */
$.plugin('b2bAjaxPanelPluginLoader', {
    init: function () {
        var me = this;

        this.applyDataAttributes();

        me._on(
            document,
            'b2b--ajax-panel_loading',
            function (event, eventData) {
                me.eachPluginName(eventData, function(pluginName, $panel) {
                    me.destroyPlugin($panel, pluginName);
                });
            });

        me._on(
            document,
            'b2b--ajax-panel_loaded',
            function (event, eventData) {
                me.eachPluginName(eventData, function(pluginName, $panel) {
                    $panel[pluginName]();
                });
            });
    },

    eachPluginName: function(eventData, callback) {
        var $panel = $(eventData.panel),
            pluginNames = $panel.data('plugins');

        if(!pluginNames) {
            return;
        }

        pluginNames = pluginNames.split(',');

        for(var i = 0; i < pluginNames.length; i++) {
            callback(pluginNames[i], $panel);
        }
    },

    destroyPlugin: function (selector, pluginName) {
        var $el = (typeof selector === 'string') ? $(selector) : selector,
            name = 'plugin_' + pluginName,
            len = $el.length,
            i = 0,
            $currentEl,
            plugin;

        if (!len) {
            return;
        }

        for (; i < len; i++) {
            $currentEl = $($el[i]);

            plugin = $currentEl.data(name);

            if (plugin) {
                plugin.destroy();
                $currentEl.removeData(name);
            }
        }
    },

    destroy: function() {
        var me = this;
        me._destroy();
    }
});

$(document).b2bAjaxPanelPluginLoader();