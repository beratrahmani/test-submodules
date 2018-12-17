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

namespace SwagAboCommerce\Tests\Functional\Subscriber;

use PHPUnit\Framework\TestCase;
use SwagAboCommerce\Subscriber\CustomProducts;
use SwagAboCommerce\Tests\Functional\KernelTestCaseTrait;
use SwagAboCommerce\Tests\Functional\Mocks\AboCommerceServiceMock;
use SwagAboCommerce\Tests\Functional\SubscriberTestTrait;

class CustomProductsTest extends TestCase
{
    use KernelTestCaseTrait;
    use SubscriberTestTrait;

    public function test_getSubscribedEvents()
    {
        $events = CustomProducts::getSubscribedEvents();
        $this->assertSame(
            'onSwagCustomProducts',
            $events['Enlight_Controller_Action_PostDispatchSecure_Widgets_SwagCustomProducts']
        );
    }

    public function test_onSwagCustomProducts_wrong_action()
    {
        $subscriber = $this->getSubscriber();
        $this->getControllerEventArgs()->getRequest()->setActionName('fooBar');

        $eventArgs = $this->getControllerEventArgs();
        $result = $subscriber->onSwagCustomProducts($eventArgs);

        $this->assertNull($result);
    }

    public function test_onSwagCustomProducts_duration_not_set()
    {
        $subscriber = $this->getSubscriber();
        $this->getControllerEventArgs()->getRequest()->setActionName('overviewCalculation');

        $eventArgs = $this->getControllerEventArgs();
        $result = $subscriber->onSwagCustomProducts($eventArgs);

        $this->assertNull($result);
    }

    public function test_onSwagCustomProducts_invalid_product_number()
    {
        $subscriber = $this->getSubscriber();
        $this->getControllerEventArgs()->getRequest()->setActionName('overviewCalculation');
        $this->getControllerEventArgs()->getRequest()->setParam('swagAboCommerceDuration', 2);
        $this->getControllerEventArgs()->getRequest()->setParam('number', 'SW12345678');

        $eventArgs = $this->getControllerEventArgs();
        $result = $subscriber->onSwagCustomProducts($eventArgs);

        $this->assertNull($result);
    }

    public function test_onSwagCustomProducts_no_aboCommerceData_for_product_number()
    {
        $subscriber = $this->getSubscriber();
        $this->getControllerEventArgs()->getRequest()->setActionName('overviewCalculation');
        $this->getControllerEventArgs()->getRequest()->setParam('swagAboCommerceDuration', 2);
        $this->getControllerEventArgs()->getRequest()->setParam('number', 'SW10003');

        $eventArgs = $this->getControllerEventArgs();
        $result = $subscriber->onSwagCustomProducts($eventArgs);

        $this->assertNull($result);
    }

    public function test_onSwagCustomProducts_aboCommerceData_no_price_for_duration()
    {
        $subscriber = $this->getSubscriber($this->getAboCommerceData());
        $this->getControllerEventArgs()->getRequest()->setActionName('overviewCalculation');
        $this->getControllerEventArgs()->getRequest()->setParam('swagAboCommerceDuration', -2);
        $this->getControllerEventArgs()->getRequest()->setParam('number', 'SW10003');

        $eventArgs = $this->getControllerEventArgs();
        $result = $subscriber->onSwagCustomProducts($eventArgs);

        $this->assertNull($result);
    }

    public function test_onSwagCustomProducts_aboCommerceData_no_absolut_discount_for_duration()
    {
        $subscriber = $this->getSubscriber($this->getAboCommerceData());
        $this->getControllerEventArgs()->getRequest()->setActionName('overviewCalculation');
        $this->getControllerEventArgs()->getRequest()->setParam('swagAboCommerceDuration', 12);
        $this->getControllerEventArgs()->getRequest()->setParam('number', 'SW10003');

        $eventArgs = $this->getControllerEventArgs();
        $result = $subscriber->onSwagCustomProducts($eventArgs);

        $this->assertNull($result);
    }

    public function test_onSwagCustomProducts_consider_aboCommerceData()
    {
        $subscriber = $this->getSubscriber($this->getAboCommerceData());
        $this->getControllerEventArgs()->getRequest()->setActionName('overviewCalculation');
        $this->getControllerEventArgs()->getRequest()->setParam('swagAboCommerceDuration', 2);
        $this->getControllerEventArgs()->getRequest()->setParam('number', 'SW10003');
        $this->getControllerEventArgs()->getRequest()->setParam('sQuantity', 2);

        $this->getControllerEventArgs()->getSubject()->View()->assign('data', [
            'basePrice' => 20,
            'totalUnitPrice' => 25,
            'total' => 50,
        ]);

        $eventArgs = $this->getControllerEventArgs();
        $subscriber->onSwagCustomProducts($eventArgs);

        $viewData = $this->getControllerEventArgs()->getSubject()->View()->getAssign();

        $this->assertSame(18, $viewData['data']['basePrice']);
        $this->assertSame(23, $viewData['data']['totalUnitPrice']);
        $this->assertSame(46, $viewData['data']['total']);
    }

    /**
     * @param array $aboCommerceData
     *
     * @throws \Exception
     *
     * @return CustomProducts
     */
    private function getSubscriber(array $aboCommerceData = [])
    {
        return new CustomProducts(
            Shopware()->Container()->get('models'),
            new AboCommerceServiceMock($aboCommerceData)
        );
    }

    /**
     * @return array
     */
    private function getAboCommerceData()
    {
        return [
            'prices' => [
                [
                    'duration' => 1,
                    'discountPrice' => 18.0,
                    'discountAbsolute' => 2.0,
                    'discountPercentage' => 10.0,
                    'fromQuantity' => 1,
                    'toQuantity' => 'beliebig',
                ],
                [
                    'duration' => 11,
                    'discountPrice' => 20.0,
                    'discountAbsolute' => 0,
                    'discountPercentage' => 0,
                    'fromQuantity' => 1,
                    'toQuantity' => 'beliebig',
                ],
            ],
        ];
    }
}
