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
use SwagPromotion\Components\Promotion\CurrencyConverter;

/**
 * @small
 */
class CurrencyConverterTest extends TestCase
{
    protected static $ensureLoadedPlugins = [
        'SwagPromotion' => [],
    ];

    public function testUSDConversion()
    {
        $factor = Shopware()->Db()->fetchOne('SELECT factor FROM s_core_currencies WHERE id = 2');
        $converter = new CurrencyConverter(Shopware()->Container()->get('shopware_storefront.context_service'));

        $this->setFactor($factor, $converter);

        $amount = 17;
        $this->assertEquals($amount * $factor, $converter->convert($amount), '', 0.01);
    }

    public function testEURConversion()
    {
        $factor = Shopware()->Db()->fetchOne('SELECT factor FROM s_core_currencies WHERE id = 1');
        $converter = new CurrencyConverter(Shopware()->Container()->get('shopware_storefront.context_service'));

        $this->setFactor($factor, $converter);

        $amount = 17;
        $this->assertEquals(17, $converter->convert($amount), '', 0.01);
    }

    public function testFactor()
    {
        $converter = new CurrencyConverter(Shopware()->Container()->get('shopware_storefront.context_service'));

        $this->setFactor(44, $converter);

        $this->assertEquals(44, $converter->getFactor());
    }

    /**
     * @param float             $factor
     * @param CurrencyConverter $converter
     */
    private function setFactor($factor, CurrencyConverter $converter)
    {
        $reflectionClass = new \ReflectionClass(CurrencyConverter::class);
        $property = $reflectionClass->getProperty('factor');
        $property->setAccessible(true);
        $property->setValue($converter, $factor);
    }
}
