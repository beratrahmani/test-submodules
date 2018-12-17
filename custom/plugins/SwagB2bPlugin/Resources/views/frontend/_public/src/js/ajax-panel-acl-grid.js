/**
 * Disables row actions in grids
 */
$.plugin('b2bAjaxPanelAclGrid', {
    defaults: {},

    init: function () {
        var me = this;

        this.applyDataAttributes();

        me._on(document, 'b2b--ajax-panel_loading', $.proxy(me.disableForm, me));
    },

    disableForm: function (event, eventData) {
        var $source = $(eventData.source);

        if(!$source.is('.ajax-panel-link.is--b2b-acl-forbidden')) {
            return;
        }

        event.preventDefault();
        event.stopPropagation();
        event.stopImmediatePropagation();
    },

    destroy: function() {
        var me = this;
        me._destroy();
    }
});

$(document).b2bAjaxPanelAclGrid();