/**
 * Add `is--auto-enable-form` class to a form element to enable the submit button when every item has a value
 *
 * On change triggers a b2b_auto_enable_form event
 */
$.plugin('b2bAutoEnableForm', {

    defaults: {
        formSelector: '.is--auto-enable-form'
    },

    init: function () {
        var me = this;

        this.applyDataAttributes();

        me._on(
            document,
            'change',
            me.defaults.formSelector + ' input,' + me.defaults.formSelector + ' select',
            me.handleChangeEvent
        );
    },

    handleChangeEvent: function(event) {
        var $form = $(this).closest('form'),
            enable = true,
            $submit = $form.find('button:submit');

        if(event.isDefaultPrevented()) {
            return;
        }

        $form.find('input, select').each(function() {
            var $formItem = $(this);

            if(!$formItem.val()) {
                enable = false;
            }
        });

        $submit.attr('disabled', !enable);
        $form.trigger('b2b_auto_enable_form');
    },

    destroy: function() {
        var me = this;
        me._destroy();
    }
});

$(document).b2bAutoEnableForm();