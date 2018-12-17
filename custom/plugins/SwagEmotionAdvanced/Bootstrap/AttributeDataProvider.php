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

namespace SwagEmotionAdvanced\Bootstrap;

use Shopware\Bundle\AttributeBundle\Service\TypeMapping;

class AttributeDataProvider
{
    /**
     * attributes for the Emotion Advanced plugin
     *
     * @return array
     */
    public static function getEmotionAdvancedAttributes()
    {
        return [
            [
                'table' => 's_emotion_attributes',
                'column' => 'swag_quickview',
                'type' => TypeMapping::TYPE_BOOLEAN,
                'default' => 0,
            ],
            [
                'table' => 's_emotion_attributes',
                'column' => 'swag_rows',
                'type' => TypeMapping::TYPE_INTEGER,
                'default' => 6,
            ],
        ];
    }
}
