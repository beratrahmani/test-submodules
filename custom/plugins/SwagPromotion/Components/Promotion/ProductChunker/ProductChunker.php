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

namespace SwagPromotion\Components\Promotion\ProductChunker;

/**
 * ProductChunker chunks a given array of products into $step chunks of products
 */
interface ProductChunker
{
    /**
     * Chunk a given list of products.
     *
     * chunk([0,1,2,3,4,5,6,7,8], 3) => [[g1], [g2], [g3]]
     *
     * In this case each group will contain 3 products from the input array, e.g. [1, 2, 3]. The actual order depends
     * on the implementation of the chunker, as the first item will be the one, that defines the discount later on.
     * For this reason, the CheapestPriceChunker could return something like this:
     *
     * chunk([0,1,2,3,4,5,6,7,8], 3) => [[0,8,7], [1,6,5], [2,4,3]]
     *
     * In that case, the CheapestPriceChunker defines the ORDER of the items (cheapest prices first), the separation
     * of the items (one very cheap product at the beginning of any chunk) is performed by the AbstractProductChunker,
     * which could therefore be reuse for e.g. a "HighestPriceChunker" or a "ShippingCostChunker" etc.
     *
     * @param array $products
     * @param $step
     *
     * @return mixed
     */
    public function chunk(array $products, $step);

    /**
     * @param string $name
     *
     * @return bool
     */
    public function supports($name);
}
