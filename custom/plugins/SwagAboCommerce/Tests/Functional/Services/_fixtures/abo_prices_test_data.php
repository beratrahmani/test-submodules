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

namespace SwagAboCommerce\Tests\Functional\Services\_fixtures;

$dataToTest = [
    'testCase1' => [
        [
            'discount_absolute' => '12.605042016807',
            'discount_percent' => '0',
        ],
        [
            'discount_absolute' => '0',
            'discount_percent' => '10',
        ],
        [
            'discount_absolute' => '0',
            'discount_percent' => '22.5',
        ],
        [
            'discount_absolute' => '13.865546218487',
            'discount_percent' => '0',
        ],
    ],
    'testCase2' => [
        [
            'discount_absolute' => '0',
            'discount_percent' => '0',
        ],
        [
            'discount_absolute' => '0',
            'discount_percent' => '11.375',
        ],
        [
            'discount_absolute' => 0,
            'discount_percent' => '0',
        ],
    ],
    'testCase3' => [
        [
            'discount_absolute' => '5',
            'discount_percent' => '0',
        ],
    ],
    'testCase4' => [
        [
            'discount_absolute' => '0.0000000015',
            'discount_percent' => '10.98',
        ],
        [
            'discount_absolute' => 0,
            'discount_percent' => 0,
        ],
        [
            'discount_absolute' => '31.75',
            'discount_percent' => 22.5,
        ],
        [
            'discount_absolute' => 13.865546218487,
            'discount_percent' => '88.65',
        ],
    ],
    'testCase5' => [
        [
            'discount_absolute' => 0.303,
            'discount_percent' => .98,
        ],
        [
            'discount_absolute' => 4.40001,
            'discount_percent' => 15.6,
        ],
        [
            'discount_absolute' => 0,
            'discount_percent' => 0.5,
        ],
        [
            'discount_absolute' => 6.55487,
            'discount_percent' => 6.5,
        ],
    ],
];

return $dataToTest;
