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

namespace SwagLiveShopping\Tests\Unit\Bundle\SearchBundle\Sorting;

use Shopware\Bundle\SearchBundle\Sorting\Sorting;
use SwagLiveShopping\Bundle\SearchBundle\Sorting\LiveShoppingSorting;

class LiveShoppingSortingTest extends \PHPUnit\Framework\TestCase
{
    public function test_get_name()
    {
        $sorting = new LiveShoppingSorting();

        self::assertInstanceOf(Sorting::class, $sorting);
        self::assertSame('live_shopping', $sorting->getName());
    }

    public function test_json_serialize()
    {
        $sorting = new LiveShoppingSorting();

        self::assertSame(['direction' => 'ASC'], $sorting->jsonSerialize());
    }
}
