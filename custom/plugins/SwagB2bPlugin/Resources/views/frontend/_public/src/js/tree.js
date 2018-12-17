$.plugin('b2bTree', {
    defaults: {
    },

    init: function() {
        var me = this,
            $tree = me.$el.find('.is--b2b-tree');

        me.applyDataAttributes();

        me._on(
            $tree,
            'click',
            'a',
            function(event) {

                event.preventDefault();
                event.stopPropagation();

                var $anchor = $(this),
                    $listItem = $anchor.closest('li');

                if($listItem.hasClass('is--loading')) {
                    return;
                }

                if($listItem.hasClass('is--opened')) {
                    $listItem.addClass('is--closed')
                        .removeClass('is--opened');

                    $listItem.find('ul').remove();
                    return;
                }

                $listItem.removeClass('is--closed')
                    .addClass('is--loading');

                var $selectedInput = $tree.find('input:checked');

                $.ajax({
                    url: this.href,
                    success: function(response) {
                        $listItem
                            .removeClass('is--loading')
                            .addClass('is--opened');

                        $listItem.append(response);
                        $selectedInput.prop('checked', true);
                        me.doLayout($tree);

                        $anchor.trigger('tree_toggle_menu', $anchor);
                    }
                });
            }
        );

        me._on(
            $tree,
            'click',
            'li',
            function(event) {
                var $target = $(event.target);

                if($target.parents('a').length) {
                    return;
                }

                if($target.is('input')) {
                    return;
                }

                var $input = $target.closest('input');

                if($input.length) {
                    $input.click();
                }
            });

        me._on(
            $tree,
            'change',
            'input',
            function() {
                var $input = $(this);
                var value = $input.val();
                var $tree = $(this).closest('.is--b2b-tree-container');
                var inputId = $tree.data('treeConnectedInputId');

                $tree
                    .find('.is--selected')
                    .removeClass('is--selected');

                $input
                    .closest('li')
                    .addClass('is--selected');

                $('#' + inputId).val(value);
            }
        );
    },

    doLayout: function($tree) {
        $tree.find('.b2b-tree-node-inner').each(function() {
            var $this = $(this),
                multiplier = $this.parents('ul.is--b2b-tree').length - 1;

            $this.css('marginLeft', multiplier * 24);
        });
    },

    destroy: function() {
        var me = this;
        me._destroy();
    }
});
