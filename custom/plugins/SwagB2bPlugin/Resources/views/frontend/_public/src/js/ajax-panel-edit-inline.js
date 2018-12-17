$.plugin('b2bAjaxPanelEditInline', {

    defaults: {
        rowSelector: '[data-class="row"]'
    },

    init: function () {
        var me = this;

        this.applyDataAttributes();

        me._on(
            document,
            'click',
            '*[data-mode="edit"]:not(.is--b2b-acl-forbidden)',
            function(event) {

                $('*[data-b2b-form-input-holder="true"]').b2bFormInputHolder();

                var $button = $(event.currentTarget),
                    $row = $button.closest(me.defaults.rowSelector),
                    $quantitySelect = $row.find('[data-display="edit-mode"]'),
                    $quantityView = $row.find('[data-display="view-mode"]'),
                    $commentInput = $row.next('tr'),
                    $actualClickElement = $(event.target);

                if ($actualClickElement.hasClass('no--edit')) {
                    return;
                }

                $button.attr('disabled', 'disabled');

                $quantitySelect.removeClass('is--hidden');
                $quantityView.addClass('is--hidden');
                $commentInput.removeClass('is--hidden');

                var $spacer = $commentInput.next('[data-display="spacer-mode"]');
                $spacer.remove();
            }
        );
    },

    destroy: function() {
        var me = this;
        me._destroy();
    }
});

$(document).b2bAjaxPanelEditInline();