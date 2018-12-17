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

return [
    'type' => 'price',
    'question' => 'Price Range question',
    'template' => 'range_slider',
    'infoText' => 'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.',
    'numberOfRows' => 2,
    'numberOfColumns' => 2,
    'columnHeight' => 300,
    'needsToBeAnswered' => false,
    'multipleAnswers' => false,
    'expandQuestion' => false,
    'boost' => 1,
    'hideText' => 0,
    'answers' => [
        0 => [
            'order' => 0,
            'key' => 'minPrice',
            'value' => 'Minimaler Preis',
            'answer' => '5',
            'cssClass' => 'minCss',
            'mediaId' => null,
            'rowId' => null,
            'columnId' => null,
            'targetId' => '',
        ],
        1 => [
            'order' => 1,
            'key' => 'maxPrice',
            'value' => 'Maximaler Preis',
            'answer' => '100',
            'cssClass' => '',
            'mediaId' => null,
            'rowId' => null,
            'columnId' => null,
            'targetId' => '',
        ],
    ],
];
