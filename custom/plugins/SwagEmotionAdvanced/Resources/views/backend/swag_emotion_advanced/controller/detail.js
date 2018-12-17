//
// {namespace name=backend/swag_emotion_advanced/view/main}
// {block name=backend/swag_emotion_advanced/controller/detail}
Ext.define('Shopware.apps.SwagEmotionAdvanced.controller.Detail', {

    override: 'Shopware.apps.Emotion.controller.Detail',

    init: function() {
        var me = this;

        me.callParent(arguments);

        me.control({
            'emotion-detail-window emotion-detail-layout': {
                'changeSections': me.onChangeSections,
            },
        });
    },

    /**
     * @override
     *
     * @param { Shopware.apps.Emotion.model.Emotion } emotion
     * @param { string } mode
     */
    onModeChange: function(emotion, mode) {
        var me = this,
            listingForm = me.getSettingsForm(),
            method = 'show';

        me.callParent(arguments);

        if (mode === 'storytelling') {
            listingForm.listingCheckbox.setValue(false);
            method = 'hide';
        }

        listingForm.listingCheckbox[method]();
    },

    /**
     * @param { Shopware.apps.Emotion.model.Emotion } emotionRecord
     * @param { number } sections
     * @param { number } currentSections
     * @param { Ext.form.Field } sectionField
     * @param { Ext.form.Field } modeField
     * @param { string } mode
     */
    onChangeSections: function(emotionRecord, sections, currentSections, sectionField, modeField, mode) {
        var me = this,
            elements = emotionRecord.getElements(),
            affectedElements = [],
            viewports;
    
        sections = sections || 6;

        elements.each(function(element) {
            viewports = element.getViewports();

            viewports.each(function(viewport) {
                var startSection = Math.ceil(viewport.get('startRow') / sections),
                    endSection = Math.ceil(viewport.get('endRow') / sections);

                if (startSection !== endSection) {
                    affectedElements.push(element);
                    return false;
                }
            });
        });

        if (affectedElements.length > 0) {
            Ext.MessageBox.confirm(
                '{s name="settings/mode/confirm/title"}{/s}',
                '{s name="settings/mode/confirm/msg"}{/s}',
                function(response) {
                    if (response !== 'yes') {
                        sectionField.setValue(currentSections);

                        if (modeField && mode) {
                            modeField.setValue(mode);
                        }

                        return;
                    }

                    Ext.each(affectedElements, function(element) {
                        viewports = element.getViewports();

                        viewports.each(function(viewport) {
                            var startSection = Math.ceil(viewport.get('startRow') / sections),
                                endSection = Math.ceil(viewport.get('endRow') / sections);

                            if (startSection !== endSection) {
                                viewport.set({
                                    startRow: 1,
                                    startCol: 1,
                                    endRow: 1,
                                    endCol: 1,
                                    visible: false
                                });
                            }
                        });
                    });

                    me.setSections(emotionRecord, sections);
                }
            );
        } else {
            me.setSections(emotionRecord, sections);
        }
    },

    /**
     * @param { Shopware.apps.Emotion.model.Emotion } emotionRecord
     * @param { number } sections
     */
    setSections: function(emotionRecord, sections) {
        var me = this,
            currentSections = emotionRecord.get('swagRows') || 6,
            rowChange = sections - currentSections,
            rows = emotionRecord.get('rows'),
            sectionCount = Math.round(rows / currentSections),
            grid = me.getDesignerGrid();

        if (rowChange > 0) {
            emotionRecord.set('rows', rows + (sectionCount * rowChange));
        }

        emotionRecord.set('swagRows', sections);
        grid.refresh();
    }
});
// {/block}
