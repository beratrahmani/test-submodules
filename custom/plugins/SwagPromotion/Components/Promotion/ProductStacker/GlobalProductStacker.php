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

namespace SwagPromotion\Components\Promotion\ProductStacker;

use SwagPromotion\Components\Promotion\ProductChunker\CheapestProductChunker;
use SwagPromotion\Components\Promotion\ProductChunker\ProductChunkerRegistry;

/**
 * GlobalProductStacker will stack products globally
 */
class GlobalProductStacker implements ProductStacker
{
    const GLOBAL_PRODUCT_STACKER_NAME = 'global';

    /** @var ProductChunkerRegistry */
    private $chunkerRegistry;

    /**
     * ArticleProductStacker constructor.
     *
     * @param ProductChunkerRegistry $chunkerRegistry
     */
    public function __construct(ProductChunkerRegistry $chunkerRegistry)
    {
        $this->chunkerRegistry = $chunkerRegistry;
    }

    /**
     * {@inheritdoc}
     */
    public function getStack(
        array $products,
        $step,
        $maxQuantity,
        $chunkMode = CheapestProductChunker::CHEAPEST_PRODUCT_CHUNKER_NAME
    ) {
        $result = [];

        $products = $this->flattenProducts($products);

        if (count($products) < $step) {
            return [];
        }

        $chunks = $this->chunkerRegistry->get($chunkMode)->chunk($products, $step);
        if ($chunks) {
            $result = array_merge($result, $chunks);
        }

        if (!$maxQuantity) {
            return $result;
        }

        return array_slice($result, 0, $maxQuantity);
    }

    /**
     * Flatten products
     *
     * @param array $products
     *
     * @return array
     */
    public function flattenProducts(array $products)
    {
        // flatten product list
        $result = [];
        foreach ($products as $product) {
            //don't stack free goods products
            $unserializedPromotionIds = unserialize($product['basketAttribute::swag_is_free_good_by_promotion_id']);
            if (!empty($unserializedPromotionIds)) {
                --$product['quantity'];
            }

            for ($i = 1; $i <= $product['quantity']; ++$i) {
                $result[] = $product;
            }
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($name)
    {
        return $name === self::GLOBAL_PRODUCT_STACKER_NAME;
    }
}
