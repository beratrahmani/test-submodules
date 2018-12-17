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

namespace  SwagPromotion\Tests\Functional\Subscriber;

use Shopware\Components\DependencyInjection\Container;
use SwagPromotion\Subscriber\Checkout;
use SwagPromotion\Tests\DatabaseTestCaseTrait;
use SwagPromotion\Tests\PromotionTestCase;

class CheckoutTest extends PromotionTestCase
{
    use DatabaseTestCaseTrait;

    /**
     * @var Container
     */
    private $container;

    public function setUp()
    {
        parent::setUp();

        $this->container = Shopware()->Container();
    }

    public function test_onUpdateArticle()
    {
        $arguments = new CheckoutTestArgumentMock();
        $arguments->setData('id', '672659');
        $arguments->setData('quantity', 0);

        $sql = file_get_contents(__DIR__ . '/Fixtures/promotion1.sql');
        $this->container->get('dbal_connection')->exec($sql);

        $subscriber = new Checkout(
            $this->container->get('swag_promotion.service.free_goods_service'),
            $this->container->get('swag_promotion.service.dependency_provider'),
            $this->container->get('dbal_connection')
        );
        $subscriber->onUpdateArticle($arguments);

        $sql = 'SELECT swag_is_free_good_by_promotion_id FROM s_order_basket_attributes WHERE basketID = 672659';
        $result = $this->container->get('dbal_connection')->fetchColumn($sql);

        $this->assertEmpty($result);
    }
}

class CheckoutTestArgumentMock extends \Enlight_Event_EventArgs
{
    /**
     * @var array
     */
    public $data;

    public function __construct()
    {
        $this->data = [];
    }

    /**
     * @param string $key
     * @param mixed  $value
     */
    public function setData($key, $value)
    {
        $this->data[$key] = $value;
    }

    /**
     * @param $string
     *
     * @return mixed
     */
    public function get($string)
    {
        return $this->data[$string];
    }
}
