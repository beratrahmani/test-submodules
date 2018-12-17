// {namespace name=backend/plugins/swag_digital_publishing/editor}
// {block name="backend/swag_digital_publishing/view/editor/shadowField"}
Ext.define('Shopware.apps.SwagDigitalPublishing.view.editor.fields.ShadowField', {
    extend: 'Ext.container.Container',

    layout: 'anchor',

    snippets: {
        shadowXLabel: '{s name="shadowXLabel"}{/s}',
        shadowYLabel: '{s name="shadowYLabel"}{/s}',
        shadowBlurLabel: '{s name="shadowBlurLabel"}{/s}',
        shadowColorLabel: '{s name="shadowColorLabel"}{/s}'
    },

    initComponent: function () {
        var me = this;

        me.items = me.createItems();

        me.callParent(arguments);
    },

    /**
     * @returns { Array }
     */
    createItems: function () {
        var me = this;

        return [
            me.createShadowColorField(),
            me.createSliderField('shadowOffsetX', me.snippets.shadowXLabel),
            me.createSliderField('shadowOffsetY', me.snippets.shadowYLabel),
            me.createSliderField('shadowBlur', me.snippets.shadowBlurLabel)
        ];
    },

    /**
     * @returns { Shopware.form.field.ColorField }
     */
    createShadowColorField: function () {
        var me = this;

        me.colorField = Ext.create('Shopware.form.field.ColorField', {
            anchor: '100%',
            name: 'shadowColor',
            fieldLabel: me.snippets.shadowColorLabel
        });

        me.colorField.inputField.setValue(me.data['shadowColor'] || '#FFFFFF');
        me.colorField.inputField.on('change', Ext.bind(me.onChange, me));

        return me.colorField;
    },

    /**
     * @param { string } name
     * @param { string } label
     * @returns { Ext.slider.Single }
     */
    createSliderField: function (name, label) {
        var me = this;

        return Ext.create('Ext.slider.Single', {
            name: name,
            fieldLabel: label,
            anchor: '100%',
            labelStyle: 'margin-top: 0;',
            value: me.data[name] || 0,
            margin: '15 0 15 0',
            minValue: name !== 'shadowBlur' ? -20 : 0,
            maxValue: 20,
            increment: 1,
            listeners: {
                change: Ext.bind(me.onChange, me)
            }
        });
    },

    /**
     * @param { Ext.form.field.Field } field
     * @param { string|int } newValue
     * @param { string|int } oldValue
     * @param { object } eOpts
     */
    onChange: function (field, newValue, oldValue, eOpts) {
        var me = this;

        me.fireEvent('change', field, newValue, oldValue, eOpts);
    }
});
// {/block}
