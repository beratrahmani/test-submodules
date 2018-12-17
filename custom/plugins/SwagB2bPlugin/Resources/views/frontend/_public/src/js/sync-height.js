/**
 * This plugin syncs the height of two or more containers and puts a min-height on them
 *
 * Enable by adding the class `b2b--sync-height` and set a group through data-sync-group="_NAME_"
 */
$.plugin('b2bSyncHeight', {

    defaults: {
        elementSelector: '.b2b--sync-height'
    },

    init: function () {
        this.applyDataAttributes();
        this.syncHeight();
        this.addListeners();


    },

    addListeners: function() {
        var me = this;

        me._on(document, 'b2b--ajax-panel_loaded', me.defaults.elementSelector, $.proxy(me.syncHeight, me));
    },

    syncHeight: function() {
        var me = this;

        var handledGroups = {};

        $(me.defaults.elementSelector).each(function () {
            var $el = $(this),
                groupName = $el.data('syncGroup');

            if(handledGroups[groupName]) {
                return;
            }

            handledGroups[groupName] = true;

            var $group = $(me.defaults.elementSelector + '[data-sync-group="' + groupName + '"]');

            $group.each(function () {
                $(this).css('minHeight', 0);
            });

            var maxHeight = 0;

            $group.each(function () {
                var currentHeight = $(this).innerHeight();

                if(currentHeight > maxHeight) {
                    maxHeight = currentHeight;
                }
            });

            $group.each(function () {
                $(this).css('minHeight', maxHeight + 'px');
            });
        });
    },

    destroy: function() {
        var me = this;
        me._destroy();
    }
});

$(document).b2bSyncHeight();