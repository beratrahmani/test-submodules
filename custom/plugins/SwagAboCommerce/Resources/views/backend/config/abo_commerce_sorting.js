// {namespace name=backend/abo_commerce/product_stream/view/main}
Ext.define('Shopware.apps.Config.AboCommerceSorting', {

    getLabel: function() {
        return '{s name="abo_commerce_sorting"}{/s}';
    },

    supports: function(sortingClass) {
        return (sortingClass === 'SwagAboCommerce\\Bundle\\SearchBundle\\Sorting\\AboCommerceSorting');
    },

    load: function(sortingClass, parameters, callback) {
        if (!Ext.isFunction(callback)) {
            throw new Error('Requires provided callback function');
        }
        callback(this._createRecord(parameters));
    },

    create: function(callback) {
        var me = this;

        if (!Ext.isFunction(callback)) {
            throw new Error('Requires provided callback function');
        }

        Ext.create('Shopware.apps.Config.view.custom_search.sorting.includes.CreateWindow', {
            title: me.getLabel(),
            items: [{
                xtype: 'custom-search-direction-combo',
                getAscendingLabel: function() {
                    return '{s name="abo_commerce_sorting_asc"}{/s}';
                },
                getDescendingLabel: function() {
                    return '{s name="abo_commerce_sorting_desc"}{/s}';
                }
            }],
            callback: function(values) {
                callback(me._createRecord(values));
            }
        }).show();
    },

    _createRecord: function(parameters) {
        var label = '{s name="abo_commerce_sorting_asc"}{/s}';

        if (parameters.direction === 'DESC') {
            label = '{s name="abo_commerce_sorting_desc"}{/s}';
        }

        return {
            'class': 'SwagAboCommerce\\Bundle\\SearchBundle\\Sorting\\AboCommerceSorting',
            'label': label,
            'parameters': parameters
        };
    }
});
