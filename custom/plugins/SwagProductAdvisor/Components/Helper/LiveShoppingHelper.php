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

namespace SwagProductAdvisor\Components\Helper;

use Shopware\Components\Plugin\ConfigReader;
use Shopware\Models\Shop\Shop;
use SwagLiveShopping\Components\LiveShoppingInterface;
use SwagLiveShopping\Models\LiveShopping as LiveShoppingModel;
use SwagProductAdvisor\Components\DependencyProvider\DependencyProviderInterface;

class LiveShoppingHelper implements LiveShoppingHelperInterface
{
    /**
     * @var LiveShoppingInterface
     */
    private $liveShoppingComponent;

    /**
     * @var \Shopware_Components_Modules
     */
    private $modules;

    /**
     * @var ConfigReader
     */
    private $configReader;

    /**
     * @var Shop
     */
    private $shop;

    /**
     * @param DependencyProviderInterface $dependencyProvider
     * @param ConfigReader                $configReader
     */
    public function __construct(
        DependencyProviderInterface $dependencyProvider,
        ConfigReader $configReader
    ) {
        $this->liveShoppingComponent = $dependencyProvider->getLiveShopping();
        $this->modules = $dependencyProvider->getModules();
        $this->configReader = $configReader;
        $this->shop = $dependencyProvider->getShop();
    }

    /**
     * {@inheritdoc}
     */
    public function checkForLiveShopping(array $products)
    {
        if ($this->liveShoppingComponent === null) {
            return $products;
        }

        $liveShoppingConfig = $this->configReader->getByPluginName('SwagLiveShopping', $this->shop);

        foreach ($products as &$product) {
            $productId = $product['articleID'];

            $liveShopping = $this->liveShoppingComponent->getActiveLiveShoppingForProduct($productId);

            if (!$liveShopping instanceof LiveShoppingModel) {
                continue;
            }

            $product['liveShopping'] = $this->liveShoppingComponent->getLiveShoppingArrayData($liveShopping);
            $product['price'] = $this->modules->Articles()->sFormatPrice($liveShopping->getCurrentPrice());
            $product['liveShopping']['showDescription'] = $liveShoppingConfig['showDescriptionInTheListing'];
        }

        return $products;
    }
}
