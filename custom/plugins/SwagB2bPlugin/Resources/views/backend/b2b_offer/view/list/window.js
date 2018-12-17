//{namespace name=backend/plugins/b2b_debtor_plugin}
Ext.define('Shopware.apps.b2bOffer.view.list.Window', {
  extend: 'Shopware.window.Listing',
  alias: 'widget.Offer-list-window',
  id: 'b2bOffer-listing--window',
  height: 450,
  width: 1300,
  title : '{s name=Offers}Offers{/s}',

  configure: function() {
    return {
      listingGrid: 'Shopware.apps.b2bOffer.view.list.B2bOffer',
      listingStore: 'Shopware.apps.b2bOffer.store.B2bOffer',

      extensions: [
          { xtype: 'offer-listing-filter-panel' }
      ]
    };
  },
});