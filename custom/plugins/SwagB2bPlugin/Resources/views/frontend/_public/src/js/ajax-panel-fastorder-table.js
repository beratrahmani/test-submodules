/**
 * global: StateManager
 *
 * B2B Fast Order Table Plugin for automatic row add and async product name fetching
 *
 * Usage:
 * <div class="b2b--ajax-panel" data-id="fast-order-grid" data-plugins="b2bFastOrderTable" data-product-url="{url action=getProductName}"></div>
 *
 * Inside Ajax Panel:
 * <table class="table--fastorder component-table b2b--component-grid" data-new-auto-row="true">
 *     <tr data-index="0">
 *         <td class="col-headline"></td>
 *         <td><input type="text" class="input-ordernumber" name="product[0][orderNumber]" value="" /></td>
 *         <td><input type="number" class="input-quantity" name="product[0][quantity]" value="" /></td>
 *      </tr>
 * </table>
 */
$.plugin('b2bFastOrderTable', {
    defaults: {

        errors: {
            productUrl: 'product-url is not defined. Automatic fetching of product name is disabled.'
        },

        moduleSelector: '.module-fastorder',
        tableSelector: '.table--fastorder',

        fastOrderInputContainerSelector: '.fast-order-inputs',

        inputQuantitySelector: '.b2b-table-quantity',
        submitFormSelector: '.cart-form, .order-list-form',

        orderListForm: '.order-list-form',

        panelAfterLoadEvent: 'b2b--ajax-panel_loaded'
    },

    init: function() {

        $.publish('b2b/fastorder/onInit', [ this ]);

        this.applyDataAttributes();
        this.registerGlobalListeners();
    },

    registerGlobalListeners: function() {
        var me = this;

        $.publish('b2b/fastorder/registerGlobalListeners', [ me ]);

        var module = me.defaults.moduleSelector;

        me._on(module, 'keyup change', 'input', $.proxy(me.onTableInputChange, me));
        me._on(module, 'change', '.input-ordernumber', $.proxy(me.onTableInputOrderNumberChange, me));

        me._on(module, 'submit', me.defaults.submitFormSelector, $.proxy(me.onFormSubmit, me));

        me._on(document, me.defaults.panelAfterLoadEvent, $.proxy(me.onAfterLoadEvent, me));

        $(window).bind('beforeunload', $.proxy(me.beforeUnload, me));

        StateManager.addPlugin('.csv--configuration .configuration--header', 'swCollapsePanel', {
            'contentSiblingSelector': '.csv--configuration .configuration--content'
        });
    },

    beforeUnload: function () {
        var me = this;

        $.publish('b2b/fastorder/beforeUnload', [ me ]);

        if (me.checkProductsForQuantity()) {
            return true;
        }
    },

    checkProductsForQuantity: function () {
        var me = this;
        var productsCheck = false;

        $.publish('b2b/fastorder/beforeUnload', [ me, productsCheck ]);

        $(me.defaults.inputQuantitySelector).each(function () {
            if($(this).val() > 0) {
                productsCheck = true;
                return false;
            }
        });

        return productsCheck;
    },
    
    onAfterLoadEvent: function (event, eventData) {
        var me = this;

        $.publish('b2b/fastorder/onAfterLoadEvent', [ me ]);

        if (eventData.source.hasClass('cart-form')) {
            var $collapseCart = $('*[data-collapse-cart="true"]');

            if ($collapseCart) {
                $collapseCart.data('plugin_swCollapseCart').onMouseEnter(event);
                $.publish('plugin/swAddArticle/onAddArticle');
            }
        }

        $(me.defaults.inputQuantitySelector).each(function () {
            $(this).val('');
        });
    },

    onTableInputChange: function(event) {
        var me = this,
            $productTable = $(event.target).closest(me.defaults.tableSelector);

        $.publish('b2b/fastorder/onTableInputChange', [ me, $productTable ]);

        // prevent new line adding if last row inputs are empty
        var $lastRow = $productTable.find('tr:last');
        if (!$lastRow.find('input:first').val()) {
            return;
        }
        if(!$lastRow.find('input:first').val().length && !$lastRow.find('input:last').val().length) {
            return;
        }

        // setting index for new row
        var $newRow = $lastRow.clone(),
            rowIndex = $lastRow.data('index'),
            newIndex = rowIndex + 1;

        // remove search results for the new row
        $newRow.find('b2b--search-results').remove();

        $newRow.find('input:first').attr('name', 'products[' + newIndex + '][referenceNumber]');
        $newRow.find('input:last').attr('name', 'products[' + newIndex + '][quantity]');

        var $newRowContent = $newRow.html();
        $productTable.append('<tr data-index="' + newIndex + '">' + $newRowContent + '</tr>');
    },

    onTableInputOrderNumberChange: function(event) {
        var me = this,
            $input = $(event.target),
            $row = $input.closest('tr'),
            $headlineColumn = $row.find('.col-headline'),
            productUrlElement = $('[data-product-url]');

        $.publish('b2b/fastorder/onTableInputOrderNumberChange', [ me, $input, $row, $headlineColumn, productUrlElement ]);
        
        if(!productUrlElement) {
            console.warn(me.defaults.errors.productUrl);
            return;
        }

        $.ajax({
            url: productUrlElement.data('product-url'),
            data: {
                orderNumber: $($input).val()
            },
            type: 'GET',
            success: function(data){

                $.publish('b2b/fastorder/onTableInputOrderNumberChange/ajax/success', [ me, data, $input, $row, $headlineColumn, productUrlElement ]);

                $headlineColumn.html(data);
                $.publish('plugin/b2bSuite/onSetProduct', $headlineColumn);
            }
        });
    },

    onFormSubmit: function(event) {
        var me = this,
            $form = $(event.target);

        $.publish('b2b/fastorder/onFormSubmit', [ me, $form ]);

        me.handleRequest(
            $('[data-id="fast-order-remote-box"]'),
            $form,
            $(me.defaults.fastOrderInputContainerSelector)
        );

        event.preventDefault();
    },

    handleRequest: function ($panel, $form, $formCloneInputs) {
        var me = this,
            $clonedForm = $form.clone();

        $.publish('b2b/fastorder/handleRequest', [ me, $clonedForm ]);

        $formCloneInputs.find('input').each(function () {
            $(this).clone().appendTo($clonedForm);
        });

        $clonedForm.find('input').each(function () {
            me.checkProducts($(this), $clonedForm);
        });

        var $selectedOption = $(me.defaults.orderListForm).find('select[name="orderlist"] option:selected');

        if ($selectedOption.length) {
            $clonedForm.append('<input type="hidden" name="orderlist" value="' + $selectedOption.val() + '">');
        }

        $clonedForm.data('ajax-panel-trigger-reload', 'fast-order-grid');
        
        $panel.trigger('b2b--do-ajax-call', [$clonedForm.attr('action'), $panel, $clonedForm]);
    },

    checkProducts: function ($element, $form) {

        $.publish('b2b/fastorder/checkProducts', [ $element, $form ]);

        if (($element.hasClass('input-quantity') || $element.hasClass('b2b-table-quantity')) && !$element.val()) {
            $form
                .find('input[name="' + $element.attr("name").replace("quantity", "referenceNumber") + '"]')
                .remove();

            $element.remove();
            return;
        }

        if ($element.hasClass('input-quantity') || $element.hasClass('b2b-table-quantity')) {
            var $reference = $form.find('input[name="' + $element.attr("name").replace("quantity", "referenceNumber") + '"]');

            if (!$reference.val()) {
                $reference.remove();
                $element.remove();
            }
        }
    },

    destroy: function() {
        var me = this;
        me._destroy();
    }
});

$(document).b2bFastOrderTable();