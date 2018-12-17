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

namespace SwagAboCommerce\Bootstrap;

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

    /**
     * installs the AboCommerceFacet
     */
    public function install()
    {
        $sql = <<<SQL
INSERT INTO `s_search_custom_facet` (`unique_key`, `active`, `display_in_categories`, `position`, `name`, `facet`, `deletable`)
VALUES ('AboCommerceFacet', 0, 1, 60, 'Abo Commerce Filter', '{"SwagAboCommerce\\\\\\\Bundle\\\\\\\SearchBundle\\\\\\\Facet\\\\\\\AboCommerceFacet":{"label":"Abo Artikel"}}', 0)
ON DUPLICATE KEY UPDATE `facet` = '{"SwagAboCommerce\\\\\\\Bundle\\\\\\\SearchBundle\\\\\\\Facet\\\\\\\AboCommerceFacet":{"label":"Abo Artikel"}}';
SQL;

        $this->connection->exec($sql);
    }

    /**
     * deletes the AboCommerceFacet
     */
    public function uninstall()
    {
        $this->connection->createQueryBuilder()
            ->delete('s_search_custom_facet')
            ->where('`unique_key` LIKE "AboCommerceFacet"')
            ->execute();
    }

    /**
     * activates the AboCommerceFacet
     *
     * @param bool $active
     */
    public function activate($active)
    {
        $this->connection->createQueryBuilder()
            ->update('s_search_custom_facet')
            ->set('active', ':active')
            ->where('unique_key LIKE "AboCommerceFacet"')
            ->setParameter('active', $active)
            ->execute();
    }
}
