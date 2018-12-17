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

namespace SwagBundle\Tests\Functional\TestHelper\Results;

use SwagBundle\Struct\Bundle;

class ResultForGetListOfBundles
{
    /**
     * @return array
     */
    public static function getResult()
    {
        $bundle1 = new Bundle();
        $bundle1->setId(1);
        $bundle1->setName('Test Bundle 01');

        $bundle2 = new Bundle();
        $bundle2->setId(2);
        $bundle2->setName('Test Bundle 02');

        return [
            178 => [
                $bundle1,
            ],
            21 => [
                $bundle1,
                $bundle2,
            ],
            22 => [
                $bundle1,
            ],
            153 => [
                $bundle1,
            ],
            170 => [
                $bundle1,
            ],
            272 => [
                $bundle2,
            ],
            9 => [
                $bundle2,
            ],
            2 => [
                $bundle2,
            ],
        ];
    }
}
