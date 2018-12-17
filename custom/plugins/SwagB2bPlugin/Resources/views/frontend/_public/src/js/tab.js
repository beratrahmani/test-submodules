/**
 * This plugin adds missing functionality for tab views in combination with ajax-panel.
 *
 * Just use the standard Shopware tab view markup and add 'b2b--tab-view' class to the outermost container.
 *
 * Additions:
 *  * handles forms as links
 *  * on change of these forms resubmitts the form
 *  * if auto enabled form is enabled selects it
 */
$.plugin('b2bTab', {

    defaults: {
        tabMenuSelector: '.b2b--tab-menu',

        initialState: null,

        tabChangeEvent: 'tab_changed'
    },

    init: function () {
        var me = this;

        me.applyDataAttributes();

        me._on(
            document,
            'click',
            me.defaults.tabMenuSelector + ' .tab--navigation a,'
             + me.defaults.tabMenuSelector + ' .tab--navigation button',
            $.proxy(me.handleTriggerEvent, me)
        );

        me._on(
            document,
            'change',
            me.defaults.tabMenuSelector + ' input',
            $.proxy(me.handleChangeRefresh, me)
        );

        me._on(
            document,
            'b2b_auto_enable_form',
            me.defaults.tabMenuSelector + ' form',
            $.proxy(me.checkAutoEnable, me)
        );

        $(me.defaults.tabMenuSelector + ' .tab--navigation .tab--link:visible:first')
            .click();

        me._on(
            document,
            'b2b--disable-company-tabs',
            $.proxy(me.disableTabs, me)
        );
    },

    checkAutoEnable: function(event) {
        var me = this,
            $form = $(event.currentTarget),
            $menu = $form.closest('.b2b--tab-menu'),
            $submit = $form.find('button:submit');

        if($menu.find('.is--active').length) {
            return;
        }
        
        me.defaults.initialState = true;

        $submit.click();
    },

    handleChangeRefresh: function(event) {
        var me = this,
            $button = $(event.currentTarget)
            .closest('form')
            .find('button:submit');
        
        if(!$button.hasClass('is--active') || me.defaults.initialState) {
            me.defaults.initialState = null;
            return;
        }

        $button.click();
    },

    disableTabs: function(event, deletedRoleElement) {
        var me = this,
            tabMenu = $(me.defaults.tabMenuSelector),
            ajaxPanel = tabMenu.find('.tab--container.b2b--ajax-panel'),
            roleId = tabMenu.find('.b2b--tree-selection-aware').first().attr('value');

        if(roleId !== deletedRoleElement.id) {
            return;
        }

        tabMenu.find('.tab--link').each(function () {
            $(this).attr('disabled', 'disabled').removeClass('is--active');
        });

        ajaxPanel.trigger(
            'b2b--do-ajax-call',
            [
                tabMenu.data('default-tab-url'),
                ajaxPanel,
                ajaxPanel
            ]
        );
    },

    handleTriggerEvent: function(event) {
        var me = this,
            $link = $(event.currentTarget);

        $link
            .closest('.tab--navigation')
            .find('.is--active')
            .removeClass('is--active');

        $link.addClass('is--active');

        $(document).trigger(me.defaults.tabChangeEvent);
    },

    destroy: function() {
        var me = this;
        me._destroy();
    }
});

$(document).b2bTab();