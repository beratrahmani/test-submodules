/**
 * B2B Custom Order Number Table Plugin for automatic row add and async product name fetching
 *
 * Usage:
 * <div class="b2b--ajax-panel module-ordernumber" data-id="ordernumber-grid" data-url="{url action=grid}" data-plugins=" b2bOrderNumber" data-product-url="{url action=getProductName}"></div>
 *
 * Inside Ajax Panel:
 * <tr data-save-url="{if $row->id}{url action="update"}{else}{url action="create"}{/if}" data-id="{$row->id}">
 *     <tr data-index="0">
 *         <td class="col-headline"></td>
 *         <td>
 *             <div class="b2b--search-container">
 *                 <input type="text" class="input-ordernumber" name="orderNumber" data-product-search="{url controller=b2bproductsearch action=searchProduct}" value="{$row->orderNumber}"/>
 *             </div>
 *         </td>
 *         <td><input type="text" class="input-customordernumber" name="customOrderNumber" value="{$row->customOrderNumber}"/></td>
 *         <td data-label="{s name="Actions"}Actions{/s}" class="col-actions">
 *             <button name="saveButton" title="{s name="SaveOrderNumber"}Save ordernumber{/s}" type="button" class="btn btn--edit is--small is--hidden"><i class="icon--disk"></i></button>
 *             <form action="{url action=remove}" method="post" class="form--inline">
 *                 <input type="hidden" name="id" value="{$row->id}">
 *                 <button title="{s name="DeleteOrderNumber"}Delete Ordernumber{/s}" type="submit" class="btn is--primary is--small component-action-delete {if !$row->id}is--hidden{/if} {b2b_acl controller=b2bordernumber action=remove}" data-confirm="true" data-confirm-url="{url controller="b2bconfirm" action="remove"}">
 *                    <i class="icon--trash"></i>
 *                 </button>
 *             </form>
 *         </td>
 *      </tr>
 * </table>
 */
$.plugin('b2bOrderNumber', {
    defaults: {

        errors: {
            productUrl: 'product-url is not defined. Automatic fetching of product name is disabled.'
        },

        moduleSelector: '.module-ordernumber',
        tableSelector: '.table--ordernumber',
        rowSelector: '[data-class="row"]',
        inputSelector: '.input-ordernumber',
        inputCustomOrderNumberSelector: '.input-customordernumber',
        buttonEditSelector: '.btn--edit',
        enterKey: 'Enter'
    },

    init: function() {

        $.publish('b2b/ordernumber/onInit', [ this ]);

        this.applyDataAttributes();
        this.registerGlobalListeners();
    },

    registerGlobalListeners: function() {
        var me = this;
        var module = me.defaults.moduleSelector;

        $.publish('b2b/ordernumber/registerGlobalListeners', [ me, module ]);

        me._on(module, 'keyup change', me.defaults.tableSelector + ' input', $.proxy(me.onTableInputChange, me));
        me._on(module, 'change', me.defaults.inputSelector, $.proxy(me.onTableInputOrderNumberChange, me));

        me._on(module, 'keydown change', me.defaults.inputCustomOrderNumberSelector, $.proxy(me.onKeyDownEvent, me));
        me._on(module, 'click', me.defaults.buttonEditSelector, $.proxy(me.onSaveOrderNumber, me));
    },

    onSaveOrderNumber: function (event) {
        event.preventDefault();
        event.stopPropagation();

        var me = this,
            $target = $(event.target),
            $row = $target.closest('tr'),
            $saveUrl = $row.attr('data-save-url'),
            $id = $row.attr('data-id'),
            $orderNumberInput = $row.find(me.defaults.inputSelector),
            $customOrderNumber = $row.find(me.defaults.inputCustomOrderNumberSelector);

        $.publish('b2b/ordernumber/onSaveOrderNumber', [ me, $target, $row, $saveUrl, $id, $orderNumberInput, $customOrderNumber ]);

        if (!$saveUrl) {
            console.warn('No save url found');
            return;
        }

        var $filterField = $('input[name="filters[all][field-name]"]').val(),
            $filterType = $('input[name="filters[all][type]"]').val(),
            $filterValue = $('input[name="filters[all][value]"]').val(),
            $sortBy = $('select[name="sort-by"] option[selected="selected"]').val(),
            $page = $('select[name="page"] option[selected="selected"]').val();

        $.ajax({
            url: $saveUrl,
            type: 'POST',
            data: {
                id: $id,
                orderNumber: $($orderNumberInput).val(),
                customOrderNumber: $($customOrderNumber).val(),
                "filters[all][field-name]": $filterField,
                "filters[all][type]": $filterType,
                "filters[all][value]": $filterValue,
                "sort-by": $sortBy,
                "page": $page
            },
            success: function(data) {
                $('[data-id="ordernumber-grid"]').html(data);

                $.publish('b2b/ordernumber/onSaveOrderNumber/success', [ me, data, $target, $row, $saveUrl, $id, $orderNumberInput, $customOrderNumber ]);
            }
        });
    },

    onTableInputChange: function(event) {
        var me = this,
            $target = $(event.target),
            $orderNumberTable = $target.closest(me.defaults.tableSelector),
            $actualRow = $target.closest('tr');

        $.publish('b2b/ordernumber/onTableInputChange', [ me, $target, $orderNumberTable, $actualRow ]);

        if (!($actualRow.attr('data-save-url'))) {
            return;
        }

        $actualRow.find('.col-actions>button').removeClass('is--hidden');

        // prevent new line adding if first row inputs are empty
        var $lastRow = $orderNumberTable.find('tr:last');

        if(!$lastRow.find(me.defaults.inputSelector).val().length
            && !$lastRow.find(me.defaults.inputCustomOrderNumberSelector).val().length) {
            return;
        }

        // setting index for new row
        var $newRow = $lastRow.clone();

        // remove search results for the new row
        $newRow.find('b2b--search-results').remove();

        $newRow.find('.col-headline').html('');
        $newRow.find(me.defaults.inputSelector).attr('value', '');
        $newRow.find(me.defaults.inputCustomOrderNumberSelector).attr('value', '');
        $newRow.find('.col-actions button').addClass('is--hidden');

        var $createUrl = $lastRow.attr('data-save-url');

        var $newRowContent = $newRow.html();
        $orderNumberTable.append("<tr data-save-url='" + $createUrl + "'>" + $newRowContent + "</tr>");
    },

    onTableInputOrderNumberChange: function(event) {
        var me = this,
            $input = $(event.target),
            $row = $input.closest('tr'),
            $headlineColumn = $row.find('.col-headline'),
            productUrlElement = $('[data-product-url]');

        $.publish('b2b/ordernumber/onTableInputChange', [ me, $input, $row, $headlineColumn, productUrlElement ]);

        if(!productUrlElement) {
            console.warn(me.defaults.errors.productUrl);
            return;
        }

        $.ajax({
            url: productUrlElement.attr('data-product-url'),
            data: {
                orderNumber: $($input).val()
            },
            type: 'GET',
            success: function(data){
                $headlineColumn.html(data);

                $.publish('b2b/ordernumber/onTableInputChange/success', [ me, data, $input, $row, $headlineColumn, productUrlElement ]);
            }
        });
    },

    onKeyDownEvent: function (event) {
        var me = this;

        $.publish('b2b/ordernumber/onKeyDownEvent', [ me ]);

        if (event.key === me.defaults.enterKey) {
            var $target = $(event.target),
                $row = $target.closest('tr'),
                $saveButton = $row.find(me.defaults.buttonEditSelector),
                $customOrderNumberInput = $row.find(me.defaults.inputCustomOrderNumberSelector);

            if ($customOrderNumberInput.val() && !$saveButton.hasClass('is--hidden')) {
                $saveButton.trigger('click');
            }
        }
    },

    destroy: function() {
        var me = this;
        me._destroy();
    }
});
