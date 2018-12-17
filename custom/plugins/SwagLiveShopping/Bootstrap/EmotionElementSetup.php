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

namespace SwagLiveShopping\Bootstrap;

use Shopware\Components\Emotion\ComponentInstaller;
use Shopware\Models\Emotion\Library\Field;

class EmotionElementSetup
{
    /**
     * The default number of products that are being displayed in the slider
     *
     * @var string
     */
    const DEFAULT_NUMBER_OF_PRODUCTS = '10';

    /**
     * @var ComponentInstaller
     */
    private $componentInstaller;

    /**
     * @param ComponentInstaller $componentInstaller
     */
    public function __construct(ComponentInstaller $componentInstaller)
    {
        $this->componentInstaller = $componentInstaller;
    }

    /**
     * @param string $pluginName
     *
     * @throws \Exception
     */
    public function createEmotionWidget($pluginName)
    {
        $component = $this->componentInstaller->createOrUpdate(
                $pluginName,
                'Live-Shopping-Slider',
                [
                        'name' => 'Live-Shopping-Slider',
                        'xtype' => 'emotion-components-live-shopping-slider',
                        'template' => 'live_shopping_slider',
                        'description' => 'Live Shopping component for Shopping world',
                ]
        );

        $existingFields = $component->getFields()->getValues();

        if (!$this->hasField('description', $existingFields)) {
            $component->createTextField([
                    'name' => 'description',
                    'fieldLabel' => 'Description',
                    'supportText' => 'Enter a description',
                    'defaultValue' => 'Live Shopping',
            ]);
        }

        if (!$this->hasField('number_products', $existingFields)) {
            $component->createNumberField([
                    'name' => 'number_products',
                    'fieldLabel' => 'Number of products',
                    'defaultValue' => self::DEFAULT_NUMBER_OF_PRODUCTS,
            ]);
        }

        if (!$this->hasField('scroll_speed', $existingFields)) {
            $component->createNumberField([
                    'name' => 'scroll_speed',
                    'fieldLabel' => 'Scroll Speed',
                    'defaultValue' => '500',
            ]);
        }

        if (!$this->hasField('rotation_speed', $existingFields)) {
            $component->createNumberField([
                    'name' => 'rotation_speed',
                    'fieldLabel' => 'Rotation Speed',
                    'defaultValue' => '5000',
            ]);
        }

        if (!$this->hasField('rotate_automatically', $existingFields)) {
            $component->createCheckboxField([
                    'name' => 'rotate_automatically',
                    'fieldLabel' => 'Rotate Automatically',
                    'defaultValue' => 'true',
            ]);
        }

        if (!$this->hasField('show_arrows', $existingFields)) {
            $component->createCheckboxField([
                    'name' => 'show_arrows',
                    'fieldLabel' => 'Show Arrows',
                    'defaultValue' => 'true',
            ]);
        }
    }

    /**
     * @param string $name
     * @param array  $fields
     *
     * @return bool
     */
    private function hasField($name, array $fields)
    {
        /** @var Field $field */
        foreach ($fields as $field) {
            if ($field->getName() === $name) {
                return true;
            }
        }

        return false;
    }
}
