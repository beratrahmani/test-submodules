//{namespace name=backend/plugins/b2b_debtor_plugin}

Ext.define('Shopware.apps.Customer.debtor.view.detail.Grid', {
    extend: 'Ext.grid.Panel',
    title: '{s name=b2b_Debtor_Landingpage_tab}Debtor landingpage{/s}',
    plugins: [
        Ext.create('Ext.grid.plugin.RowEditing', {
            clicksToEdit: 1
        })
    ],

    initComponent: function() {
        var me = this,
            emotionStore = Ext.create('Shopware.apps.Customer.debtor.store.Emotion');

        emotionStore.load();

        me.columns = me.getColumns(emotionStore);

        me.callParent(arguments);
    },

    getColumns: function (emotionStore) {
        var me = this;
        return [
            {
                header: '{s name=Name}Name{/s}',
                flex: 1,
                dataIndex: 'name',
                renderer: function (value, metaData, record) {
                    return me.debtorRenderer(value, record)
                }
            },
            {
                header: '{s name=Email}Email{/s}',
                flex: 1,
                dataIndex: 'email',
                renderer: function (value, metaData, record) {
                    return me.debtorRenderer(value, record)
                }
            },
            {
                header: '{s name=Landingpage}Landingpage{/s}',
                flex: 1,
                dataIndex: 'emotion',
                editor: {
                    xtype: 'combobox',
                    allowBlank: false,
                    forceSelection: true,
                    displayField:'emotionName',
                    valueField:'id',
                    store: emotionStore
                },
                renderer: function (value) {
                    return me.renderEmotionName(emotionStore, value);
                }
            }
        ];
    },

    renderEmotionName: function (emotionStore, value) {
        var displayName = '';

        emotionStore.each(function (data) {
            if (value === data.get('id')) {
                displayName = data.get('emotionName');
                return false;
            }
        });

        if (value === 0) {
            displayName = '<b>' + displayName + '</b>';
        }

        return displayName;
    },

    debtorRenderer: function (value, record) {
        if (record.get('type') === 'debtor') {
            return '<b>' + value + '</b>'
        }
        
        return value;
    }
});