$.plugin('b2bContactPasswordActivation', {
    defaults: {
        passwordActivationSelector: '.b2b--password-activation'
    },

    init: function () {
        var me = this;
        me._on(me.$el, 'change', me.defaults.passwordActivationSelector, $.proxy(me.onCheckboxChange, me));
    },

    onCheckboxChange: function (event) {
        var checkbox = event.currentTarget,
            inputPassword = $('.b2b--input-password'),
            formPassword = $('.b2b--password');

        if($(checkbox).is(':checked')) {
            formPassword.addClass('is--hidden');
        } else {
            inputPassword.val('');
            formPassword.removeClass('is--hidden');
        }
    },

    destroy: function () {
        var me = this;
        me._destroy();
    }
});
