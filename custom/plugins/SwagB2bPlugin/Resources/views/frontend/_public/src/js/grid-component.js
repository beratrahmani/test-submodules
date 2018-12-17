/**
 * Handles selection of rows in the grid view.
 *
 * * store the last selected data-row-id to enable reloads
 * * mark the currently selected row
 * * reset the stored id on click on 'component-action-create'
 * * preselect a row by adding `data-b2b-grid-preselect="ID"` somewhere in its parent chain
 */
$.plugin('b2bGridComponent', {
    defaults: {
        gridContainerSelector: '.b2b--grid-container',

        selectRowSelector: 'tbody td:not(".col-actions")',

        tableRowSelector: 'tr',

        resetSelectionClickSelector: '.component-action-create',

        preselectedIdDataKey: 'b2bGridPreselect',

        isActiveClass: 'is--active',

        ajaxPanelSelector: '.b2b--ajax-panel',

        deleteItemSelector: '.component-action-delete',

        panelRefreshEvent: 'b2b--ajax-panel_refresh',

        performAjaxCallEvent: 'b2b--do-ajax-call',

        searchContainerClass: '.search--area',

        searchTermChanged: null,

        activeForm: null
    },

    init: function() {
        var me = this,
            grid = me.$el.find(me.defaults.gridContainerSelector),
            preselectedId = grid.closest('*[data-b2b-grid-preselect]').data(me.defaults.preselectedIdDataKey),
            storedSelectedId = this.getStorageItem().data('gridSelectedId');

        this.applyDataAttributes();

        if(storedSelectedId) {
            me.selectRow(grid, storedSelectedId);
        } else if(preselectedId) {
            me.selectRow(grid, preselectedId);
        }

        me.defaults.searchTermChanged = false;

        me._on(
            grid.find(me.defaults.resetSelectionClickSelector),
            'click',
            function () {
                grid.find('tr').removeClass(me.defaults.isActiveClass);
                me.getStorageItem().removeData('gridSelectedId');
            }
        );

        me._on(
            grid.find(me.defaults.selectRowSelector),
            'click',
            $.proxy(me.onTableDataClick, me)
        );

        me._on(
            me.$el.find(me.defaults.deleteItemSelector),
            'click',
            $.proxy(me.onDeleteClick, me)
        );

        me._on(
            grid.find(me.defaults.searchContainerClass).find('input'),
            'change',
            $.proxy(me.onChangeSearchTerm, me)
        );

        me._on(
            grid.find(me.defaults.searchContainerClass).closest('form'),
            'submit',
            $.proxy(me.onSearchSubmit, me)
        );

        me._on(
            grid.find(me.defaults.searchContainerClass).closest('button'),
            'click',
            $.proxy(me.onSearchSubmit, me)
        );

        me._on(
            me.$el.find('.js--action-previous'),
            'click',
            $.proxy(me.onPrevNextPageClick, me)
        );

        me._on(
            me.$el.find('.js--action-next'),
            'click',
            $.proxy(me.onPrevNextPageClick, me)
        );
    },

    onPrevNextPageClick: function(event) {
        var me = this,
            $button = $(event.currentTarget),
            $paginationComponent = $button.closest('.is--b2b-component-pagination'),
            $pageSelect = $paginationComponent.find('select');

        $pageSelect.find('option[value="' + $button.attr('value') + '"]').prop('selected', true);
    },

    onDeleteClick: function(event) {
        var me = this,
            $target = $(event.currentTarget),
            $form = $target.closest('form');

        event.preventDefault();

        var originalButtonContent = $target.html();
        $target.html('<i class="icon--loading-indicator"></i>');
        $target.attr('disabled', 'disabled');

        if ($target.data('confirm')) {

            // save form to temp state
            me.defaults.activeForm = $form;

            $.ajax({
                'url': $target.data('confirm-url'),
                'type': 'post',
                'data': $form.serialize(),
                success: function(response) {
                    $target.html(originalButtonContent);
                    $target.prop('disabled', false);

                    $.b2bConfirmModal.open(response, {
                        'confirm': function() {
                            me.onConfirmRemove();
                        },
                        'cancel': function() {
                            $.b2bConfirmModal.close();
                        }
                    });
                },
                error: function() {
                    $target.html(originalButtonContent);
                    $target.prop('disabled', false);
                }
            });
        } else {
            me.removeItemAjax($form);
        }
    },

    removeItemAjax: function ($form) {
        $.ajax({
            url: $form.attr('action'),
            method: $form.attr('method'),
            data: $form.serialize(),
            success: function() {
                var $ajaxPanel = $form.closest('.b2b--ajax-panel');
                $ajaxPanel.trigger('b2b--ajax-panel_refresh');
            }
        });
    },

    onConfirmRemove: function () {
        var me = this;

        if (me.defaults.activeForm) {
            me.removeItemAjax(me.defaults.activeForm)
        }

        me.defaults.activeForm = null;

        $.b2bConfirmModal.close();
    },

    selectRow: function(grid, id) {
        grid.find('tr[data-row-id="' + id + '"]').addClass(this.defaults.isActiveClass);
    },

    onTableDataClick: function (event) {
        var me = this,
            $target = $(event.target),
            $parent = $target.parent(me.defaults.tableRowSelector),
            $siblings = $parent.siblings(me.defaults.tableRowSelector);

        $siblings.removeClass(me.defaults.isActiveClass);
        $parent.addClass(me.defaults.isActiveClass);
        me.getStorageItem().data('gridSelectedId', $parent.data('rowId'));
    },

    onChangeSearchTerm: function() {
        var me = this;
        me.defaults.searchTermChanged = true;
    },

    onSearchSubmit: function() {
        var me = this,
            $form = me.$el.find('form'),
            $pageSelect = $form.find('select[name="page"]'),
            $firstPage = $form.find('select[name="page"] option[value=1]');

        if(!$pageSelect.length || !me.defaults.searchTermChanged || $pageSelect.val() == 1) {
            return;
        }

        $firstPage.attr("selected", true);
    },

    getStorageItem: function() {
        var me = this,
            panel = me.$el.closest(me.defaults.ajaxPanelSelector);

        if(!panel.length) {
            return $(document);
        }

        return panel;
    },

    destroy: function() {
        var me = this;
        me._destroy();
    }
});