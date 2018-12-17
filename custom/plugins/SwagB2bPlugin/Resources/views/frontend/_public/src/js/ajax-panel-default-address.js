$.plugin('b2bAjaxPanelDefaultAddress', {

    defaults: {
        exclusiveFieldClass: 'is--exclusive-selection'
    },

    init: function() {
        var me = this;

        me.applyDataAttributes();

        me._on(me.$el, 'change', $.proxy(me.exclusiveSelect, me));
    },

    exclusiveSelect: function(event) {
        var me = this,
            $target = $(event.target);

        if($target.hasClass(me.opts.exclusiveFieldClass)) {
            $('.' + me.opts.exclusiveFieldClass).prop('checked', false);
            $target.prop('checked', true);
        }
    },

    destroy: function() {
        this._destroy();
    }
});