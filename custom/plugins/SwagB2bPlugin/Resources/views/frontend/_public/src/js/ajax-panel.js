/**
 * Loads occurred per ajax, replaces it in the DOM and intercepts all it's submit and link click events.
 *
 * USAGE:
 *
 * Basic container needs to look like this:
 *
 * <div class="b2b--ajax-panel" data-url="http://foo/bar"></div>
 *
 * If DOM element should be a trigger, but can neither be <a>, nor <form>, you can add a special class
 *
 * <button class="ajax-panel-link" data-href="http://foo/bar">Click</a>
 *
 * If a link should use a different target:
 *
 * Container:  <div ... data-id="foreign"></div>
 * Link: <a [...] data-target="foreign">Open in another component</a>
 *
 * If you want a link to be ignored, add the class '.ignore--b2b-ajax-panel'.
 *
 * EVENTS:
 *
 * Triggered:
 *    'b2b--ajax-panel_loading': triggered on the panel before loading new data
 *    'b2b--ajax-panel_loaded': triggered on the panel after loading and replacing the data
 *
 * Handled:
 *      'b2b--ajax-panel_refresh': triggered on the panel reissues the last request on the panel
 *      'b2b--do-ajax-call': trigger a ajax request with included refresh
 *
 * global: CSRF
 */
$.plugin('b2bAjaxPanel', {
    defaults: {
        panelSelector: '.b2b--ajax-panel',

        panelLinkDataKey: 'href',
        panelUrlDataKey: 'url',
        panelPayloadDataKey: 'payload',
        panelHistoryDataKey : 'lastPanelRequest',

        panelLoadingClass: 'content--loading',
        panelIconLoadingIndicatorClass: 'icon--loading-indicator',

        ignoreSelector: '.ignore--b2b-ajax-panel',

        panelLinkSelector: '.ajax-panel-link',

        panelBeforeLoadEvent: 'b2b--ajax-panel_loading',
        panelAfterLoadEvent: 'b2b--ajax-panel_loaded',

        panelRefreshEvent: 'b2b--ajax-panel_refresh',
        panelRegisterEvent: 'b2b--ajax-panel_register',

        performAjaxCallEvent: 'b2b--do-ajax-call'
    },

    init: function() {
        var me = this;

        this.applyDataAttributes();
        this.registerGlobalListeners();
        this.registerShopwarePlugins();

        $(me.defaults.panelSelector).each(function (index, panel) {
            me.register(panel);
        });
    },

    register: function (panel) {
        var $panel = $(panel);
        var url = $panel.data(this.defaults.panelUrlDataKey);

        if(url && url.length) {
            this.load(url, panel, $panel);
        }
    },

    error: function(target) {
        $(target).html('<div style="margin:20px"><h1 style="margin-bottom:5px">An error occurred</h1><p>Please enable error logging for more information about this error.</p></div>');
    },

    load: function(url, target, source) {
        var me = this,
            $target = $(target),
            serializedData = {},
            method = 'GET';

        if (source && source.length) {
            if (source.is('form')) {
                if (source.attr('method')) {
                    method = source.attr('method').toUpperCase() === 'POST' ? 'POST' : 'GET';
                }
                serializedData = source.serializeArray();
            } else if (source.data(me.defaults.panelPayloadDataKey)) {
                serializedData = source.data(me.defaults.panelPayloadDataKey);
            }
        }

        var ajaxData = {
            type: method,
            url: url,
            data: serializedData
        };

        me.doAjaxCall(ajaxData, $target, source);
    },

    doAjaxCall: function(ajaxData, $target, $source) {
        var me = this;

        $target.append($('<div>', {
            'class': me.defaults.panelLoadingClass,
            'html': $('<i>', {
                'class': me.defaults.panelIconLoadingIndicatorClass
            })
        }));

        if (me.notify($target, me.defaults.panelBeforeLoadEvent, {'panel': $target, 'source': $source, 'ajaxData': ajaxData})) {
            return;
        }

        if('GET' === ajaxData.type) {
            $target.data(me.defaults.panelHistoryDataKey, ajaxData);
        }

        $.ajax($.extend({}, ajaxData, {
            success: function(response, status, xhr) {

                /** global: window */
                if(xhr.getResponseHeader('B2b-no-login')) {
                    window.location.reload();
                    return;
                }

                // close modal after success
                if($source.data('close-success') && !$(response).find('.modal--errors').length) {
                    $.modal.close();
                } else {
                    $target.html(response);
                }

                me.refreshShopwarePlugins($target);

                $target.trigger(me.defaults.panelAfterLoadEvent, {
                    'panel': $target,
                    'source': $source,
                    'ajaxData': ajaxData
                });
            },
            error: function () {
                me.error($target);
            },
            always: function () {
                $target.removeClass(me.defaults.panelLoadingClass);
            }
        }));
    },

    breakEventExecution: function(event) {
        event.preventDefault();
    },

    registerGlobalListeners: function() {
        var me = this;

        me._on(document, 'click', me.defaults.panelSelector + ' a, ' + me.defaults.panelLinkSelector, function (event) {
            if(event.isDefaultPrevented()) {
                return;
            }

            var $eventTarget = $(event.target);
            if(!$eventTarget.is(this) && $eventTarget.closest('form').length) {
                return;
            }

            var $anchor = $(this);
            if($anchor.is(me.defaults.ignoreSelector)) {
                return;
            }

            me.breakEventExecution(event);

            var url,
                $panel = $anchor.closest(me.defaults.panelSelector),
                targetPanel = $anchor.data('target'),
                $targetPanel = $(me.defaults.panelSelector + '[data-id="' + targetPanel + '"]');

            if($anchor[0].hasAttribute(me.defaults.panelLinkDataKey)) {
                url = $anchor.attr(me.defaults.panelLinkDataKey);
            } else {
                url = $anchor.data(me.defaults.panelLinkDataKey);
            }

            if ($targetPanel.length) {
                me.load(url, $targetPanel, $anchor);
            } else {
                me.load(url, $panel, $anchor);
            }
        });

        me._on(document, 'submit', me.defaults.panelSelector + ' form, form' + me.defaults.panelLinkSelector, function (event) {
            if(event.isDefaultPrevented()) {
                return;
            }

            var $form = $(this);

            if($form.is(me.defaults.ignoreSelector)) {
                return;
            }

            me.breakEventExecution(event);

            var $panel = $form.closest(me.defaults.panelSelector),
                url = $form.attr('action'),
                targetPanel = $form.data('target'),
                $targetPanel = $(me.defaults.panelSelector + '[data-id="' + targetPanel + '"]');

            if ($targetPanel.length) {
                me.load(url, $targetPanel, $form);
            } else {
                me.load(url, $panel, $form);
            }
        });

        me._on(document, me.defaults.panelAfterLoadEvent, me.defaults.panelSelector, function(event) {
            if(event.isDefaultPrevented()) {
                return;
            }

            var $panel = $(this);

            me.breakEventExecution(event);

            $panel.find(me.defaults.panelSelector).each(function() {
                var panel = this;

                me.register(panel);
            });
        });

        me._on(document, me.defaults.performAjaxCallEvent, function (event, url, $target, source) {
            me.load(url, $target, source);
        });

        me._on(document, me.defaults.panelRefreshEvent, me.defaults.panelSelector, function(event) {
            if(event.isDefaultPrevented()) {
                return;
            }

            var $panel = $(this),
                ajaxData = $panel.data(me.defaults.panelHistoryDataKey);

            if(!ajaxData) {
                return;
            }

            me.breakEventExecution(event);
            me.doAjaxCall(ajaxData, $panel, $panel);
        });

        me._on(document, me.defaults.panelRegisterEvent, me.defaults.panelSelector, function(event) {
            if(event.isDefaultPrevented()) {
                return;
            }

            var $panel = $(this);

            me.register($panel);
        });

        me._on(document, 'click', '*[data-form-id]', function(event) {
            var me = this,
                formId = $(me).data('form-id'),
                $form = $('#' + formId);

            if(!$form.length) {
                return;
            }

            $form.submit();
        });
    },

    notify: function($panel, eventName, data) {
        var beforeEvent = $.Event(eventName);
        $panel.trigger(beforeEvent, data);

        return beforeEvent.isDefaultPrevented();
    },

    registerShopwarePlugins: function() {
        StateManager.addPlugin('select:not([data-no-fancy-select="true"])', 'swSelectboxReplacement');

        StateManager.addPlugin('.datepicker', 'swDatePicker');

        StateManager.addPlugin('.csv--configuration .configuration--header', 'swCollapsePanel', {
            'contentSiblingSelector': '.csv--configuration .configuration--content'
        });
    },

    refreshShopwarePlugins: function($target) {
        $.modal.onWindowResize();

        StateManager.updatePlugin('select:not([data-no-fancy-select="true"])', 'swSelectboxReplacement');

        if($target.find('.datepicker').length) {
            StateManager.updatePlugin('.datepicker', 'swDatePicker');
        }

        StateManager.updatePlugin('.csv--configuration .configuration--header', 'swCollapsePanel');

        CSRF.updateForms();
    },

    destroy: function() {
        var me = this;
        me._destroy();
    }
});

$(document).b2bAjaxPanel();
