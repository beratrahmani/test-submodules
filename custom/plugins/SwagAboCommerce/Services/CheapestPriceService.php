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

namespace SwagAboCommerce\Services;

use Doctrine\DBAL\Connection;
use Shopware\Bundle\StoreFrontBundle\Struct\Attribute;
use Shopware\Bundle\StoreFrontBundle\Struct\ListProduct;

class CheapestPriceService implements CheapestPriceServiceInterface
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * {@inheritdoc}
     */
    public function updateCheapestPrice(ListProduct $product)
    {
        $aboPrices = $this->getAboPrices();
        $maxAbsoluteDiscount = (float) $this->getMaxAbsoluteDiscount($aboPrices);
        $maxPercentageDiscount = (float) $this->getMaxPercentageDiscount($aboPrices);
        $newCheapestPrice = $this->getNewCheapestPrice($product, $maxPercentageDiscount, $maxAbsoluteDiscount);
        $cheapestAboPriceAttribute = new Attribute([
            'cheapest_abo_price' => $newCheapestPrice,
        ]);
        $product->addAttribute('swag_abo_commerce_prices', $cheapestAboPriceAttribute);

        return $product;
    }

    /**
     * @return array
     */
    private function getAboPrices()
    {
        $query = $this->connection->createQueryBuilder();
        $query->select('abo_prices.discount_absolute', 'abo_prices.discount_percent')
            ->from('s_plugin_swag_abo_commerce_prices', 'abo_prices')
            ->innerJoin(
                'abo_prices',
                's_plugin_swag_abo_commerce_articles',
                'abo_articles',
                'abo_prices.abo_article_id = abo_articles.id'
            );

        return $query->execute()->fetchAll();
    }

    /**
     * @param array $aboPrices
     *
     * @return float
     */
    private function getMaxAbsoluteDiscount(array $aboPrices)
    {
        $absoluteDiscounts = [];

        if (count($aboPrices) > 1) {
            foreach ($aboPrices as $res => $value) {
                $absoluteDiscounts[$res] = (float) $value['discount_absolute'];
            }

            return max($absoluteDiscounts);
        }

        return (float) $aboPrices[0]['discount_absolute'];
    }

    /**
     * @param array $aboPrices
     *
     * @return float
     */
    private function getMaxPercentageDiscount(array $aboPrices)
    {
        $percentageDiscounts = [];

        if (count($aboPrices) > 1) {
            foreach ($aboPrices as $res => $value) {
                $percentageDiscounts[$res] = (float) $value['discount_percent'];
            }

            return max($percentageDiscounts);
        }

        return (float) $aboPrices[0]['discount_percent'];
    }

    /**
     * @param ListProduct $product
     * @param float       $maxPercentageDiscount
     * @param float       $maxAbsoluteDiscount
     *
     * @return float
     */
    private function getNewCheapestPrice(ListProduct $product, $maxPercentageDiscount, $maxAbsoluteDiscount)
    {
        $oldCheapestPrice = $product->getCheapestPrice()->getCalculatedPrice();
        $tax = 1 + ($product->getTax()->getTax() / 100);

        if ($maxAbsoluteDiscount === 0.0 && $maxPercentageDiscount === 0.0) {
            return $oldCheapestPrice;
        }

        if ($maxPercentageDiscount > 0.0) {
            $maxPercentageDiscount = 1 - ($maxPercentageDiscount / 100);
        } else {
            $maxPercentageDiscount = 1.0;
        }

        if ($maxAbsoluteDiscount > 0.0) {
            $maxAbsoluteDiscount *= $tax;
        }

        if (($oldCheapestPrice - $maxAbsoluteDiscount) < ($oldCheapestPrice * $maxPercentageDiscount)) {
            return $oldCheapestPrice - $maxAbsoluteDiscount;
        }

        return $oldCheapestPrice * $maxPercentageDiscount;
    }
}
