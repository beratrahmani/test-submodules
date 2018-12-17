// {block name="backend/config/view/custom_search/facet/detail"}
// {$smarty.block.parent}
// {include file="backend/config/bundle_facet.js"}

Ext.define('Shopware.apps.Config.BundleFacetExtension', {
    override: 'Shopware.apps.Config.view.custom_search.facet.Detail',

    initHandlers: function() {
        var handlers = this.callParent(arguments);
        handlers.push(Ext.create('Shopware.apps.Config.BundleFacet'));
        return handlers;
    }
});

// {/block}

// {block name="backend/config/view/custom_search/sorting/sorting/selection"}
// {$smarty.block.parent}
// {include file="backend/config/bundle_sorting.js"}

Ext.define('Shopware.apps.Config.BundleSortingExtension', {
    override: 'Shopware.apps.Config.view.custom_search.sorting.SortingSelection',

    initSortings: function() {
        var handlers = this.callParent(arguments);
        handlers.push(Ext.create('Shopware.apps.Config.BundleSorting'));
        return handlers;
    }
});

// {/block}
