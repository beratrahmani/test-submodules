/**
 * Disables all form elements in forbidden forms, and removes the submit button
 */
$.plugin('b2bAjaxPanelAclForm', {
    defaults: {
        triggerSelector: '.has--b2b-form'
    },

    init: function () {
        var me = this;

        this.applyDataAttributes();

        me._on(document, 'b2b--ajax-panel_loaded', $.proxy(me.disableForm, me));
    },

    disableForm: function (event, eventData) {
        var $panel = $(eventData.panel);

        $panel
            .find('form.is--b2b-acl-forbidden input, form.is--b2b-acl-forbidden select, form.is--b2b-acl-forbidden button')
            .attr('disabled', 'disabled');
        $panel
            .find('form.is--b2b-acl-forbidden button[type="submit"]')
            .remove();
    },

    destroy: function() {
        var me = this;
        me._destroy();
    }
});

$(document).b2bAjaxPanelAclForm();