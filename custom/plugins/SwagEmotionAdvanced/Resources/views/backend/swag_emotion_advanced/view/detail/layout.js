//
// {namespace name=backend/swag_emotion_advanced/view/main}
// {block name=backend/swag_emotion_advanced/view/detail/layout}
Ext.define('Shopware.apps.SwagEmotionAdvanced.view.detail.Layout', {

    override: 'Shopware.apps.Emotion.view.detail.Layout',

    initComponent: function() {
        var me = this;

        me.callParent(arguments);

        me.addEvents('changeSections');

        me.storytellingFieldset = me.createStoryTellingFieldset();
        me.insert(1, me.storytellingFieldset);
    },

    onModeChange: function(modeField, mode, currentMode) {
        var me = this, sections;

        if (mode === 'storytelling') {
            // Reset the cell height to the default value
            me.cellHeightField.setValue(185);

            // Disabling the cellHeight field
            me.disableCellHeightField();

            // Storytelling shopping worlds are always fullscreen
            me.fullscreenField.setValue(true);

            // Fire sections change event to check if the current settings fit
            sections = me.emotion.get('swagRows') || 6;
            me.fireEvent('changeSections', me.emotion, sections, sections, me.sectionRowsField, modeField, currentMode);
        }

        me.fullscreenField.setReadOnly(mode === 'storytelling');
        me.fullscreenField[(mode === 'storytelling') ? 'addCls' : 'removeCls']('x-form-readonly');
        me.fullscreenField[(mode === 'storytelling') ? 'addCls' : 'removeCls']('x-item-disabled');

        me.storytellingFieldset[(mode === 'storytelling') ? 'show' : 'hide']();

        me.callParent(arguments);
    },

    createResponsiveModeStore: function() {
        var me = this;

        me.responsiveModeStore = me.callParent(arguments);

        me.responsiveModeStore.add({
            'value': 'storytelling',
            'display': '{s name="settings/mode/store/story/display"}{/s}',
            'supportText': '{s name="settings/mode/store/story/desc"}{/s}'
        });

        return me.responsiveModeStore;
    },

    createStoryTellingFieldset: function() {
        var me = this;

        me.sectionRowsField = Ext.create('Ext.form.field.Number', {
            name: 'swagRows',
            fieldLabel: '{s name="settings/rows/label"}{/s}',
            helpText: '{s name="settings/rows/help"}{/s}',
            minValue: 1,
            maxValue: 12,
            step: 1,
            value: me.emotion.get('swagRows') || 6,
            allowBlank: false,
            labelWidth: me.defaults.labelWidth,
            listeners: {
                scope: me,
                buffer: me.defaults.eventBuffer,
                change: function(field, newValue, oldValue) {
                    me.fireEvent('changeSections', me.emotion, newValue, oldValue, field);
                }
            }
        });

        return Ext.create('Ext.form.FieldSet', {
            title: '{s name="settings/storytelling/fieldset"}{/s}',
            defaults: me.defaults,
            hidden: me.emotion.get('mode') !== 'storytelling',
            items: [
                me.sectionRowsField
            ]
        });
    }
});
// {/block}
