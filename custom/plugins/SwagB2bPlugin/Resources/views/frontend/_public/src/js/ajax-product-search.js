/**
 * This plugin allows to use an ajax based product search on any input element inside an ajax panel.
 * To use this plugin you have to pass the plugin name in the data-plugins attribute on the parent ajax panel.
 *
 * Ajax Panel:
 * <div class="b2b--ajax-panel" data-id="example-plugin" data-url="{url}" data-plugins="b2bAjaxProductSearch"></div>
 *
 * Input Inside the Ajax Panel:
 *
 * <div class="b2b--search-container">
 *      <input type="text" name="ordernumber" data-product-search="{url controller=b2bproductsearch action=searchProduct}">
 * </div>
 *
 */
$.plugin('b2bAjaxProductSearch', {

    defaults: {

        resultsActive: false,

        isLoading: false,

        searchContainer: '.b2b--search-container',

        resultsSelector: '.b2b--search-results',

        inputSelector: 'input.input-ordernumber',

        quantitySelector: '.b2b--search-quantity',

        loadingContainerCls: 'container--element-loader',

        loadingContainerIcon: 'icon--loading-indicator',

        keyMap: {
            down: 40,
            up: 38,
            enter: 13,
            tab: 9,
            left: 37,
            right: 39
        },

        searchDelay: 300,

        delayTimer: 0
    },

    init: function () {
        var me = this,
            $inputSearch = $(me.defaults.inputSelector);
        this.applyDataAttributes();

        if(!$inputSearch.length) {
            return;
        }

        $(me.defaults.inputSelector).attr('autocomplete', 'off');

        me._on(me.$el, 'keydown', '*[data-product-search]', $.proxy(me.onKeyDown, me));

        me._on(me.$el, 'keyup', '*[data-product-search]', $.proxy(me.onKeyUp, me));

        me._on(me.$el, 'focus', '*[data-product-search]', $.proxy(me.onFocus, me));

        me._on(me.$el, 'click', me.defaults.resultsSelector + ' li', $.proxy(me.onClickSelectElement, me));

        me._on(me.$el, 'click', 'button[type="submit"]', $.proxy(me.onSubmitForm, me));

        me._on('body', 'click', $.proxy(me.onBodyClick, me));
    },

    loadingEnable: function(input) {
        var me = this,
            $input = $(input),
            $searchContainer = $input.closest(me.defaults.searchContainer),
            $loadingContainer = $searchContainer.find('.' + me.defaults.loadingContainerCls);

        if($input.data('loading')) {
            return;
        }

        $loadingContainer.remove();
        $input.after('<div class="' + me.defaults.loadingContainerCls + '"><i class="' + me.defaults.loadingContainerIcon + '"></i></div>');
        $searchContainer.find('.' + me.defaults.loadingContainerCls).fadeIn('fast');

        $input.data('loading', true);
        me.defaults.isLoading = true;
    },

    loadingDisable: function(input) {
        var me = this,
            $input = $(input),
            $searchContainer = $input.closest(me.defaults.searchContainer);

        if(!$input.data('loading')) {
            return;
        }

        $searchContainer.find('.' + me.defaults.loadingContainerCls).fadeOut('fast', function() {
            $(this).remove();

            $input.data('loading', false);
            me.defaults.isLoading = false;
        });
    },

    onSubmitForm: function(event) {
        var me = this;

        if(!me.defaults.resultsActive) {
            return;
        }

        event.preventDefault();
        event.stopPropagation();
    },

    onBodyClick: function (event) {
        var me = this;

        if(!me.defaults.resultsActive) {
            return;
        }

        me.hideResults();
    },

    onKeyDown: function (event) {
        var me = this,
            keyCode = event.keyCode;

        if(keyCode === me.defaults.keyMap.tab) {
            me.hideResults();
        }

        if(keyCode === me.defaults.keyMap.enter) {
            me.onSelectElement(event);
        }
    },

    onKeyUp: function (event) {
        var me = this,
            input = $(event.currentTarget),
            term = input.val(),
            keyCode = event.keyCode;

        event.preventDefault();

        if(!term) {
            me.hideResults();
        }

        if(keyCode === me.defaults.keyMap.left || keyCode === me.defaults.keyMap.right) {
            return;
        }

        if(keyCode === me.defaults.keyMap.down && me.defaults.resultsActive) {
            me.onNextElement(event);
            return;
        }

        if(keyCode === me.defaults.keyMap.up && me.defaults.resultsActive) {
            me.onPreviousElement(event);
            return;
        }

        me.searchRequest(term, input);
    },

    onNextElement: function(event) {
      var me = this,
          $currentTarget = $(event.currentTarget),
          $searchContainer = $currentTarget.closest(me.defaults.searchContainer),
          $resultsContainer = $searchContainer.find(me.defaults.resultsSelector),
          $currentElement = $searchContainer.find('.is--active');

      if(!$currentElement.length) {
          $resultsContainer.find('li').first().addClass('is--active');
          return;
      }

      $currentElement = $resultsContainer.find('.is--active');
      $currentElement.removeClass('is--active');
      $currentElement.next('li').addClass('is--active');
    },

    onPreviousElement: function(event) {
        var me = this,
            $currentTarget = $(event.currentTarget),
            $searchContainer = $currentTarget.closest(me.defaults.searchContainer),
            $resultsContainer = $searchContainer.find(me.defaults.resultsSelector),
            $currentElement = $searchContainer.find('.is--active');

        if(!$currentElement.length) {
            $resultsContainer.find('li').last().addClass('is--active');
            return;
        }

        $currentElement = $resultsContainer.find('.is--active');
        $currentElement.removeClass('is--active');
        $currentElement.prev('li').addClass('is--active');
    },

    onSelectElement: function(event) {
        var me = this,
            $currentTarget = $(event.currentTarget),
            $searchContainer = $currentTarget.closest(me.defaults.searchContainer),
            $currentElement = $searchContainer.find('.is--active');

        if(!$currentElement.length || !me.defaults.resultsActive) {
            return;
        }

        event.preventDefault();

        var orderNumber = $currentElement.find('span').html();
        $currentTarget.val(orderNumber);
        $currentTarget.trigger('change');

        me.setQuantityInput($searchContainer, $currentElement);

        me.hideResults();
    },

    onClickSelectElement: function(event) {
        var me = this,
            $currentTarget = $(event.currentTarget),
            $searchContainer = $currentTarget.closest(me.defaults.searchContainer),
            $input = $searchContainer.find('input');

        if(!$currentTarget.length) {
            return;
        }

        var orderNumber = $currentTarget.find('span').html();
        $input.val(orderNumber);
        $input.trigger('change');

        me.setQuantityInput($searchContainer, $currentTarget);

        me.hideResults();
    },

    onFocus: function(event) {
        var me = this,
            $searchInput = $(event.currentTarget),
            term = $searchInput.val();

        me.searchRequest(term, $searchInput);
    },

    setQuantityInput: function($searchContainer, $currentElement) {
        var me = this,
            $quantityInput = $searchContainer.closest('tr').find(me.defaults.quantitySelector);

        if(!$quantityInput.length) {
            $quantityInput = $searchContainer.closest('form').find(me.defaults.quantitySelector);
        }

        if($quantityInput.length) {
            $quantityInput.attr({
                'max': $currentElement.data('max'),
                'min': $currentElement.data('min'),
                'step': $currentElement.data('step'),
            });

            if(!$currentElement.data('max')) {
                $quantityInput.removeAttr('max');
            }
        }
    },

    removeQuantityInput: function(input) {
        var me = this,
            $searchContainer = input.closest(me.defaults.searchContainer),
            $quantityInput = $searchContainer.closest('tr').find(me.defaults.quantitySelector);

        if($quantityInput.length) {
            $quantityInput.removeAttr('min');
            $quantityInput.removeAttr('max');
            $quantityInput.removeAttr('step');
        }
    },

    searchRequest: function (term, input) {
        var me = this,
            searchUrl = input.data('product-search');

        me.removeQuantityInput(input);

        if (term.length) {

            me.loadingEnable(input);

            clearTimeout(me.defaults.delayTimer);
            me.defaults.delayTimer = setTimeout(function() {

                $.ajax({
                    type: 'get',
                    url: searchUrl,
                    data: {
                        term: term
                    },
                    success: function(resultTemplate) {
                        me.showResults(input, resultTemplate);
                        me.loadingDisable(input);
                    },
                    error: function() {
                        me.loadingDisable(input);
                    }
                });

            }, me.defaults.searchDelay);

        } else {
            me.loadingDisable(input);
            me.hideResults(input);
        }
    },

    showResults: function (input, resultTemplate) {
        var me = this;

        if(me.defaults.resultsActive) {
            me.updateResults(input, resultTemplate);
            return;
        }

        me.defaults.resultsActive = true;

        input.after('<div class="b2b--search-results">' + resultTemplate + '</div>');
    },

    updateResults: function(input, resultTemplate) {
        var $searchContainer = input.closest('.b2b--search-container'),
        $searchResults = $searchContainer.find('.b2b--search-results');

        $searchResults.html(resultTemplate);
    },

    hideResults: function () {
        var me = this;

        me.defaults.resultsActive = false;
        $('.b2b--search-results').remove();
    },

    destroy: function() {
        var me = this;
        me._destroy();
    }
});