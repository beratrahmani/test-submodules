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

namespace SwagBundle\Tests\Unit\Bundle\SearchBundleDBAL;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use PHPUnit\Framework\TestCase;
use SwagBundle\Bundle\SearchBundleDBAL\BundleJoinHelper;

class BundleJoinHelperTest extends TestCase
{
    public function test_addActiveCondition_has_sql_hack()
    {
        $connectionMock = $this->getMockBuilder(Connection::class)->disableOriginalConstructor()->getMock();
        $queryBuilderMock = new QueryBuilderMock($connectionMock);

        $joinHelper = new BundleJoinHelper($connectionMock);

        $reflectionClass = new \ReflectionClass(BundleJoinHelper::class);
        $method = $reflectionClass->getMethod('addActiveCondition');
        $method->setAccessible(true);

        $method->invoke($joinHelper, $queryBuilderMock);

        /*
         * The following query part is necessary for correct displaying the bundle filter facet.
         *
         * Explanation:
         *
         * laststock (boolean / 0 or 1)
         * instock (integer / 0 -> maxInt)
         * instock (minpurchase / 0 -> maxInt)
         *
         * 0 x 10 = 0
         * 1 x 10 = 10
         *
         * if (laststock = 1 and instock >= minpurchase) the filter should be shown / else not.
         * if (laststock = 0) the filter should be shown because we can order new stock content
         */
        $expectedValue = '(productVariants.laststock * productVariants.instock >= productVariants.laststock * productVariants.minpurchase)';

        $this->assertContains($expectedValue, $queryBuilderMock->andWheres);
    }
}

class QueryBuilderMock extends QueryBuilder
{
    public $andWheres = '';

    /**
     * @param string $condition
     */
    public function andWhere($condition)
    {
        $this->andWheres .= $condition . PHP_EOL;
    }
}
