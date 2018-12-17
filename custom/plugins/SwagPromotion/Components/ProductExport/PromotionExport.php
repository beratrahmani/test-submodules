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

namespace SwagPromotion\Components\ProductExport;

use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Service\ListProductServiceInterface;
use SwagPromotion\Components\Listing\PromotionProductHighlighter;
use SwagPromotion\Models\Repository\PromotionRepository;
use SwagPromotion\Models\Repository\Repository;

class PromotionExport implements PromotionExportInterface
{
    /**
     * @var PromotionRepository
     */
    private $promotionRepository;

    /**
     * @var PromotionProductHighlighter
     */
    private $promotionProductHighlighter;

    /**
     * @var ListProductServiceInterface
     */
    private $listProductService;

    /**
     * @var ContextServiceInterface
     */
    private $contextService;

    /**
     * @param Repository                  $promotionRepository
     * @param PromotionProductHighlighter $promotionProductHighlighter
     * @param ListProductServiceInterface $listProductService
     * @param ContextServiceInterface     $contextService
     */
    public function __construct(
        Repository $promotionRepository,
        PromotionProductHighlighter $promotionProductHighlighter,
        ListProductServiceInterface $listProductService,
        ContextServiceInterface $contextService
    ) {
        $this->promotionRepository = $promotionRepository;
        $this->promotionProductHighlighter = $promotionProductHighlighter;
        $this->listProductService = $listProductService;
        $this->contextService = $contextService;
    }

    /**
     * {@inheritdoc}
     */
    public function handleExport(array $products, array $config)
    {
        $shopId = isset($config['languageID']) ? $config['languageID'] : null;
        $customerGroupId = isset($config['customergroupID']) ? $config['customergroupID'] : null;

        if (!$shopId || !$customerGroupId) {
            return $products;
        }

        $activePromotions = $this->promotionRepository->getActivePromotions($customerGroupId, $shopId);

        //Only product rules can be applied. If there are no product rules in the shop the function will return the products
        $activePromotions = array_filter($activePromotions, function ($item) {
            return strpos($item->type, 'product.absolute') === 0 || strpos($item->type, 'product.percentage') === 0 ? $item : null;
        });

        //No active promotion was found
        if (empty($activePromotions)) {
            return $products;
        }

        //Get a completely calculated array of ListProducts
        $shopContext = $this->contextService->getShopContext();
        $listProducts = $this->listProductService->getList(array_column($products, 'ordernumber'), $shopContext);
        $matches = $this->promotionProductHighlighter->getProductPromotions($listProducts, $shopContext);

        //Update the product, if a matching rule was found.
        foreach ($products as &$product) {
            if (isset($matches[$product['ordernumber']])) {
                $listProduct = $listProducts[$product['ordernumber']];
                $price = $listProduct->getCheapestPrice();

                $product['has_promotion'] = true;
                $product['org_price'] = $product['price'];
                $product['price'] = $price->getCalculatedPrice();
                $product['pseudoprice'] = $price->getCalculatedPseudoPrice();
            }
        }
        unset($product);

        return $products;
    }
}
