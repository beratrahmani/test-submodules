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

namespace SwagBundle\Tests\Functional\Subscriber;

use PHPUnit\Framework\TestCase;
use SwagBundle\Subscriber\Voucher;
use SwagBundle\Tests\DatabaseTestCaseTrait;
use SwagBundle\Tests\Functional\Mocks\ConfigReaderMock;
use SwagBundle\Tests\Functional\Mocks\DependenciesProviderMock;

class VoucherTest extends TestCase
{
    use DatabaseTestCaseTrait;

    public function test_construct()
    {
        $subscriber = $this->getSubscriber();

        $this->assertInstanceOf(Voucher::class, $subscriber);
    }

    public function test_getSubscribedEvents()
    {
        $result = Voucher::getSubscribedEvents();
        $this->assertArrayHasKey('sBasket::sGetAmountRestrictedArticles::after', $result);
        $this->assertArrayHasKey('sBasket::sGetAmountArticles::after', $result);
        $this->assertArrayHasKey('sBasket::sAddVoucher::before', $result);

        $this->assertCount(3, $result);
    }

    public function test_onGetAmountRestrictedProducts_return_no_session()
    {
        $subscriber = $this->getSubscriber(true);
        $hookArgs = $this->getHookArgs('sGetAmountRestrictedArticles');

        $result = $subscriber->onGetAmountRestrictedProducts($hookArgs);

        $this->assertEquals(100, $result['totalAmount']);
    }

    public function test_onGetAmountRestrictedProducts_do_not_change_amount()
    {
        $subscriber = $this->getSubscriber();
        $hookArgs = $this->getHookArgs('sGetAmountRestrictedArticles');

        $result = $subscriber->onGetAmountRestrictedProducts($hookArgs);

        $this->assertEquals(100, $result['totalAmount']);
    }

    public function test_onGetAmountRestrictedProducts_exclude_from_percental_voucher()
    {
        $dbalConnection = Shopware()->Container()->get('dbal_connection');
        $sessionId = 'mySession';
        $dbalConnection->executeQuery(
            file_get_contents(__DIR__ . '/_fixtures/bundle_in_basket.sql'),
            [':bundleId' => 111, ':sessionId' => $sessionId, ':basketId' => 222]
        );
        Shopware()->Front()->Request()->setParam('sVoucher', 'prozentual');
        Shopware()->Session()->offsetSet('sessionId', $sessionId);

        $subscriber = $this->getSubscriber(false, true);
        $hookArgs = $this->getHookArgs('sGetAmountRestrictedArticles');

        $result = $subscriber->onGetAmountRestrictedProducts($hookArgs);

        $this->assertEquals(80, $result['totalAmount']);

        Shopware()->Front()->Request()->clearParams();
        Shopware()->Session()->offsetUnset('sessionId');
    }

    public function test_onGetAmountRestrictedProducts_consider_bundle_discount()
    {
        $dbalConnection = Shopware()->Container()->get('dbal_connection');
        $sessionId = 'mySession';
        $dbalConnection->executeQuery(
            file_get_contents(__DIR__ . '/_fixtures/bundle_in_basket.sql'),
            [':bundleId' => 111, ':sessionId' => $sessionId, ':basketId' => 222]
        );
        Shopware()->Session()->offsetSet('sessionId', $sessionId);

        $subscriber = $this->getSubscriber(false, false, true);
        $hookArgs = $this->getHookArgs('sGetAmountRestrictedArticles');

        $result = $subscriber->onGetAmountRestrictedProducts($hookArgs);

        $this->assertEquals(99.5, $result['totalAmount']);

        Shopware()->Session()->offsetUnset('sessionId');
    }

    public function test_onGetAmountProducts_return_no_session()
    {
        $subscriber = $this->getSubscriber(true);
        $hookArgs = $this->getHookArgs('sGetAmountArticles');

        $result = $subscriber->onGetAmountProducts($hookArgs);

        $this->assertEquals(100, $result['totalAmount']);
    }

    public function test_onGetAmountProducts_do_not_change_amount()
    {
        $subscriber = $this->getSubscriber();
        $hookArgs = $this->getHookArgs('sGetAmountArticles');

        $result = $subscriber->onGetAmountProducts($hookArgs);

        $this->assertEquals(100, $result['totalAmount']);
    }

    public function test_onGetAmountProducts_exclude_from_percental_voucher()
    {
        $dbalConnection = Shopware()->Container()->get('dbal_connection');
        $sessionId = 'mySession';
        $dbalConnection->executeQuery(
            file_get_contents(__DIR__ . '/_fixtures/bundle_in_basket.sql'),
            [':bundleId' => 111, ':sessionId' => $sessionId, ':basketId' => 222]
        );
        Shopware()->Front()->Request()->setParam('sVoucher', 'prozentual');
        Shopware()->Session()->offsetSet('sessionId', $sessionId);

        $subscriber = $this->getSubscriber(false, true);
        $hookArgs = $this->getHookArgs('sGetAmountArticles');

        $result = $subscriber->onGetAmountProducts($hookArgs);

        $this->assertEquals(60, $result['totalAmount']);

        Shopware()->Front()->Request()->clearParams();
        Shopware()->Session()->offsetUnset('sessionId');
    }

    public function test_onGetAmountProducts_consider_bundle_discount()
    {
        $dbalConnection = Shopware()->Container()->get('dbal_connection');
        $sessionId = 'mySession';
        $dbalConnection->executeQuery(
            file_get_contents(__DIR__ . '/_fixtures/bundle_in_basket.sql'),
            [':bundleId' => 111, ':sessionId' => $sessionId, ':basketId' => 222]
        );
        Shopware()->Session()->offsetSet('sessionId', $sessionId);

        $subscriber = $this->getSubscriber(false, false, true);
        $hookArgs = $this->getHookArgs('sGetAmountArticles');

        $result = $subscriber->onGetAmountProducts($hookArgs);

        $this->assertEquals(99.5, $result['totalAmount']);

        Shopware()->Session()->offsetUnset('sessionId');
    }

    public function test_onGetAmountProducts_exclude_from_percental_voucher_checkMinOrder_no_shop()
    {
        $dbalConnection = Shopware()->Container()->get('dbal_connection');
        $sessionId = 'mySession';
        $dbalConnection->executeQuery(
            file_get_contents(__DIR__ . '/_fixtures/bundle_in_basket.sql'),
            [':bundleId' => 111, ':sessionId' => $sessionId, ':basketId' => 222]
        );
        Shopware()->Front()->Request()->setParam('sVoucher', 'prozentual');
        Shopware()->Session()->offsetSet('sessionId', $sessionId);

        $subscriber = $this->getSubscriber(false, true, false, true);
        $hookArgs = $this->getHookArgs('sGetAmountArticles');

        $result = $subscriber->onGetAmountProducts($hookArgs);

        $this->assertEquals(60, $result['totalAmount']);
        $voucherValidation = Shopware()->Template()->getTemplateVars('sVoucherValidation');
        $this->assertNull($voucherValidation);

        Shopware()->Front()->Request()->clearParams();
        Shopware()->Session()->offsetUnset('sessionId');
    }

    public function test_onGetAmountProducts_exclude_from_percental_voucher_checkMinOrder_no_voucherDetails()
    {
        $dbalConnection = Shopware()->Container()->get('dbal_connection');
        $sessionId = 'mySession';
        $dbalConnection->executeQuery(
            file_get_contents(__DIR__ . '/_fixtures/bundle_in_basket.sql'),
            [':bundleId' => 111, ':sessionId' => $sessionId, ':basketId' => 222]
        );
        Shopware()->Session()->offsetSet('sessionId', $sessionId);

        $subscriber = $this->getSubscriber(false, true);
        $hookArgs = $this->getHookArgs('sGetAmountArticles');

        $result = $subscriber->onGetAmountProducts($hookArgs);

        $this->assertEquals(60, $result['totalAmount']);
        $voucherValidation = Shopware()->Template()->getTemplateVars('sVoucherValidation');
        $this->assertNull($voucherValidation);

        Shopware()->Front()->Request()->clearParams();
        Shopware()->Session()->offsetUnset('sessionId');
    }

    public function test_onGetAmountProducts_exclude_from_percental_voucher_checkMinOrder_change_factor()
    {
        $dbalConnection = Shopware()->Container()->get('dbal_connection');
        $sessionId = 'mySession';
        $dbalConnection->executeQuery(
            file_get_contents(__DIR__ . '/_fixtures/bundle_in_basket.sql'),
            [':bundleId' => 111, ':sessionId' => $sessionId, ':basketId' => 222]
        );
        Shopware()->Front()->Request()->setParam('sVoucher', 'absolut');
        Shopware()->Session()->offsetSet('sessionId', $sessionId);

        $subscriber = $this->getSubscriber(false, true);
        $hookArgs = $this->getHookArgs('sGetAmountArticles');

        $result = $subscriber->onGetAmountProducts($hookArgs);

        $this->assertEquals(60, $result['totalAmount']);
        $voucherValidation = Shopware()->Template()->getTemplateVars('sVoucherValidation');
        $this->assertNull($voucherValidation);

        Shopware()->Front()->Request()->clearParams();
        Shopware()->Session()->offsetUnset('sessionId');
    }

    public function test_onGetAmountProducts_exclude_from_percental_voucher_checkMinOrder_return_true()
    {
        $dbalConnection = Shopware()->Container()->get('dbal_connection');
        $sessionId = 'mySession';
        $dbalConnection->executeQuery(
            file_get_contents(__DIR__ . '/_fixtures/bundle_in_basket.sql'),
            [':bundleId' => 111, ':sessionId' => $sessionId, ':basketId' => 222]
        );
        Shopware()->Front()->Request()->setParam('sVoucher', 'prozentual');
        Shopware()->Session()->offsetSet('sessionId', $sessionId);

        $subscriber = $this->getSubscriber(false, true);
        $hookArgs = $this->getHookArgs('sGetAmountArticles', 10);

        $result = $subscriber->onGetAmountProducts($hookArgs);

        $this->assertEquals(-30, $result['totalAmount']);
        $voucherValidation = Shopware()->Template()->getTemplateVars('sVoucherValidation');
        $this->assertTrue($voucherValidation);

        Shopware()->Front()->Request()->clearParams();
        Shopware()->Session()->offsetUnset('sessionId');
    }

    public function test_afterAddVoucher()
    {
        $subscriber = $this->getSubscriber();
        $hookArgs = $this->getHookArgs('sAddVoucher');

        $subscriber->afterAddVoucher($hookArgs);
        $voucherCode = Shopware()->Front()->Request()->getParam('sVoucher');

        $this->assertEquals('prozentual', $voucherCode);
    }

    /**
     * @param bool $noSession
     * @param bool $excludeVoucher
     * @param bool $subtractBundleDiscount
     * @param bool $noShop
     *
     * @return Voucher
     */
    private function getSubscriber(
        $noSession = false,
        $excludeVoucher = false,
        $subtractBundleDiscount = false,
        $noShop = false
    ) {
        $dependenciesProvider = new DependenciesProviderMock(
            Shopware()->Container(),
            $noSession,
            $noShop
        );

        $configReader = new ConfigReaderMock($excludeVoucher, $subtractBundleDiscount);

        return new Voucher(
            Shopware()->Container()->get('swag_bundle.voucher_service'),
            $configReader,
            'SwagBundle',
            $dependenciesProvider,
            Shopware()->Container()->get('dbal_connection'),
            Shopware()->Container()->get('template'),
            Shopware()->Container()->get('front')
        );
    }

    /**
     * @param int    $totalAmount
     * @param string $method
     *
     * @return \Enlight_Hook_HookArgs
     */
    private function getHookArgs($method, $totalAmount = 100)
    {
        $hookArgs = new \Enlight_Hook_HookArgs(
            new \stdClass(),
            $method,
            [
                'articles' => ['SW10239'],
                'supplier' => 4,
                'voucherCode' => 'prozentual',
            ]
        );
        $hookArgs->setReturn([
            'totalAmount' => $totalAmount,
        ]);

        return $hookArgs;
    }
}
