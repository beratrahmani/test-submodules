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

namespace SwagBundle\Services\Products;

use SwagBundle\Models\Bundle;

class LongestShippingTimeInspector implements LongestShippingTimeInspectorInterface
{
    /**
     * @var array
     */
    private $products;

    /**
     * @var \DateTime
     */
    private $referenceDate;

    /**
     * @var Bundle
     */
    private $bundle;

    /**
     * @param string $date
     */
    public function __construct($date = 'now')
    {
        $this->referenceDate = new \DateTime($date);
    }

    /**
     * {@inheritdoc}
     */
    public function determineLongestShippingProduct(array $products, Bundle $bundle)
    {
        $this->products = $products;
        $this->bundle = $bundle;

        if (0 === count($this->products)) {
            return;
        }

        if ($this->determineProductByNonAvailability($this->products)) {
            return;
        }

        if ($this->determineProductByNonStocked($this->products)) {
            return;
        }

        if ($this->determineProductByStocked($this->products)) {
            return;
        }

        if ($this->determineProductByEsd($this->products)) {
            return;
        }

        throw new \RuntimeException('Could not determine a longest shipping product');
    }

    /**
     * {@inheritdoc}
     */
    public function determineProductByNonAvailability(array $products)
    {
        $notAvailableProducts = array_filter($products, function ($product) {
            return $product['instock'] <= 0 &&
                $product['shippingtime'] <= 0 && !$product['sReleaseDate'] && !$product['esd'];
        });

        if (!count($notAvailableProducts)) {
            return false;
        }

        $this->updateBundle(end($notAvailableProducts));

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function determineProductByNonStocked(array $products)
    {
        $nonStockedComparableProducts = array_filter($products, function ($product) {
            return $product['instock'] <= 0
                && ($product['shippingtime'] >= 0 || $product['sReleaseDate'])
                && !$product['esd'];
        });

        if (!count($nonStockedComparableProducts)) {
            return false;
        }

        $this->updateBundle(
            $this->findLongestShippingTimeProduct($nonStockedComparableProducts)
        );

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function determineProductByStocked(array $products)
    {
        $normalProducts = array_filter($products, function ($product) {
            return $product['instock'] > 0 && !$product['esd'];
        });

        if (!count($normalProducts)) {
            return false;
        }

        $this->updateBundle(
            end($normalProducts)
        );

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function determineProductByEsd(array $products)
    {
        $esdProducts = array_filter($products, function ($product) {
            return (bool) $product['esd'];
        });

        if (!count($esdProducts)) {
            return false;
        }

        $this->updateBundle(
            end($esdProducts)
        );

        return true;
    }

    /**
     * find the max shipping time
     *
     * @param array $products
     *
     * @return array a product
     */
    private function findLongestShippingTimeProduct(array $products)
    {
        $longestShippingTimeProduct = array_pop($products);
        $mainValueToCompare = $this->extractShippingTimeCompareValue($longestShippingTimeProduct);

        foreach ($products as $product) {
            $valueToCompare = $this->extractShippingTimeCompareValue($product);

            if ($mainValueToCompare > $valueToCompare) {
                continue;
            }

            $longestShippingTimeProduct = $product;
            $mainValueToCompare = $this->extractShippingTimeCompareValue($longestShippingTimeProduct);
        }

        return $longestShippingTimeProduct;
    }

    /**
     * extract 'shippingtime' or format 'sReleaseDate'
     *
     * @param array $product
     *
     * @return int
     */
    private function extractShippingTimeCompareValue(array $product)
    {
        $daysUntilRelease = 0;
        $daysUntilShipping = 0;

        if ($product['sReleaseDate']) {
            $daysUntilRelease = $this->referenceDate->diff($product['sReleaseDate'])->format('%a');
        }

        if ($product['shippingtime']) {
            $daysUntilShipping = $product['shippingtime'];
        }

        return (int) ($daysUntilRelease > $daysUntilShipping ? $daysUntilRelease : $daysUntilShipping);
    }

    /**
     * set the appropriate bundle property
     *
     * @param array $product
     */
    private function updateBundle(array $product)
    {
        $this->bundle->setLongestShippingTimeProduct($product);
    }
}
