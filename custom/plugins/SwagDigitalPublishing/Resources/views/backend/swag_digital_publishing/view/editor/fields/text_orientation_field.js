// {namespace name=backend/plugins/swag_digital_publishing/editor}
// {block name="backend/swag_digital_publishing/view/editor/textOrientationField"}
Ext.define('Shopware.apps.SwagDigitalPublishing.view.editor.fields.TextOrientationField', {
    extend: 'Ext.form.FieldContainer',

    layout: 'column',

    settings: null,

    initComponent: function () {
        var me = this;

        if (!me.data['orientation']) {
            me.data['orientation'] = 'left';
        }

        me.createSettings();

        me.items = me.createItems();

        me.callParent(arguments);
    },

    /**
     * @returns { Array }
     */
    createItems: function () {
        var me = this,
            items = [];

        Ext.Array.each(me.settings, function (orientation) {
            items.push(me.createRadioField(orientation));
        });

        return items;
    },

    /**
     * @param { object } orientation
     * @returns { Ext.form.field.Radio }
     */
    createRadioField: function (orientation) {
        var me = this,
            boxLabel = [
                '<span style="width: 16px; height: 14px; display:block;" class="',
                orientation.spriteClass,
                '"></span>'
            ].join('');

        return Ext.create('Ext.form.field.Radio', {
            columnWidth: 0.25,
            name: 'orientation',
            inputValue: orientation.name,
            checked: (me.data['orientation'] === orientation.name),
            fieldLabel: boxLabel,
            labelAlign: 'right',
            labelWidth: 20,
            labelStyle: 'margin: 3px 0 0 0; padding: 0; overflow: hidden; width: 16px; height: 14px; float:right;',
            listeners: {
                change: Ext.bind(me.onChange, me)
            }
        });
    },

    /**
     * Creates the setting property
     */
    createSettings: function () {
        var me = this;

        if (me.settings !== null) {
            return;
        }

        me.settings = me.getSettings();
    },

    /**
     * @returns { Array }
     */
    getSettings: function () {
        return [
            { name: 'left', spriteClass: 'sprite-edit-alignment' },
            { name: 'center', spriteClass: 'sprite-edit-alignment-center' },
            { name: 'right', spriteClass: 'sprite-edit-alignment-right' },
            { name: 'justify', spriteClass: 'sprite-edit-alignment-justify' }
        ];
    },

    /**
     * @param { Ext.form.field.Field } field
     * @param { string|int } newValue
     * @param { string|int } oldValue
     * @param { object } eOpts
     */
    onChange: function (field, newValue, oldValue, eOpts) {
        var me = this;

        me.fireEvent('change', me, field, newValue, oldValue, eOpts);
    }
});
// {/block}
