//{block name="backend/b2b_offer/application"}
Ext.define('Shopware.apps.b2bOffer', {
  name:'Shopware.apps.b2bOffer',
  extend:'Enlight.app.SubApplication',

  bulkLoad: false,

  loadPath:'{url action=load}',

  controllers: [ 'Main' ],

  views:[
    'list.B2bOffer',
    'list.Window',
    'list.B2bOfferFilter',

    'detail.Window',
    'detail.detail.Container',
    'detail.detail.B2bOffer',
    'detail.detail.Positions',
    'detail.detail.Discount',

    'detail.event.Container',

    'components.fields.Article'
  ],

  stores:['B2bOffer', 'Positions', 'Event', 'Comment'],
  models:['B2bOffer', 'Positions', 'Event'],

  launch: function() {
    var me = this,
        mainController = me.getController('Main');

    return mainController.mainWindow;
  }
});
//{/block}
