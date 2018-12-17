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

namespace SwagPromotion\Tests\Functional\Subscriber;

use SwagPromotion\Subscriber\VoucherSubscriber;
use SwagPromotion\Tests\DatabaseTestCaseTrait;

class VoucherSubscriberTest extends \PHPUnit\Framework\TestCase
{
    use DatabaseTestCaseTrait;

    public function test_getSubscribedEvents()
    {
        $result = VoucherSubscriber::getSubscribedEvents();

        $this->assertTrue(is_array($result));
        $this->assertCount(1, $result);
    }

    public function test_onAddVoucher_empty_code()
    {
        $eventArgs = new \Enlight_Event_EventArgs(['code' => '']);
        $subscriber = $this->getSubscriber();

        $result = $subscriber->onAddVoucher($eventArgs);

        $this->assertNull($result);
    }

    public function test_onAddVoucher_no_promotion()
    {
        $eventArgs = new \Enlight_Event_EventArgs(['code' => '23A7BCA4']);
        $subscriber = $this->getSubscriber();

        $result = $subscriber->onAddVoucher($eventArgs);

        $this->assertNull($result);
    }

    public function test_onAddVoucher()
    {
        $this->execSql(file_get_contents(__DIR__ . '/Fixtures/promotionWithVoucher.sql'));

        $eventArgs = new \Enlight_Event_EventArgs(['code' => 'absolut']);
        $subscriber = $this->getSubscriber();

        $result = $subscriber->onAddVoucher($eventArgs);

        $this->assertTrue($result);
    }

    private function getSubscriber()
    {
        return new VoucherSubscriber(
            Shopware()->Container()->getParameter('swag_promotion.plugin_dir'),
            Shopware()->Container()->get('template'),
            Shopware()->Container()->get('session'),
            Shopware()->Container()->get('swag_promotion.repository'),
            Shopware()->Container()->get('shopware_storefront.context_service'),
            Shopware()->Container()->get('dbal_connection')
        );
    }
}
