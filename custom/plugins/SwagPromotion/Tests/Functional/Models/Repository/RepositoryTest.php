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

namespace SwagPromotion\Tests\Functional;

use Shopware\Components\Test\Plugin\TestCase;
use SwagPromotion\Models\PromotionHydrator;
use SwagPromotion\Models\Repository\DummyRepository;
use SwagPromotion\Models\Repository\PromotionRepository;
use SwagPromotion\Struct\Promotion;
use SwagPromotion\Tests\Helper\PromotionFactory;

/**
 * @small
 */
class RepositoryTest extends TestCase
{
    public static function setUpBeforeClass()
    {
        Shopware()->Db()->exec('DELETE FROM s_plugin_promotion WHERE type = "testPromotionOrder"');
        $sql = <<<EOF
INSERT INTO s_plugin_promotion (`name`, `rules`, `type`, `no_vouchers`, `active` , `stop_processing`, `exclusive`, `priority`) VALUES
('prom1', '{"and":{"true1":[]}}', 'testPromotionOrder', 0, 1, 0, 1, -1),
('prom2', '{"and":{"true1":[]}}', 'testPromotionOrder', 0, 1, 0, 1, 0),
('prom3', '{"and":{"true1":[]}}', 'testPromotionOrder', 0, 1, 0, 1, 1),
('prom4', '{"and":{"true1":[]}}', 'testPromotionOrder', 0, 1, 0, 0, -1),
('prom5', '{"and":{"true1":[]}}', 'testPromotionOrder', 0, 1, 0, 0, 0),
('prom6', '{"and":{"true1":[]}}', 'testPromotionOrder', 0, 1, 0, 0, 1)
EOF;

        Shopware()->Db()->exec($sql);

        parent::setUpBeforeClass();
    }

    public static function tearDownAfterClass()
    {
        Shopware()->Db()->exec('DELETE FROM s_plugin_promotion WHERE type = "testPromotionOrder"');
        parent::tearDownAfterClass();
    }

    /**
     * Sorting order is critical for the correct calculation of promotions - so we have a test for it
     */
    public function testRepositoryShouldGetSortingRight()
    {
        $promotions = $this->getRepository()->getActivePromotions(null, null, [333]);
        $promotionNames = array_values(
            array_map(
                function (Promotion $promotion) {
                    return $promotion->name;
                },
                array_filter(
                    $promotions,
                    function (Promotion $promotion) {
                        return $promotion->type == 'testPromotionOrder';
                    }
                )
            )
        );

        $this->assertEquals(['prom3', 'prom2', 'prom1', 'prom6', 'prom5', 'prom4'], $promotionNames, 'promotion order missmatch');
    }

    public function testVoucherFilterWithVoucher()
    {
        $result = $this->callMethod(
            $this->getRepository(),
            'filterPromotions',
            [
                [
                    [
                        'id' => 1,
                        'name' => 'test',
                        'shops' => [1],
                        'max_usage' => 1,
                        'customer_groups' => [2],
                        'voucher_id' => 1,
                    ],
                ],
                null,
                null,
                [1, 3],
            ]
        );

        $this->assertNotEmpty($result);
    }

    public function testVoucherFilter()
    {
        $result = $this->callMethod(
            $this->getRepository(),
            'filterPromotions',
            [
                [
                    [
                        'id' => 1,
                        'name' => 'test',
                        'shops' => [1],
                        'max_usage' => 1,
                        'customer_groups' => [2],
                        'voucher_id' => 1,
                    ],
                ],
                null,
                null,
                [],
            ]
        );

        $this->assertEmpty($result);
    }

    public function testRepositoryShopFilter()
    {
        $result = $this->callMethod(
            $this->getRepository(),
            'filterPromotions',
            [
                [
                    [
                        'id' => 1,
                        'name' => 'test',
                        'shops' => [1],
                        'max_usage' => 1,
                        'customer_groups' => [2],
                    ],
                ],
                null,
                2,
                [],
            ]
        );

        $this->assertEmpty($result);
    }

    public function testRepositoryCustomerGroupFilter()
    {
        $result = $this->callMethod(
            $this->getRepository(),
            'filterPromotions',
            [
                [
                    [
                        'id' => 1,
                        'name' => 'test',
                        'shops' => [1],
                        'max_usage' => 1,
                        'customer_groups' => [2],
                    ],
                ],
                4,
                1,
                [],
            ]
        );

        $this->assertEmpty($result);
    }

    public function testRepositoryAllPass()
    {
        $result = $this->callMethod(
            $this->getRepository(),
            'filterPromotions',
            [
                [
                    [
                        'id' => 1,
                        'name' => 'test',
                        'shops' => [1],
                        'max_usage' => 5,
                        'customer_groups' => [2],
                    ],
                ],
                2,
                1,
                [1 => 4],
            ]
        );

        $this->assertCount(1, $result);
    }

    public function testDummyRepository()
    {
        $promotion = PromotionFactory::create([]);
        $repo = new DummyRepository([]);
        $repo->set([$promotion]);
        $this->assertSame($promotion, $repo->getActivePromotions()[0]);
    }

    /**
     * @param $obj
     * @param $name
     * @param array $args
     *
     * @return mixed
     */
    protected function callMethod($obj, $name, array $args)
    {
        $class = new \ReflectionClass($obj);
        $method = $class->getMethod($name);
        $method->setAccessible(true);

        return $method->invokeArgs($obj, $args);
    }

    private function getRepository()
    {
        return new PromotionRepository(Shopware()->Container()->get('dbal_connection'), new PromotionHydrator());
    }
}
