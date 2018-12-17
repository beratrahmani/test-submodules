//

//{namespace name="backend/advisor/main"}
//{block name="backend/advisor/view/details/ui/property-selection"}
Ext.define('Shopware.apps.Advisor.view.details.ui.PropertySelection', {
    extend: 'Ext.container.Container',

    alias: 'widget.advisor-details-ui-PropertySelection',
    layout: 'anchor',

    snippets: {
        selectionLabel: '{s name="property_filter_property"}Property{/s}'
    },

    /**
     * @overwrite
     */
    initComponent: function () {
        var me = this;

        me.items = me.createItems();

        me.callParent(arguments);
    },

    /**
     * @returns { *[] }
     */
    createItems: function () {
        return [ this.createPropertySelection() ];
    },

    /**
     * @returns { Ext.form.field.ComboBox }
     */
    createPropertySelection: function () {
        var me = this;

        me.comboBox = Ext.create('Ext.form.field.ComboBox', {
            name: 'configuration',
            fieldLabel: me.snippets.selectionLabel,
            labelWidth: 150,
            displayField: 'name',
            valueField: 'id',
            anchor: '100%',
            store: me.createPropertyStore(),
            editable: false,
            forceSelection: true,
            allowBlank: false,
            listeners: {
                change: Ext.bind(me.onChange, me),
                select: Ext.bind(me.answerGrid.clearGrid, me.answerGrid)
            }
        });

        return me.comboBox;
    },

    /**
     * @param { Ext.form.field.Combobox } comboBox
     * @param { string } newValue
     */
    onChange: function (comboBox, newValue) {
        var me = this;

        me.question.set('configuration', newValue);

        me.answerGrid.refreshGridData(
            me.answerGrid.advisor,
            me.answerGrid.question,
            me.answerGrid.store
        )
    },

    /**
     * @returns { Ext.data.Store | * }
     */
    createPropertyStore: function () {
        var me = this;

        return Ext.create('Ext.data.Store', {
            fields: ['id', 'name'],
            proxy: {
                type: 'ajax',
                url: '{url controller=Advisor action=getPropertiesAjax}',
                extraParams: {
                    streamId: this.advisor.get('streamId')
                },
                reader: {
                    type: 'json',
                    root: 'data'
                }
            },
            listeners: {
                load: Ext.bind(me.createNames,me)
            }
        }).load();
    },

    /**
     * Creates the names for the property-combo
     */
    createNames: function () {
        var me = this;
        me.comboBox.getStore().each(function (rec) {
            rec.set('name', me.createName(rec));
        });
    },

    /**
     * Creates a single name for a single entry of the property-combo-box
     *
     * @param { Ext.data.Model } record
     * @returns { string }
     */
    createName: function (record) {
        return [
            record.get('name'),
            ' (ID #',
            record.get('id'),
            ')'
        ].join('');
    }
});
//{/block}