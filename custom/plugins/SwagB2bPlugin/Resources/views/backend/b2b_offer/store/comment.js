Ext.define('Shopware.apps.b2bOffer.store.Comment', {
  extend:'Ext.data.Store',
  model: 'Shopware.apps.b2bOffer.model.Event',
  proxy: {
      type: 'ajax',
      url: '{url module=backend controller=b2bofferlog action=commentList}',
      reader: {
          type: 'json',
          root: 'data',
          totalProperty: 'count'
      }
  }
});