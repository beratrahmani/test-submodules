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
    'module' => 'backend',
    'controller' => 'LiveShopping',
    'action' => 'createLiveShopping',
    'name' => 'My Liveshopping',
    'type' => 1,
    'articleId' => 272,
    'active' => true,
    'number' => '08154711',
    'limited' => true,
    'quantity' => 0,
    'purchase' => 0,
    'validFrom' => '01.05.2017',
    'validTo' => '31.05.2100',
    'validFromTime' => '00:00',
    'validToTime' => '00:00',
    'sells' => 0,
    'frontpageDisplay' => false,
    'categoriesDisplay' => false,
    'customerGroups' => [
            [
                'id' => 1,
                'key' => 'EK',
                'name' => 'Shopkunden',
                'tax' => true,
                'taxInput' => true,
                'mode' => false,
                'discount' => 0,
            ],
        ],
    'shops' => [
            [
                'id' => 1,
                'default' => true,
                'localeId' => 0,
                'categoryId' => 3,
                'name' => 'Deutsch',
            ],
        ],
    'prices' => [
            [
                'id' => null,
                'from' => 0,
                'to' => '',
                'price' => 18.99,
                'pseudoPrice' => 0,
                'percent' => 0,
                'cloned' => false,
                'customerGroupKey' => 'EK',
                'endPrice' => 18.98999999999963,
                'customerGroup' => [
                        [
                            'id' => 1,
                            'key' => 'EK',
                            'name' => 'Shopkunden',
                            'tax' => true,
                            'taxInput' => true,
                            'mode' => false,
                            'discount' => 0,
                        ],
                    ],
            ],
        ],
    'limitedVariants' => [],
];
