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

namespace SwagBundle\Tests\Functional\TestHelper;

use Doctrine\DBAL\Connection;

class BundleTestDataAdministration
{
    /**
     * @var Connection
     */
    private $dbalConnection;

    /**
     * @param Connection $dbalConnection
     */
    public function __construct(Connection $dbalConnection)
    {
        $this->dbalConnection = $dbalConnection;
    }

    /**
     * Installs the bundles with all required data
     */
    public function installBundles()
    {
        $bundleData = BundleData::getBundleData();

        foreach ($bundleData as $bundle) {
            $bundleProducts = $bundle['bundleArticles'];
            $bundleCustomerGroups = $bundle['bundleCustomerGroups'];
            $bundlePrices = $bundle['bundlePrices'];
            unset($bundle['bundleArticles'], $bundle['bundleCustomerGroups'], $bundle['bundlePrices']);

            $queryBuilder = $this->dbalConnection->createQueryBuilder();
            $queryBuilder->insert('s_articles_bundles')
                ->values($bundle)
                ->execute();

            foreach ($bundleProducts as $bundleProduct) {
                $queryBuilder = $this->dbalConnection->createQueryBuilder();
                $queryBuilder->insert('s_articles_bundles_articles')
                    ->values($bundleProduct)
                    ->execute();
            }

            foreach ($bundleCustomerGroups as $bundleCustomerGroup) {
                $queryBuilder = $this->dbalConnection->createQueryBuilder();
                $queryBuilder->insert('s_articles_bundles_customergroups')
                    ->values($bundleCustomerGroup)
                    ->execute();
            }

            foreach ($bundlePrices as $bundlePrice) {
                $queryBuilder = $this->dbalConnection->createQueryBuilder();
                $queryBuilder->insert('s_articles_bundles_prices')
                    ->values($bundlePrice)
                    ->execute();
            }
        }
    }
}
