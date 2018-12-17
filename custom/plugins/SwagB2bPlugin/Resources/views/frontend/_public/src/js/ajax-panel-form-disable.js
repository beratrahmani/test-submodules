/**
 * Disables all form elements on 'b2b--ajax-panel_loading' loading. Just add 'has--b2b-form' to the container
 */
$.plugin('b2bAjaxPanelFormDisable', {
    defaults: {
        triggerSelector: '.has--b2b-form'
    },

    init: function () {
        var me = this;

        this.applyDataAttributes();

        me._on(
            document,
            'b2b--ajax-panel_loading',
            function (event, eventData) {
                var $panel = $(eventData.panel);

                if(!$panel.is(me.defaults.triggerSelector)) {
                    return;
                }

                $panel.find('input, select, button, form').prop('disabled', true);
            });
    },

    destroy: function() {
        var me = this;
        me._destroy();
    }
});

$(document).b2bAjaxPanelFormDisable();