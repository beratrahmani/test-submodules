Ext.define('Shopware.apps.b2bOffer.store.Event', {
  extend:'Ext.data.Store',
  model: 'Shopware.apps.b2bOffer.model.Event',
  proxy: {
      type: 'ajax',
      url: '{url module=backend controller=b2bofferlog action=log}',
      reader: {
          type: 'json',
          root: 'data',
          totalProperty: 'count'
      }
  },
  pageSize: 20
});