/**
 * Sync input field with another field which will be addressed by element class
 *
 * USAGE:
 *
 * <input type="text" name="original" data-b2b-form-input-holder="true" data-targetElement="targetClass">
 *
 * <input type="hidden" name="target" class="targetClass">
 *
 */
$.plugin('b2bFormInputHolder', {

    init: function() {
        this.registerGlobalListeners();
    },

    registerGlobalListeners: function() {
        var me = this;

        me._on(me.$el, 'change', $.proxy(me.onChange, me));

        me.$el.change();
    },

    onChange: function(event) {
        var sourceValue = event.target.value;
        var $targetElement = $('.' + $(event.target).attr('data-targetElement'));

        if(!$targetElement.length) {
            return;
        }

        $targetElement.val(sourceValue);
    },

    destroy: function() {
        var me = this;
        me._destroy();
    }
});

$('*[data-b2b-form-input-holder="true"]').b2bFormInputHolder();
