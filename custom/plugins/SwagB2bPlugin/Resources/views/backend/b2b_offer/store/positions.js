Ext.define('Shopware.apps.b2bOffer.store.Positions', {
  extend: 'Ext.data.Store',
  model: 'Shopware.apps.b2bOffer.model.Positions',
  proxy: {
    type: 'ajax',
    limitParam: undefined,
    pageParam: undefined,
    startParam: undefined,
    api: {
        create  : '{url module=backend controller=b2bofferlineitemreference action=createLineItem}',
        update  : '{url module=backend controller=b2bofferlineitemreference action=updateReference}',
        destroy : '{url module=backend controller=b2bofferlineitemreference action=deleteReference}'
    },
    url: '{url module=backend controller=b2bofferlineitemreference action=getAllPositions}',
    reader: {
      type: 'json',
      root: 'data',
      totalProperty: 'count'
    }
  }
});