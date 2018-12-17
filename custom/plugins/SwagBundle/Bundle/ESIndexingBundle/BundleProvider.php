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

namespace SwagBundle\Bundle\ESIndexingBundle;

use Doctrine\DBAL\Connection;
use Shopware\Bundle\ESIndexingBundle\Product\ProductProviderInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\Attribute;
use Shopware\Bundle\StoreFrontBundle\Struct\Shop;

class BundleProvider implements ProductProviderInterface
{
    /**
     * @var ProductProviderInterface
     */
    private $coreProvider;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @param ProductProviderInterface $coreProvider
     * @param Connection               $connection
     */
    public function __construct(
        ProductProviderInterface $coreProvider,
        Connection $connection
    ) {
        $this->coreProvider = $coreProvider;
        $this->connection = $connection;
    }

    /**
     * {@inheritdoc}
     */
    public function get(Shop $shop, $numbers)
    {
        $products = $this->coreProvider->get($shop, $numbers);

        $bundles = $this->getBundles();

        foreach ($products as $product) {
            if (!array_key_exists($product->getNumber(), $bundles)) {
                $product->addAttribute('swag_bundle', new Attribute(['has_bundle' => false]));
                continue;
            }

            /** @var array $bundlesOfProduct */
            $bundlesOfProduct = $bundles[$product->getNumber()];

            $data['has_bundle'] = true;
            /*
             * If valid_from and / or valid_to NULL or not set, set a new time string which is always valid,
             * because its used for indicating a active bundle.
             */
            foreach ($bundlesOfProduct as $bundle) {
                $key = 'group_' . $bundle['customer_group_id'];

                if (!$bundle['valid_from']) {
                    $bundle['valid_from'] = '1900-01-01 00:00:00';
                }
                if (!$bundle['valid_to']) {
                    $bundle['valid_to'] = '4000-01-01 00:00:00';
                }

                $data[$key] = $bundle;
            }

            $product->addAttribute('swag_bundle', new Attribute($data));
        }

        return $products;
    }

    /**
     * @return array
     */
    private function getBundles()
    {
        $queryBuilder = $this->connection->createQueryBuilder();
        $queryBuilder->select([
            'DISTINCT productDetails.ordernumber',
            'bundles.valid_from',
            'bundles.valid_to',
            'groupMapping.customer_group_id',
        ])
            ->from('s_articles_bundles', 'bundles')
            ->innerJoin(
                'bundles',
                's_articles_details',
                'productDetails',
                'bundles.articleID = productDetails.articleID'
            )
            ->innerJoin(
                'bundles',
                's_articles_bundles_customergroups',
                'groupMapping',
                'groupMapping.bundle_id = bundles.id'
            )
            ->andWhere('productDetails.kind = 1')
            ->andWhere('bundles.active = 1');

        $mainResult = $queryBuilder->execute()->fetchAll(\PDO::FETCH_GROUP);

        $queryBuilder = $this->connection->createQueryBuilder();
        $queryBuilder->select([
            'DISTINCT productDetails.ordernumber',
            'bundles.valid_from',
            'bundles.valid_to',
            'groupMapping.customer_group_id',
        ])
            ->from('s_articles_bundles', 'bundles')
            ->innerJoin(
                'bundles',
                's_articles_bundles_articles',
                'bundlesRelation',
                'bundles.id = bundlesRelation.bundle_id'
            )
            ->innerJoin(
                'bundlesRelation',
                's_articles_details',
                'productDetails',
                'bundlesRelation.article_detail_id = productDetails.id'
            )
            ->innerJoin(
                'bundles',
                's_articles_bundles_customergroups',
                'groupMapping',
                'groupMapping.bundle_id = bundles.id'
            )
            ->where('bundles.display_global = 1')
            ->andWhere('bundles.active = 1');

        $subResult = $queryBuilder->execute()->fetchAll(\PDO::FETCH_GROUP);

        return array_merge($subResult, $mainResult);
    }
}
