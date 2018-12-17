// {namespace name=backend/plugins/swag_digital_publishing/editor}
// {block name="backend/swag_digital_publishing/view/editor/paddingField"}
Ext.define('Shopware.apps.SwagDigitalPublishing.view.editor.fields.PaddingField', {
    extend: 'Ext.form.FieldContainer',

    layout: 'fit',

    height: 108,

    initComponent: function () {
        var me = this;

        me.items = me.createItems();

        me.callParent(arguments);
    },

    /**
     * @returns { Array }
     */
    createItems: function () {
        var me = this,
            mainContainer, upContainer, leftRightContainer, downContainer;

        upContainer = me.createContainer('column', [
            me.createEmptyContainer(),
            me.createNumberField('paddingTop', me.data['paddingTop'], 0)
        ]);

        leftRightContainer = me.createContainer('column', [
            me.createNumberField('paddingLeft', me.data['paddingLeft'], 0),
            me.createCheckBox(),
            me.createNumberField('paddingRight', me.data['paddingRight'], 0)
        ], true);

        downContainer = me.createContainer('column', [
            me.createEmptyContainer(),
            me.createNumberField('paddingBottom', me.data['paddingBottom'], 0)
        ], true);

        mainContainer = me.createContainer('', [
            upContainer,
            leftRightContainer,
            downContainer
        ]);

        return [
            mainContainer
        ];
    },

    /**
     * @param { string } name
     * @param { int } value
     * @param { int } defaultValue
     * @returns { Ext.form.field.Number }
     */
    createNumberField: function (name, value, defaultValue) {
        var me = this;

        me[name] = Ext.create('Ext.form.field.Number', {
            columnWidth: 0.33,
            name: name,
            value: value | defaultValue,
            listeners: {
                change: function (field, newValue, oldValue, eOpts) {
                    me.onChange(field, newValue, oldValue, eOpts);
                }
            }
        });

        return me[name];
    },

    /**
     * @returns { Ext.form.field.Checkbox }
     */
    createCheckBox: function () {
        var me = this;

        me.chaining = Ext.create('Ext.form.field.Checkbox', {
            margin: '0 0 0 12',
            columnWidth: 0.33,
            name: 'paddingChain',
            boxLabel: '<span class="sprite-chain" style="margin-right: 11px; width:18px; height:20px; float: right; margin-top: 2px; display: block"></span>'
        });

        return me.chaining;
    },

    /**
     * @param { string } layout
     * @param { Array } items
     * @param { bool } marginTop
     * @returns { Ext.container.Container }
     */
    createContainer: function (layout, items, marginTop) {
        return Ext.create('Ext.container.Container', {
            layout: layout,
            items: items,
            padding: marginTop ? '10 0 0 0' : '0 0 0 0'
        });
    },

    /**
     * @returns { Ext.form.FieldContainer }
     */
    createEmptyContainer: function () {
        return Ext.create('Ext.form.FieldContainer', { columnWidth: 0.33 });
    },

    /**
     * @public
     * @returns { bool }
     */
    isChaining: function () {
        var me = this;

        return me.chaining.getValue();
    },

    /**
     * @param { Ext.form.field.Field } field
     * @param { string|int } newValue
     */
    onChange: function (field, newValue) {
        var me = this,
            fields = me.getFields(),
            chaining = me.isChaining();

        if (chaining) {
            Ext.each(fields, function(fieldName) {
                if (fieldName !== field.getName()) {
                    me[fieldName].suspendEvents();
                    me[fieldName].setValue(newValue);
                    me[fieldName].resumeEvents();
                }
            });
        }

        me.fireEvent('change', me, field, me.getValue());
    },

    /**
     * @returns { string[] }
     */
    getFields: function () {
        return ['paddingTop', 'paddingLeft', 'paddingRight', 'paddingBottom'];
    },

    /**
     * @returns { object }
     */
    getValue: function () {
        var me = this,
            fields = me.getFields(),
            value = {};

        Ext.each(fields, function(fieldName) {
            value[fieldName] = me[fieldName].getValue();
        });

        return value;
    }
});
// {/block}
