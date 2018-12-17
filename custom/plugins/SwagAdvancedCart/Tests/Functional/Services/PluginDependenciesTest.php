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

namespace SwagAdvancedCart\Tests\Functional\Services;

use SwagAdvancedCart\Services\Dependencies\PluginDependencies;
use SwagAdvancedCart\Tests\CustomerLoginTrait;
use SwagAdvancedCart\Tests\KernelTestCaseTrait;
use SwagAdvancedCart\Tests\PluginDependencyTrait;

class PluginDependenciesTest extends \PHPUnit\Framework\TestCase
{
    use KernelTestCaseTrait;
    use CustomerLoginTrait;
    use PluginDependencyTrait;

    public function test_isPluginInstalled_plugin_should_be_installed()
    {
        $this->assertTrue(
            $this->getDependencies()->isPluginInstalled('SwagAdvancedCart')
        );
    }

    public function test_isPluginInstalled_plugin_should_not_be_installed()
    {
        $this->assertFalse(
            $this->getDependencies()->isPluginInstalled('SwagNotInstalled')
        );
    }

    public function test_isBundleArticleInBasket_a_bundle_product_should_be_there()
    {
        // Skipped when Plugin is not installed
        $this->isPluginInstalled('SwagBundle', true);

        $this->addBundleArticleToBasket();

        $basketArray = [13128, 13129, 13130, 13131, 13132, 13134, 13135];
        $result = $this->getDependencies()->isBundleArticleInBasket($basketArray);

        $this->assertTrue($result);
    }

    public function test_isBundleArticleInBasket_there_should_be_no_bundle_product()
    {
        // Skipped when Plugin is not installed
        $this->isPluginInstalled('SwagBundle');

        $this->addBundleArticleToBasket();
        $this->setBundleArticleToNoBundle();

        $basketArray = [13128, 13129, 13130, 13131, 13132, 13134, 13135];
        $result = $this->getDependencies()->isBundleArticleInBasket($basketArray);

        $this->assertFalse($result);
    }

    private function getContainer()
    {
        return self::getKernel()->getContainer();
    }

    private function getConnection()
    {
        return $this->getContainer()->get('dbal_connection');
    }

    private function getDependencies()
    {
        return new PluginDependencies($this->getConnection());
    }

    private function addBundleArticleToBasket()
    {
        $sql = file_get_contents(__DIR__ . '/Fixtures/createOriginalBundleBasket.sql');
        $this->getConnection()->exec($sql);
    }

    private function setBundleArticleToNoBundle()
    {
        $this->getConnection()->exec('UPDATE s_order_basket_attributes SET bundle_id = NULL WHERE bundle_id = 1;');
    }
}
