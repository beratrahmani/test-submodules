// {namespace name="backend/bundle/article/view/main"}
// {block name="backend/article/view/bundle/tabs/description"}
Ext.define('Shopware.apps.Article.view.bundle.tabs.Description', {

    extend: 'Ext.form.Panel',

    title: '{s name=description/title}Description{/s}',

    alias: 'widget.bundle-description',

    cls: 'bundle-description',

    plugins: [{
        ptype: 'translation',
        pluginId: 'translation',
        translationType: 'bundle-description'
    }],

    initComponent: function() {
        var me = this;

        me.items = me.getItems();

        me.callParent(arguments);
    },

    /**
     * Creates the columns for the grid panel.
     * @return Array
     */
    getItems: function() {
        return [{
            xtype: 'tinymce',
            anchor: '100%',
            name: 'description',
            translatable: true
        }];
    }

});
// {/block}
