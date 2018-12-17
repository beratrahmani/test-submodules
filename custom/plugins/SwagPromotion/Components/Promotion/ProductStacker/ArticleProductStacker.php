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

use SwagPromotion\Components\Promotion\ProductChunker\ProductChunkerRegistry;

/**
 * ArticleProductStacker will stack products by base article (articleID)
 */
class ArticleProductStacker implements ProductStacker
{
    const ARTICLE_PRODUCT_STACKER_NAME = 'article';

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
    public function getStack(array $products, $step, $maxQuantity, $chunkMode = 'cheapest')
    {
        $result = [];
        foreach ($this->groupProductsByArticle($products) as $articleId => $products) {
            if (count($products) < $step) {
                continue;
            }

            $chunks = $this->chunkerRegistry->get($chunkMode)->chunk($products, $step);
            if ($chunks) {
                $result = array_merge($result, $chunks);
            }
        }

        if (!$maxQuantity) {
            return $result;
        }

        return array_slice($result, 0, $maxQuantity);
    }

    /**
     * {@inheritdoc}
     */
    public function supports($name)
    {
        return $name === self::ARTICLE_PRODUCT_STACKER_NAME;
    }

    /**
     * Groups products by articleID and flattens them
     *
     * @param array $products
     *
     * @return array
     */
    private function groupProductsByArticle(array $products)
    {
        $result = [];

        foreach ($products as $product) {
            //don't stack free goods products
            $unserializedPromotionIds = unserialize($product['basketAttribute::swag_is_free_good_by_promotion_id']);
            if (!empty($unserializedPromotionIds)) {
                --$product['quantity'];
            }

            foreach (range(1, $product['quantity']) as $i) {
                $result[$product['articleID']][] = $product;
            }
        }

        return $result;
    }
}
