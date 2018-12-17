$.plugin('b2bTreeSelect', {
    defaults: {
        nodeMargin: 15,

        treeContainerSelector: '.is--b2b-tree-select-container',
        hoverTimeToWait: 1500,

        nodeOpenClass: 'is--opened',
        nodeClosedClass: 'is--closed',
        nodeLoadingClass: 'is--loading',
        nodeClassHasChildren: 'has--children',

        deleteItemSelector: '.component-action-delete',

        ajaxPanelSelector: '.b2b--ajax-panel',
        ajaxPanelRefreshEvent: 'b2b--ajax-panel_refresh',
        ajaxPanelLoadedEvent: 'b2b--ajax-panel_loaded',

        errorBoxSelector: '.modal--errors',
        slideUpVelocity: 'fast',
        tabChangeEvent: 'tab_changed'
    },

    init: function() {
        this.applyDataAttributes();

        var me = this;

        me.$tree = me.$el.find(me.defaults.treeContainerSelector);

        if(!me.$tree.length) {
            return;
        }

        me.$tree.find(me.defaults.ajaxPanelSelector).on(me.defaults.ajaxPanelRefreshEvent, $.proxy(me.updateSelectedNodesOnRefresh, me));
        me._on(me.$tree, me.defaults.ajaxPanelLoadedEvent, $.proxy(me.doLayout, me));
        me._on(me.$tree, 'click', 'a', $.proxy(me.toggleNodeOpenState, me));
        me._on(me.$tree, 'click', '.tree-label', $.proxy(me.toggleSelection, me));
        me._on(me.$tree, 'click', me.defaults.deleteItemSelector, $.proxy(me.onDeleteClick, me));
        me._on(document, me.defaults.tabChangeEvent, $.proxy(me.onRemoveError, me));

        me.initDragAndDrop();
        me.doLayout();
    },

    initDragAndDrop: function() {
        var me = this;

        me.moveUrl = me.$tree.data('moveUrl');

        me._on(me.$tree, 'dragstart', 'li', $.proxy(me.startDrag, me));
        me._on(me.$tree, 'dragenter', '.drop-area span, .tree-handle', $.proxy(me.dragEnter, me));
        me._on(me.$tree, 'dragenter', '.b2b-tree-node-inner', $.proxy(me.toggleDragover, me));
        me._on(me.$tree, 'dragend, dragexit', $.proxy(me.doLayout, me));
        me._on(me.$tree, 'dragenter', 'li', me.stopEvent);
        me._on(me.$tree, 'dragover', 'li', me.stopEvent);
        me._on(me.$tree, 'drop', 'li', $.proxy(me.dropListItem, me));
        me._on(me.$tree, 'mouseleave', $.proxy(me.dragOutside, me));
    },

    stopEvent: function(event) {
        event.stopPropagation();
        event.preventDefault();

        var $el = $(event.currentTarget),
            $actions = $el.find('.actions');

        $actions.show();
    },

    dragOutside: function(event){
        var me = this,
            $el = $(event.target);

        if(!$el.parents('li').hasClass('dragged')) {
            return;
        }

        me.doLayout();

        $el.removeClass('drop-before').addClass('drop-before');
        $el.removeClass('drop-as-child').addClass('drop-as-child');
        $el.removeClass('drop-after').addClass('drop-after');
    },

    startDrag: function(event) {
        event.stopPropagation();

        var $el = $(event.currentTarget),
            $actions = $el.find('.actions');

        $el.addClass('dragged');

        if($actions.length) {
            $actions.hide();
        }

        event.originalEvent.dataTransfer.setData('text/plain', $el.attr('id'));
        event.originalEvent.dataTransfer.dropEffect = "move";
        event.originalEvent.dataTransfer.effectAllowed = "move";
    },

    dragEnter: function(event) {
        var me = this,
            $el = $(event.currentTarget),
            now = (new Date()).getTime(),
            timing = me.defaults.hoverTimeToWait,
            $li = $el.closest('li');

        me.$tree
            .find('.hover')
            .removeClass('hover');

        if ($el.hasClass('drop-as-child')) {
            $li.find('.b2b-tree-node-inner:first').addClass('hover');
        } else {
            $el.addClass('hover');
        }

        if(!$el.is('.tree-handle')) {
            return;
        }

        var lastTimeoutTime = $el.data('lastTimeoutTime');

        if(!lastTimeoutTime) {
            lastTimeoutTime = 0;
        }

        if((lastTimeoutTime + timing) > now) {
            return;
        }

        $el.data('lastTimeoutTime', now);

        setTimeout(function() {
            var $link = $el.closest('a'),
                $treeHandle = $el.closest('li');

            if(!$link.length) {
                return;
            }

            if($treeHandle.hasClass(me.defaults.nodeOpenClass)) {
                return;
            }

            if(!$el.is('.hover')) {
                return;
            }

            $link.click();
        }, timing);
    },

    toggleDragover: function(event) {
        var me = this,
            $el = $(event.currentTarget);

        me.$tree
            .find('.dragover')
            .removeClass('dragover');

        if($el.closest('.dragged').length) {
            return;
        }

        $el.addClass('dragover');
    },

    dropListItem: function(event) {
        var me = this,
            $el = $(event.currentTarget);

        event.stopPropagation();
        event.preventDefault();

        var $originalItem = $('#' + event.originalEvent.dataTransfer.getData('text/plain'));

        if($originalItem.find($el).length) {
            me.doLayout();
            return;
        }

        if($originalItem.is($el)) {
            me.doLayout();
            return;
        }

        if (!$originalItem.data('id')) {
            me.$tree
                .find('.dragover')
                .removeClass('dragover');

            me.$tree
                .find('.dragged')
                .removeClass('dragged');

            me.$tree
                .find('.hover')
                .removeClass('hover');
            return;
        }

        var $parent = $originalItem.parents('li').first();
        if($parent.hasClass('has--children')){
            if(!$originalItem.siblings('li:visible').length){
                $parent.removeClass('has--children');
            }
        }

        var $spacerElement = $(document.elementFromPoint(event.clientX, event.clientY));

        if(!$spacerElement.closest('.drop-area').length) {
            me.doLayout();
            return;
        }

        var type;

        if($spacerElement.hasClass('drop-before')) {
            type = 'prev-sibling';
            $originalItem.remove().insertBefore($el);
        } else if($spacerElement.hasClass('drop-as-child')) {
            type = 'last-child';
            $el.addClass('has--children');
            $originalItem.remove().appendTo($el.find('ul:eq(0)'));
        } else if($spacerElement.hasClass('drop-after')) {
            type = 'next-sibling';
            $originalItem.remove().insertAfter($el);
        }

        if(type === 'last-child' && $el.hasClass('has--children') && !$el.hasClass('is--opened')) {
            $originalItem.hide();
        }

        $.post(me.moveUrl, {
            type: type,
            roleId: $originalItem.data('id'),
            relatedRoleId: $($el).data('id'),
            success: function() {
                if(type === 'last-child' && $el.hasClass('has--children') && !$el.hasClass('is--opened')) {
                    $el.find('a:eq(0)').click();
                }
            }
        });

        me.doLayout();
    },

    updateSelectedNodesOnRefresh: function(event) {
        var me = this,
            nodeIds = [],
            $el = $(event.currentTarget),
            requestData = $el.data('lastPanelRequest');

        me.$tree.find('.is--opened').each(function() {
            nodeIds.push($(this).data('id'));
        });

        requestData.data = {openNodes: nodeIds};
        $el.data('lastPanelRequest', requestData);
    },

    toggleSelection: function(event) {
        event.stopPropagation();

        var me = this,
            $el = $(event.currentTarget),
            $selectedId = $el.closest('li').data('id'),
            $storageItem = $el.closest(me.defaults.ajaxPanelSelector);

        if (!$selectedId) {
            return;
        }

        $('input.b2b--tree-selection-aware[data-id="' + $storageItem.data('id') + '"]')
            .val($selectedId)
            .change();

        $storageItem
            .data('tree-selected-id', $selectedId);

        me.$tree
            .find('.selected')
            .removeClass('selected');

        $el.addClass('selected');
    },

    toggleNodeOpenState: function(event) {
        event.preventDefault();
        event.stopPropagation();

        var me = this,
            $anchor = $(event.currentTarget),
            $listItem = $anchor.closest('li'),
            $list = $listItem.find('ul:eq(0)');

        if(!$listItem.hasClass(me.defaults.nodeClassHasChildren) || $listItem.hasClass(me.defaults.nodeLoadingClass)) {
            return;
        }

        if($listItem.hasClass(me.defaults.nodeOpenClass)) {
            $listItem.addClass(me.defaults.nodeClosedClass)
                .removeClass(me.defaults.nodeOpenClass);

            $list.empty();
            return;
        }

        $listItem.removeClass(me.defaults.nodeClosedClass)
            .addClass(me.defaults.nodeLoadingClass);

        $.ajax({
            url: $anchor.attr('href'),
            success: function(response) {
                $listItem
                    .removeClass(me.defaults.nodeLoadingClass)
                    .addClass(me.defaults.nodeOpenClass);

                $list.append(response);
                me.doLayout();
            }
        });
    },

    onDeleteClick: function(event) {
        var me = this,
            $target = $(event.currentTarget),
            $form = $target.closest('form');

        event.stopPropagation();
        event.preventDefault();

        if ($target.data('confirm')) {

            // save form to temp state
            me.defaults.activeForm = $form;

            $.ajax({
                'url': $target.data('confirm-url'),
                'type': 'post',
                'data': $form.serialize(),
                success: function(response) {
                    $.b2bConfirmModal.open(response, {
                        'confirm': function() {
                            me.onConfirmRemove();
                        },
                        'cancel': function() {
                            $.b2bConfirmModal.close();
                        }
                    });
                }
            });
        } else {
            me.removeItemAjax($form);
        }
    },

    removeItemAjax: function ($form) {
        var me = this,
            $roleElement = $form.find('.roleId'),
            $createParentInput = $('.role-block input.b2b--tree-selection-aware'),
            $defaultRoleElement = me.$tree.find('#tree-node-id-0');

        $form.find('input.hidden-open-node-field').remove();
        me.$tree.find('.is--opened').each(function() {
            $form.append('<input type=hidden class="hidden-open-node-field" name="openNodes[]" value="' + $(this).data('id') + '"/>');
        });
        me.$el.trigger(
            'b2b--do-ajax-call', [
                $form.attr('action'),
                me.$tree.find(me.defaults.ajaxPanelSelector),
                $form
        ]);

        if (!$roleElement.length) {
            return;
        }

        if ($createParentInput.val() === $roleElement.val()) {
            $createParentInput.val($defaultRoleElement.data('id'));
        }

        me.resetTabContent($roleElement);
    },

    resetTabContent: function($roleElement) {
        var $role = {
            'id': $roleElement.val()
        };

        $(document).trigger('b2b--disable-company-tabs', $role);
    },

    onConfirmRemove: function () {
        var me = this;

        if (me.defaults.activeForm) {
            me.removeItemAjax(me.defaults.activeForm)
        }

        me.defaults.activeForm = null;

        $.b2bConfirmModal.close();
    },

    onRemoveError: function() {
        var me = this,
            $errorBox = me.$tree.find(me.defaults.errorBoxSelector);

        if (!$errorBox) {
            return;
        }

        $errorBox.slideUp(me.defaults.slideUpVelocity);
    },

    doLayout: function() {
        var me = this;

        this.$tree.find('.b2b-tree-node-inner').each(function() {
            var $this = $(this),
                multiplier = $this.parents('ul.is--b2b-tree-select').length - 1;

            $this.css('marginLeft', multiplier * me.defaults.nodeMargin);
        });

        var itemCount = 0;
        this.$tree.find('li').each(function() {
            $(this).attr('id', 'tree-node-id-' + (itemCount++));
        });

        this.$tree
            .find('.dragover')
            .removeClass('dragover');

        this.$tree
            .find('.dragged')
            .removeClass('dragged');

        this.$tree
            .find('.hover')
            .removeClass('hover');
    },

    destroy: function() {
        var me = this;
        me._destroy();
    }
});
