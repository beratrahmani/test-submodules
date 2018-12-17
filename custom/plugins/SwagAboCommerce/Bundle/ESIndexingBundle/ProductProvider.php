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

namespace SwagAboCommerce\Bundle\ESIndexingBundle;

use Doctrine\DBAL\Connection;
use Shopware\Bundle\ESIndexingBundle\Product\ProductProviderInterface;
use Shopware\Bundle\ESIndexingBundle\Struct\Product;
use Shopware\Bundle\StoreFrontBundle\Struct\Attribute;
use Shopware\Bundle\StoreFrontBundle\Struct\Shop;

class ProductProvider implements ProductProviderInterface
{
    /**
     * @var ProductProviderInterface
     */
    private $coreService;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @param ProductProviderInterface $coreService
     * @param Connection               $connection
     */
    public function __construct(
        ProductProviderInterface $coreService,
        Connection $connection
    ) {
        $this->coreService = $coreService;
        $this->connection = $connection;
    }

    /**
     * @param Shop     $shop
     * @param string[] $numbers
     *
     * @return Product[]
     */
    public function get(Shop $shop, $numbers)
    {
        $products = $this->coreService->get($shop, $numbers);

        $productIds = $this->getProductIds($products);
        foreach ($products as $product) {
            $attribute = new Attribute(
                [
                    'has_abo' => in_array($product->getId(), $productIds),
                ]
            );
            $product->addAttribute('swag_abo', $attribute);
        }

        return $products;
    }

    /**
     * @param array $products
     *
     * @return int[]
     */
    private function getProductIds(array $products)
    {
        $ids = array_map(function (Product $product) {
            return $product->getId();
        }, $products);

        $query = $this->connection->createQueryBuilder();
        $query->select('article_id')
            ->from('s_plugin_swag_abo_commerce_articles', 'abo')
            ->where('abo.article_id IN (:ids)')
            ->where('abo.active = 1')
            ->setParameter(':ids', $ids, Connection::PARAM_INT_ARRAY);

        return $query->execute()->fetchAll(\PDO::FETCH_COLUMN);
    }
}
