/**
 * Add `is--auto-submit` class to a form element to submit the for on change
 *
 * <input type="text" value="" class="is--auto-submit"/>
 *
 * * Add `is--auto-submit` class to a NOT form element and it will submit a linked form on click
 *
 * <form class="form-class" method="post" action="http/foo.bar">...</form>
 * <span type="text" value="" class="is--auto-submit" data-linked-form="form-class">Click to submit</span>
 */
$.plugin('b2bAutoSubmit', {

    defaults: {
        autoSubmitSelector: '.is--auto-submit'
    },

    init: function () {
        var me = this;

        this.applyDataAttributes();

        me._on(
            document,
            'change',
            me.defaults.autoSubmitSelector,
            me.handleTriggerEvent
        );

        me._on(
            document,
            'click',
            me.defaults.autoSubmitSelector + ':not(input, option, select, button, div.select-field)' ,
            me.handleTriggerEvent
        );
    },

    handleTriggerEvent: function(event) {
        var $trigger = $(this),
            linkedFormClass = $trigger.data('linkedForm'),
            $form;

        if(event.isDefaultPrevented()) {
            return;
        }

        if(linkedFormClass) {
            $form = $('form.' + linkedFormClass);
        } else {
            $form = $(this).closest('form');
        }

        event.preventDefault();

        $form.submit();
    },

    destroy: function() {
        var me = this;
        me._destroy();
    }
});

$(document).b2bAutoSubmit();