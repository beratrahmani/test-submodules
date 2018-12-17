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

/**
 * DetailProductStacker will stack the products by ordernumber (default SW behaviour)
 */
class DetailProductStacker implements ProductStacker
{
    const DETAIL_PRODUCT_STACKER_NAME = 'detail';

    /**
     * {@inheritdoc}
     */
    public function getStack(array $products, $step, $maxQuantity, $chunkMode = 'cheapest')
    {
        $result = [];
        foreach ($products as $product) {
            //don't stack free goods products
            $unserializedPromotionIds = unserialize($product['basketAttribute::swag_is_free_good_by_promotion_id']);
            if (!empty($unserializedPromotionIds)) {
                --$product['quantity'];
            }

            if ($product['quantity'] < $step) {
                continue;
            }

            foreach (range(1, floor($product['quantity'] / $step)) as $i) {
                $currentStack = [];
                // stack items
                foreach (range(1, $step) as $j) {
                    $currentProduct = $product;
                    $currentProduct['quantity'] = 1;
                    $currentStack[] = $currentProduct;
                }
                $result[] = $currentStack;
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
        return $name === self::DETAIL_PRODUCT_STACKER_NAME;
    }
}
