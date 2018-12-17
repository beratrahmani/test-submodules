
// {namespace name=backend/emotion/view/detail}
// {block name="backend_emotion_view_components_banner_mapping"}
// {$smarty.block.parent}
Ext.define('Shopware.apps.Emotion.swag_emotion_advanced.view.components.BannerMapping', {
    override: 'Shopware.apps.Emotion.view.components.BannerMapping',

    /**
     * @override
     * @return { Ext.data.Store }
     */
    createMappingStore: function() {
        var me = this,
            store = me.callParent(),
            fields = store.model.getFields(),
            newFields = [];

        Ext.Array.each(fields, function(field) {
            if (field.name === 'id') {
                return;
            }
            Ext.Array.push(newFields, { name: field.name, type: field.type.type });
        });

        Ext.Array.push(newFields, { name: 'as_icon', type: 'int' });

        return Ext.create('Ext.data.Store', {
            fields: newFields
        });
    },

    /**
     * @override
     * @return { Array }
     */
    createMappingGridColumns: function() {
        var me = this,
            columns = me.callParent(arguments),
            actionColumnIndex;

        Ext.Array.each(columns, function(column, index) {
            if (column.xtype === 'actioncolumn') {
                actionColumnIndex = index;
            }
        });

        Ext.Array.insert(columns, actionColumnIndex, [{
            dataIndex: 'as_icon',
            align: 'center',
            header: '{s namespace="backend/swag_emotion_advanced/view/main" name="banner_mapping/column/as_icon"}Show Icon{/s}',
            flex: 1,
            renderer: me.checkboxRenderer,
            editor: {
                xtype: 'checkboxfield',
                inputValue: 1,
                uncheckedValue: 0
            }
        }]);

        return columns;
    },

    /**
     * @override
     * @param { Number } id
     * @param { Object } config
     * @return { Object }
     */
    createMappingRecord: function(id, config) {
        var me = this,
            record = me.callParent(arguments);

        record.as_icon = config.as_icon || 0;

        return record;
    }
});
// {/block}
