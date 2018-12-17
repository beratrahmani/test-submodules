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

namespace SwagBundle\Bundle\StoreFrontBundle;

use Shopware\Bundle\StoreFrontBundle\Service\ListProductServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\Attribute;
use Shopware\Bundle\StoreFrontBundle\Struct\ListProduct;
use Shopware\Bundle\StoreFrontBundle\Struct\ProductContextInterface;
use SwagBundle\Services\Listing\BundleServiceInterface;

class ListProductServiceDecorator implements ListProductServiceInterface
{
    /**
     * @var ListProductServiceInterface
     */
    private $coreService;

    /**
     * @var BundleServiceInterface
     */
    private $bundleService;

    /**
     * @param ListProductServiceInterface $coreService
     * @param BundleServiceInterface      $bundleService
     */
    public function __construct(ListProductServiceInterface $coreService, BundleServiceInterface $bundleService)
    {
        $this->bundleService = $bundleService;
        $this->coreService = $coreService;
    }

    /**
     * {@inheritdoc}
     */
    public function get($number, ProductContextInterface $context)
    {
        $products = $this->getList([$number], $context);

        return array_shift($products);
    }

    /**
     * {@inheritdoc}
     */
    public function getList(array $numbers, ProductContextInterface $context)
    {
        $products = $this->coreService->getList($numbers, $context);

        $bundles = $this->bundleService->getListOfBundles($products);

        return array_map(
            function (ListProduct $product) use ($bundles) {
                $productId = $product->getId();

                if (isset($bundles[$productId])) {
                    $product->addAttribute('swag_bundle', new Attribute([true]));
                    $product->setAllowBuyInListing(false);
                }

                return $product;
            },
            $products
        );
    }
}
