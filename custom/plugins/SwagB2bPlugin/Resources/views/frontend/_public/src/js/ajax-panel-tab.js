/**
 * The tab panel plugin allows to mark loaded menu elements as active
 *
 * <div class="modal--tabs">
 *   <ul>
 *      <li class="tab--header">Tab Header Description</li>
 *      <li>
 *          <a class="b2b--tab-link tab--active" data-target="role-tab-content" data-href="">Link 1</a>
 *      </li>
 *      <li>
 *          <a class="b2b--tab-link tab--active" data-target="role-tab-content" data-href="">Link 2</a>
 *      </li>
 *    </ul>
 *  </div>
 *
 */
$.plugin('b2bAjaxPanelTab', {

    defaults: {
        navigationSelector: '.modal--tabs',

        navigationLinkSelector: '.b2b--tab-link',

        navigationLinkActiveCls: 'tab--active'
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

    registerEvents: function() {
        var me = this;

        me._on(
            $(me.defaults.navigationSelector).find($(me.defaults.navigationLinkSelector)),
            'click',
            function() {
                var $link = $(this);

                me.resetActiveClass();

                $link.addClass(me.defaults.navigationLinkActiveCls);
            }
        );
    },

    resetActiveClass: function() {
        var me = this;

        $(me.defaults.navigationSelector)
            .find(me.defaults.navigationLinkSelector)
            .removeClass(me.defaults.navigationLinkActiveCls);
    },

    destroy: function() {
        var me = this;
        me._destroy();
    }
});

$(document).b2bAjaxPanelTab();