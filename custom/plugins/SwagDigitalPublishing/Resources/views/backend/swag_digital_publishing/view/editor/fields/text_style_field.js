// {namespace name=backend/plugins/swag_digital_publishing/editor}
// {block name="backend/swag_digital_publishing/view/editor/textStyleField"}
Ext.define('Shopware.apps.SwagDigitalPublishing.view.editor.fields.TextStyleField', {
    extend: 'Ext.form.FieldContainer',

    layout: 'column',

    settings: null,

    initComponent: function () {
        var me = this;

        me.createSettings();

        me.items = me.createItems();

        me.callParent(arguments);
    },

    createItems: function () {
        var me = this,
            items = [];

        Ext.Array.each(me.settings, function (orientation) {
            items.push(me.createCheckboxField(orientation));
        });

        return items;
    },

    /**
     * @param { object } orientation
     * @returns { Ext.form.field.Checkbox  }
     */
    createCheckboxField: function (orientation) {
        var me = this,
            boxLabel = [
                '<span style="width: 16px; height: 14px; display:block;" class="',
                orientation.spriteClass,
                '"></span>'
            ].join('');

        return Ext.create('Ext.form.field.Checkbox', {
            columnWidth: 0.25,
            name: orientation.name,
            inputValue: orientation.name,
            checked: me.data[orientation.name] || false,
            fieldLabel: boxLabel,
            labelAlign: 'right',
            labelWidth: 20,
            labelStyle: 'margin: 4px 0 0 0; padding: 0; overflow: hidden; width: 16px; height: 14px; float:right;',
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
            { name: 'fontweight', spriteClass: 'sprite-edit-bold' },
            { name: 'fontstyle', spriteClass: 'sprite-edit-italic' },
            { name: 'underline', spriteClass: 'sprite-edit-underline' },
            { name: 'uppercase', spriteClass: 'sprite-edit-all-caps' }
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

        me.fireEvent('change', field, newValue, oldValue, eOpts);
    }
});
// {/block}
