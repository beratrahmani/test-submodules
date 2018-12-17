Ext.define('Shopware.apps.b2bOffer.view.components.fields.Article', {
  extend: 'Shopware.form.field.ProductSingleSelection',
  alias: 'widget.b2boffer-components-fields-article',
  id: 'b2boffer-components-fields-article',
  width: 200,

  initComponent: function() {
    var me = this;

    me.store = Ext.create('Ext.data.Store', {
      model: 'Shopware.model.Dynamic',
      proxy: {
        type: 'ajax',
        url: '{url controller="EntitySearch" action="search"}?model=Shopware\\Models\\Article\\Article',
        reader: Ext.create('Shopware.model.DynamicReader')
      }
    });
    me.callParent(arguments);
  }
});
