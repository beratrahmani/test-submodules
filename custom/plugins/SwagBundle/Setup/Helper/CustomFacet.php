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

namespace SwagBundle\Setup\Helper;

use Doctrine\DBAL\Connection;

class CustomFacet
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

    public function installCustomFacet()
    {
        $sql = <<<SQL
INSERT INTO `s_search_custom_facet` (`unique_key`, `active`, `display_in_categories`, `position`, `name`, `facet`, `deletable`)
VALUES ('BundleFacet', 0, 1, 60, 'Bundle Filter', '{"SwagBundle\\\\\\\Bundle\\\\\\\SearchBundle\\\\\\\Facet\\\\\\\BundleFacet":{"label":"Bundle Products"}}', 0)
ON DUPLICATE KEY UPDATE `facet` = '{"SwagBundle\\\\\\\Bundle\\\\\\\SearchBundle\\\\\\\Facet\\\\\\\BundleFacet":{"label":"Bundle Products"}}';
SQL;

        $this->connection->executeUpdate($sql);
    }

    public function uninstallCustomFacet()
    {
        $this->connection->executeUpdate("DELETE FROM `s_search_custom_facet` WHERE `unique_key` = 'BundleFacet'");
    }

    /**
     * @param bool $active
     */
    public function setCustomFacetActiveFlag($active)
    {
        $this->connection->createQueryBuilder()
            ->update('s_search_custom_facet')
            ->set('active', ':active')
            ->where('unique_key LIKE "BundleFacet"')
            ->setParameter('active', $active)
            ->execute();
    }
}
