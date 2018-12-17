//

//{namespace name="backend/advisor/main"}
//{block name="backend/advisor/view/main/listing"}
Ext.define('Shopware.apps.Advisor.view.main.Listing', {
    extend: 'Shopware.window.Listing',
    alias: 'widget.Listing',
    width: '60%',
    height: '90%',
    title: '{s name="advisor_title"}Shopping Advisor{/s}',

    /**
     * @returns { { listingGrid: string, listingStore: string } }
     */
    configure: function () {
        return {
            listingGrid: 'Shopware.apps.Advisor.view.main.Grid',
            listingStore: 'Shopware.apps.Advisor.store.ListingStore'
        };
    }
});
//{/block}