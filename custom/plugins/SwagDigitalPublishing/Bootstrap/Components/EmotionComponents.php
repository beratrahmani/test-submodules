<?php
/**
 * Shopware Premium Plugins
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this plugin can be used under
 * a proprietary license as set forth in our Terms and Conditions,
 * section 2.1.2.2 (Conditions of Usage).
 *
 * The text of our proprietary license additionally can be found at and
 * in the LICENSE file you have received along with this plugin.
 *
 * This plugin is distributed in the hope that it will be useful,
 * with LIMITED WARRANTY AND LIABILITY as set forth in our
 * Terms and Conditions, sections 9 (Warranty) and 10 (Liability).
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the plugin does not imply a trademark license.
 * Therefore any rights, title and interest in our trademarks
 * remain entirely with us.
 */

namespace SwagDigitalPublishing\Bootstrap\Components;

use Shopware\Components\Emotion\ComponentInstaller;

class EmotionComponents
{
    /**
     * @var ComponentInstaller
     */
    private $emotionComponentInstaller;

    /**
     * @var string
     */
    private $pluginName;

    /**
     * @param string             $pluginName
     * @param ComponentInstaller $emotionComponentInstaller
     */
    public function __construct($pluginName, ComponentInstaller $emotionComponentInstaller)
    {
        $this->emotionComponentInstaller = $emotionComponentInstaller;
        $this->pluginName = $pluginName;
    }

    /**
     * installs the Basic und slider emotion component of DigitalPublishing
     */
    public function install()
    {
        $this->createBasicEmotionWidget();
        $this->createSliderEmotionWidget();
    }

    /**
     * Creates the basic emotion widget.
     */
    private function createBasicEmotionWidget()
    {
        $component = $this->emotionComponentInstaller->createOrUpdate(
            $this->pluginName,
            'Digital Publishing',
            [
                'name' => 'Digital Publishing',
                'xtype' => 'emotion-digital-publishing',
                'template' => 'component_digital_publishing',
                'cls' => 'emotion--digital-publishing',
                'description' => '',
            ]
        );

        $component->createHiddenField(
            [
                'name' => 'digital_publishing_banner_id',
                'defaultValue' => '',
                'allowBlank' => false,
            ]
        );

        $component->createHiddenField(
            [
                'name' => 'digital_publishing_banner_data',
                'defaultValue' => '',
                'allowBlank' => true,
                'valueType' => 'json',
            ]
        );
    }

    /**
     * Creates the slider emotion widget.
     */
    private function createSliderEmotionWidget()
    {
        $component = $this->emotionComponentInstaller->createOrUpdate(
            $this->pluginName,
            'Digital Publishing Slider',
            [
                'name' => 'Digital Publishing Slider',
                'xtype' => 'emotion-digital-publishing-slider',
                'template' => 'component_digital_publishing_slider',
                'cls' => 'emotion--digital-publishing-slider',
                'description' => '',
            ]
        );

        $component->createHiddenField(
            [
                'name' => 'digital_publishing_slider_payload',
                'defaultValue' => '',
                'allowBlank' => true,
            ]
        );

        $component->createHiddenField(
            [
                'name' => 'digital_publishing_slider_preview_data',
                'defaultValue' => '',
                'allowBlank' => true,
                'valueType' => 'json',
            ]
        );

        $component->createCheckboxField(
            [
                'name' => 'show_arrows',
                'fieldLabel' => 'Pfeile anzeigen',
                'defaultValue' => false,
                'allowBlank' => true,
            ]
        );

        $component->createCheckboxField(
            [
                'name' => 'show_navigation',
                'fieldLabel' => 'Navigation anzeigen',
                'defaultValue' => false,
                'allowBlank' => true,
            ]
        );

        $component->createCheckboxField(
            [
                'name' => 'auto_slide',
                'fieldLabel' => 'Automatisch rotieren',
                'defaultValue' => false,
                'allowBlank' => true,
            ]
        );

        $component->createNumberField(
            [
                'name' => 'slide_interval',
                'fieldLabel' => 'Rotations-Geschwindigkeit',
                'defaultValue' => 5000,
                'allowBlank' => false,
            ]
        );

        $component->createNumberField(
            [
                'name' => 'animation_speed',
                'fieldLabel' => 'Animations-Geschwindigkeit',
                'defaultValue' => 500,
                'allowBlank' => false,
            ]
        );
    }
}
