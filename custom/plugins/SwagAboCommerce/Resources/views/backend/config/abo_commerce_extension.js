
// {block name="backend/config/view/custom_search/facet/detail"}

// {$smarty.block.parent}

// {include file="backend/config/abo_commerce_facet.js"}

Ext.define('Shopware.apps.Config.AboCommerceFacetExtension', {
    override: 'Shopware.apps.Config.view.custom_search.facet.Detail',

    initHandlers: function() {
        var handlers = this.callParent(arguments);
        handlers.push(Ext.create('Shopware.apps.Config.AboCommerceFacet'));
        return handlers;
    }
});
// {/block}

// {block name="backend/config/view/custom_search/sorting/sorting/selection"}

// {$smarty.block.parent}

// {include file="backend/config/abo_commerce_sorting.js"}

Ext.define('Shopware.apps.Config.AboCommerceSortingExtension', {
    override: 'Shopware.apps.Config.view.custom_search.sorting.SortingSelection',

    initSortings: function() {
        var handlers = this.callParent(arguments);
        handlers.push(Ext.create('Shopware.apps.Config.AboCommerceSorting'));
        return handlers;
    }
});
// {/block}
