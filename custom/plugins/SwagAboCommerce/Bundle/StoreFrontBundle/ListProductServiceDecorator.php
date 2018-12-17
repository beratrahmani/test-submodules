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

namespace SwagAboCommerce\Bundle\StoreFrontBundle;

use Doctrine\DBAL\Connection;
use Shopware\Bundle\StoreFrontBundle\Service\ListProductServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\Attribute;
use Shopware\Bundle\StoreFrontBundle\Struct\ListProduct;
use Shopware\Bundle\StoreFrontBundle\Struct\ProductContextInterface;
use SwagAboCommerce\Services\CheapestPriceServiceInterface;

class ListProductServiceDecorator implements ListProductServiceInterface
{
    /**
     * @var ListProductServiceInterface
     */
    private $decorated;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var CheapestPriceServiceInterface
     */
    private $cheapestPriceService;

    /**
     * @param ListProductServiceInterface   $decorated
     * @param Connection                    $connection
     * @param CheapestPriceServiceInterface $cheapestPriceService
     */
    public function __construct(
        ListProductServiceInterface $decorated,
        Connection $connection,
        CheapestPriceServiceInterface $cheapestPriceService
    ) {
        $this->decorated = $decorated;
        $this->connection = $connection;
        $this->cheapestPriceService = $cheapestPriceService;
    }

    /**
     * {@inheritdoc}
     */
    public function getList(array $numbers, ProductContextInterface $context)
    {
        $products = $this->decorated->getList($numbers, $context);

        $ids = array_map(function (ListProduct $product) {
            return $product->getId();
        }, $products);

        $hasAbo = $this->getAboProducts($ids);

        foreach ($products as $product) {
            $attribute = new Attribute([
                'has_abo' => false,
                'exclusive' => false,
            ]);
            $product->addAttribute('swag_abo_commerce', $attribute);

            if (!array_key_exists($product->getId(), $hasAbo)) {
                continue;
            }

            $attribute->set('has_abo', true);
            $attribute->set('exclusive', $hasAbo[$product->getId()]);

            $product->setAllowBuyInListing(false);

            if ((bool) $hasAbo[$product->getId()] === true) {
                $this->cheapestPriceService->updateCheapestPrice($product);
            }
        }

        return $products;
    }

    /**
     * {@inheritdoc}
     */
    public function get($number, ProductContextInterface $context)
    {
        $result = $this->getList([$number], $context);

        return array_shift($result);
    }

    /**
     * returns ids of products with AboCommerce option as subset of the given product ids
     *
     * @param int[] $ids
     *
     * @return int[]
     */
    private function getAboProducts($ids)
    {
        $query = $this->connection->createQueryBuilder();
        $query->select(['abo.article_id', 'abo.exclusive']);
        $query->from('s_plugin_swag_abo_commerce_articles', 'abo');
        $query->where('abo.active = 1');
        $query->andWhere('abo.article_id IN (:ids)');
        $query->setParameter(':ids', array_values($ids), Connection::PARAM_INT_ARRAY);

        return $query->execute()->fetchAll(\PDO::FETCH_KEY_PAIR);
    }
}
