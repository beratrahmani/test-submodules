
// {block name="backend/config/view/custom_search/facet/detail"}

// {$smarty.block.parent}

// {include file="backend/config/live_shopping_facet.js"}

Ext.define('Shopware.apps.Config.LiveShoppingFacetExtension', {
    override: 'Shopware.apps.Config.view.custom_search.facet.Detail',

    initHandlers: function() {
        var handlers = this.callParent(arguments);
        handlers.push(Ext.create('Shopware.apps.Config.LiveShoppingFacet'));
        return handlers;
    }
});

// {/block}

// {block name="backend/config/view/custom_search/sorting/sorting/selection"}

// {$smarty.block.parent}

// {include file="backend/config/live_shopping_sorting.js"}

Ext.define('Shopware.apps.Config.LiveShoppingSortingExtension', {
    override: 'Shopware.apps.Config.view.custom_search.sorting.SortingSelection',

    initSortings: function() {
        var handlers = this.callParent(arguments);
        handlers.push(Ext.create('Shopware.apps.Config.LiveShoppingSorting'));
        return handlers;
    }
});

// {/block}
