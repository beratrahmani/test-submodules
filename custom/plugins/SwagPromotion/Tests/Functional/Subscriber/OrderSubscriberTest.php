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
use SwagPromotion\Subscriber\OrderSubscriber;
use SwagPromotion\Tests\DatabaseTestCaseTrait;
use SwagPromotion\Tests\PromotionTestCase;

class OrderSubscriberTest extends PromotionTestCase
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

    public function test_afterSaveOrder()
    {
        $sql = file_get_contents(__DIR__ . '/Fixtures/promotion1.sql');
        $this->container->get('dbal_connection')->exec($sql);
        $sql = file_get_contents(__DIR__ . '/Fixtures/order1.sql');
        $this->container->get('dbal_connection')->exec($sql);

        $arguments = new OrderSubscriberTestHookArgsMock($this, []);
        $arguments->return = '20003005';

        $session = Shopware()->Session();
        $session->offsetSet('appliedPromotions', [1]);
        $session->offsetSet('sUserId', 1);

        $subscriber = new OrderSubscriber(
            $this->container->get('session'),
            $this->container->get('dbal_connection')
        );
        $subscriber->afterSaveOrder($arguments);

        $sql = 'SELECT * FROM s_plugin_promotion_customer_count';
        $result = array_shift($this->container->get('dbal_connection')->fetchAll($sql));

        $expectedSubset = [
            'promotion_id' => '1',
            'customer_id' => '1',
            'order_id' => '590000',
        ];

        $this->assertArraySubset($expectedSubset, $result);
    }
}

class OrderSubscriberTestHookArgsMock extends \Enlight_Hook_HookArgs
{
    /**
     * @var mixed
     */
    public $return;

    /**
     * @return mixed
     */
    public function getReturn()
    {
        return $this->return;
    }
}
