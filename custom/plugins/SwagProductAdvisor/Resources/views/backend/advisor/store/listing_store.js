//

//{namespace name="backend/advisor/main"}
//{block name="backend/advisor/store/listing-store"}
Ext.define('Shopware.apps.Advisor.store.ListingStore', {
    extend: 'Shopware.store.Listing',
    model: 'Shopware.apps.Advisor.model.Advisor',

    configure: function () {
        return {
            controller: 'Advisor'
        };
    }
});
//{/block}