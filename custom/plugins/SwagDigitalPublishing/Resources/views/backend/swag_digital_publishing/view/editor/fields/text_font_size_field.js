// {namespace name=backend/plugins/swag_digital_publishing/editor}
// {block name="backend/swag_digital_publishing/view/editor/textFontSizeField"}
Ext.define('Shopware.apps.SwagDigitalPublishing.view.editor.fields.TextFontSizeField', {
    extend: 'Ext.container.Container',

    layout: 'anchor',

    snippets: {
        fontSizeLabel: '{s name="fontSizeLabel"}{/s}',
        adjust: '{s name="adjustFontSize"}{/s}',
        adjustDyn: '{s name="adjustFontSizeDynamically"}{/s}',
        viewPortMobile: '{s namespace=backend/emotion/view/detail name="viewports/xs/name"}{/s}',
        viewPortMobileLandscape: '{s namespace=backend/emotion/view/detail name="viewports/s/name"}{/s}',
        viewPortTablet: '{s namespace=backend/emotion/view/detail name="viewports/m/name"}{/s}',
        viewPortTabletLandscape: '{s namespace=backend/emotion/view/detail name="viewports/l/name"}{/s}',
        viewPortDesktop: '{s namespace=backend/emotion/view/detail name="viewports/xl/name"}{/s}',
        adjustDynHelpText: '{s name="adjustDynHelpText"}{/s}'
    },

    initComponent: function () {
        var me = this;

        me.items = me.createItems();

        me.onChange(me.adjust, me.data['adjust']);

        me.callParent(arguments);
    },

    /**
     * @return { Array }
     */
    createItems: function () {
        var me = this;

        return [
            me.createNumberField('fontsize', me.snippets.fontSizeLabel, me.data['fontsize'] || 16),
            me.createCheckBox('adjust', me.snippets.adjust, '', me.data['adjust'] || false, false),
            me.createCheckBox('adjustDyn', me.snippets.adjustDyn, me.snippets.adjustDynHelpText, me.data['adjustDyn'] || false, me.data['adjust'] !== 'adjust'),
            me.createNumberField('fontsize_xs', me.snippets.viewPortMobile, me.data['fontsize_xs'] || 16),
            me.createNumberField('fontsize_s', me.snippets.viewPortMobileLandscape, me.data['fontsize_s'] || 18),
            me.createNumberField('fontsize_m', me.snippets.viewPortTablet, me.data['fontsize_m'] || 20),
            me.createNumberField('fontsize_l', me.snippets.viewPortTabletLandscape, me.data['fontsize_l'] || 24),
            me.createNumberField('fontsize_xl', me.snippets.viewPortDesktop, me.data['fontsize_xl'] || 28),
            me.createMarginField()
        ];
    },

    /**
     * @param { string } name
     * @param { string } label
     * @param { int } value
     * @return { Ext.form.field.Number }
     */
    createNumberField: function (name, label, value) {
        var me = this;

        me[name] = Ext.create('Ext.form.field.Number', {
            name: name,
            fieldLabel: label,
            minValue: 0,
            value: value,
            anchor: '100%',
            listeners: {
                change: Ext.bind(me.onChange, me)
            }
        });

        return me[name];
    },

    /**
     * @param { string } name
     * @param { string } label
     * @param { string } helpText
     * @param { bool } value
     * @param { bool } disabled
     * @return { Ext.form.field.Checkbox }
     */
    createCheckBox: function (name, label, helpText, value, disabled) {
        var me = this;

        me[name] = Ext.create('Ext.form.field.Checkbox', {
            name: name,
            inputValue: name,
            boxLabel: label,
            helpText: helpText,
            checked: value,
            disabled: disabled,
            anchor: '100%',
            listeners: {
                change: Ext.bind(me.onChange, me)
            }
        });

        return me[name];
    },

    /**
     * @return { Ext.container.Container }
     */
    createMarginField: function () {
        var me = this;

        me.marginField = Ext.create('Ext.container.Container', {
            padding: 10
        });

        return me.marginField;
    },

    /**
     * @param { Ext.form.field.Base } field
     * @param { mixed } newValue
     */
    onChange: function (field, newValue) {
        var me = this;

        if (field.getName() === 'adjust') {
            me.handleFontSizeFields(newValue);
        }

        if (field.getName() === 'fontsize') {
            me.handleFontSizeScaling(newValue);
        }

        me.fireEvent('change', field, newValue);
    },

    /**
     * @param { bool } disabled
     */
    handleFontSizeFields: function (disabled) {
        var me = this,
            method = disabled ? 'show' : 'hide';

        me.fontsize_xs[method]();
        me.fontsize_s[method]();
        me.fontsize_m[method]();
        me.fontsize_l[method]();
        me.fontsize_xl[method]();

        me.marginField[method]();

        me.adjustDyn.setDisabled(!disabled);
        me.fontsize.setDisabled(disabled);

        if (!disabled) {
            me.adjustDyn.setValue(false);
        }
    },

    /**
     * @param { number } value
     */
    handleFontSizeScaling: function (value) {
        var me = this;

        me.fontsize_xs.setValue(value);
        me.fontsize_s.setValue(value + 8);
        me.fontsize_m.setValue(value + 16);
        me.fontsize_l.setValue(value + 24);
        me.fontsize_xl.setValue(value + 32);
    }
});
// {/block}
