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

interface ProductStacker
{
    /**
     * Split the given $products array in $step (but at most $maxQuantity) number of subarrays.
     * Main purpose is to build stacks of products which are discountable in e.g. BuyXGetYForFree
     * situations.
     * The decision which products may be stacked together is part of the logic of the implementing
     * class, by default we provide
     *  + Article (stack products belonging to the same base article)
     *  + Detail (stack variants with the same order number)
     *  + Global (any product can be stacked)
     *
     * Usually we recommend preparing subarrays of stacks. The ProductStacker class should be used
     * to split those groups into actual stacks.
     *
     * You should never return more array entries then $maxQuantity
     *
     * @param array  $products
     * @param int    $step
     * @param string $maxQuantity
     * @param string $chunkMode
     *
     * @return array
     */
    public function getStack(
        array $products,
        $step,
        $maxQuantity,
        $chunkMode = CheapestProductChunker::CHEAPEST_PRODUCT_CHUNKER_NAME
    );

    /**
     * @param string $name
     *
     * @return bool
     */
    public function supports($name);
}
