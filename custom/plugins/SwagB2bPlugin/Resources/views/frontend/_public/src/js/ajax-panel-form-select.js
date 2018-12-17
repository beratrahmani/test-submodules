/**
 * Use a select box to trigger ajax panel navigation
 *
 * <select name="type" class="is--ajax-panel-navigation">
 *    <option value="" disabled selected="selected">Does not trigger anything</option>
 *    <option value="foo" selected="selected" class="ajax-panel-link" data-target="taget-panel-id" data-href="http://www.foo.bar.de">load foo on load</option>
 *    <option value="foo" class="ajax-panel-link" data-target="taget-panel-id" data-href="http://www.bar.bar.de">load bar on select</option>
 * </select>
 *
 * <div class="b2b--ajax-panel" data-url="" data-id="taget-panel-id"></div>
 */
$.plugin('b2bAjaxPanelFormSelect', {

    defaults: {
        navigationSelector: 'select.is--ajax-panel-navigation'
    },

    init: function () {
        var me = this;

        this.applyDataAttributes();

        me._on(
            document,
            'b2b--ajax-panel_loaded',
            $.proxy(me.registerEvents, me)
        );
    },

    registerEvents: function(event, eventData) {
        var me = this,
            $panel = $(eventData.panel);

        me._on(
            $panel.find(me.defaults.navigationSelector),
            'change',
            function() {
                var $select = $(this),
                    $option = $select.find('option.ajax-panel-link[value="' + $select.val() + '"]');

                if(!$option.length) {
                    return;
                }

                $('<span style="display: none;" class="ajax-panel-link" data-target="' + $option.data('target') + '" data-href="' + $option.data('href') + '"/>')
                    .appendTo($select.parent())
                    .click();
            }
        );

        $panel.find(me.defaults.navigationSelector).change();
    },

    destroy: function() {
        var me = this;
        me._destroy();
    }
});

$(document).b2bAjaxPanelFormSelect();