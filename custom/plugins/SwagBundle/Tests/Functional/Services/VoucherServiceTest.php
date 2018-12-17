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

namespace SwagBundle\Tests\Functional\Services;

use PHPUnit\Framework\TestCase;
use SwagBundle\Services\VoucherService;
use SwagBundle\Tests\DatabaseTestCaseTrait;

class VoucherServiceTest extends TestCase
{
    use DatabaseTestCaseTrait;

    const GENERAL_PERCENTAL_VOUCHER = 'general_percental';
    const GENERAL_ABSOLUTE_VOUCHER = 'general_absolute';
    const INDIVIDUAL_PERCENTAL_VOUCHER = 'individual_percental';

    public function test_getVoucherDetails()
    {
        $voucherService = $this->getVoucherService();
        Shopware()->Container()->get('dbal_connection')->exec(file_get_contents(__DIR__ . '/_fixtures/general_percental_voucher.sql'));

        Shopware()->Container()->get('front')->Request()->setParam('sVoucher', self::GENERAL_PERCENTAL_VOUCHER);
        $details = $voucherService->getVoucherDetails();

        self::assertNotEmpty($details);
        self::assertEquals('GUTPROZENTESTGENERAL', $details['ordercode']);
        self::assertEquals('1', $details['percental']);
    }

    public function test_getVoucherDetails_no_param_set()
    {
        $voucherService = $this->getVoucherService();
        Shopware()->Container()->get('dbal_connection')->exec(file_get_contents(__DIR__ . '/_fixtures/general_percental_voucher.sql'));
        Shopware()->Container()->get('front')->Request()->setParam('sVoucher', null);
        $details = $voucherService->getVoucherDetails();

        self::assertFalse($details);
    }

    public function test_getVoucherDetails_individual_percental()
    {
        $voucherService = $this->getVoucherService();
        Shopware()->Container()->get('dbal_connection')->exec(file_get_contents(__DIR__ . '/_fixtures/individual_percental_voucher.sql'));
        Shopware()->Container()->get('front')->Request()->setParam('sVoucher', self::INDIVIDUAL_PERCENTAL_VOUCHER);
        $details = $voucherService->getVoucherDetails();

        self::assertNotEmpty($details);
        self::assertEquals('GUTPROZENTESTINDIV', $details['ordercode']);
        self::assertEquals('1', $details['percental']);
    }

    public function test_isPercentalVoucherInBasket_in_basket()
    {
        $voucherService = $this->getVoucherService();

        Shopware()->Container()->get('session')->offsetSet('sessionId', 'c48d74eb0a312f5986d75bcef805d3f3da2b066da370c7cf05174aaa7fc2f76f');
        Shopware()->Container()->get('dbal_connection')->exec(file_get_contents(__DIR__ . '/_fixtures/bundle_with_voucher.sql'));
        $result = $voucherService->isPercentalVoucherInBasket();

        self::assertTrue($result);
    }

    public function test_isPercentalVoucherInBasket_to_be_added()
    {
        $voucherService = $this->getVoucherService();

        Shopware()->Container()->get('session')->offsetSet('sessionId', 'eb0a312f5986d75bcef805d3f3da2b066da370c7cf05174aaa7fc2f76fe');
        Shopware()->Container()->get('dbal_connection')->exec(file_get_contents(__DIR__ . '/_fixtures/general_percental_voucher.sql'));
        Shopware()->Container()->get('dbal_connection')->exec(file_get_contents(__DIR__ . '/_fixtures/bundle_without_voucher.sql'));
        Shopware()->Container()->get('front')->Request()->setParam('sVoucher', self::GENERAL_PERCENTAL_VOUCHER);
        $result = $voucherService->isPercentalVoucherInBasket();

        self::assertTrue($result);
    }

    public function test_isPercentalVoucherInBasket_none()
    {
        $voucherService = $this->getVoucherService();

        Shopware()->Container()->get('session')->offsetSet('sessionId', 'eb0a312f5986d75bcef805d3f3da2b066da370c7cf05174aaa7fc2f7');
        Shopware()->Container()->get('dbal_connection')->exec(file_get_contents(__DIR__ . '/_fixtures/bundle_without_voucher.sql'));
        Shopware()->Container()->get('front')->Request()->setParam('sVoucher', self::GENERAL_PERCENTAL_VOUCHER);
        $result = $voucherService->isPercentalVoucherInBasket();

        self::assertFalse($result);
    }

    public function test_isCodeFromPercentalVoucher_percental()
    {
        $voucherService = $this->getVoucherService();

        Shopware()->Container()->get('dbal_connection')->exec(file_get_contents(__DIR__ . '/_fixtures/general_percental_voucher.sql'));

        $result = $voucherService->isCodeFromPercentalVoucher(self::GENERAL_PERCENTAL_VOUCHER);

        self::assertTrue($result);
    }

    public function test_isCodeFromPercentalVoucher_absolute()
    {
        $voucherService = $this->getVoucherService();

        Shopware()->Container()->get('dbal_connection')->exec(file_get_contents(__DIR__ . '/_fixtures/general_absolute_voucher.sql'));

        $result = $voucherService->isCodeFromPercentalVoucher(self::GENERAL_ABSOLUTE_VOUCHER);

        self::assertFalse($result);
    }

    /**
     * @return VoucherService
     */
    private function getVoucherService()
    {
        return new VoucherService(
            Shopware()->Container()->get('front'),
            Shopware()->Container()->get('dbal_connection'),
            Shopware()->Container()->get('swag_bundle.dependencies.provider')
        );
    }
}
