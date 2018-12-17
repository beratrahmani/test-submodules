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
 * Class AbstractProductChunker
 */
abstract class AbstractProductChunker implements ProductChunker
{
    /**
     * Performs a chunking of items, so that ONE LOW item is combined with $chunkSize-1 HIGH ITEMS:
     *
     * chunk([0,1,2,3,4,5,6,7,8], 3) => [[0,8,7], [1,6,5], [2,4,3]]
     *
     * This is useful, as usually the discount is calculated for the cheapest item in the stack. If you are about to
     * change this, you can either change the actual chunking logic here or the chunking order in e.g. `CheapestProductChunker`.
     *
     * @param array $array
     * @param int   $chunkSize
     *
     * @return array
     */
    protected function doChunk(array $array, $chunkSize)
    {
        $chunks = [];
        while (!empty($array)) {
            $current = [];
            if (count($array) < ($chunkSize)) {
                break;
            }

            // consume one bottom product
            if ($el = array_pop($array)) {
                $current[] = $el;
            }
            // consume $step top products
            for ($i = 1; $i < $chunkSize; ++$i) {
                if ($el = array_shift($array)) {
                    $current[] = $el;
                }
            }
            $chunks[] = $current;
        }

        return $chunks;
    }
}
