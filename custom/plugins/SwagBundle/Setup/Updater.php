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

namespace SwagBundle\Setup;

use Doctrine\DBAL\Connection;
use Shopware\Bundle\AttributeBundle\Service\CrudService;
use Shopware\Components\Model\ModelManager;
use SwagBundle\Setup\Helper\Attributes;
use SwagBundle\Setup\Helper\CustomFacet;
use SwagBundle\Setup\Helper\Database;

class Updater
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var CrudService
     */
    private $crudService;

    /**
     * @var ModelManager
     */
    private $modelManager;

    /**
     * @var Database
     */
    private $database;

    /**
     * @param Connection   $connection
     * @param CrudService  $crudService
     * @param ModelManager $modelManager
     */
    public function __construct(Connection $connection, CrudService $crudService, ModelManager $modelManager)
    {
        $this->connection = $connection;
        $this->database = new Database($connection);
        $this->crudService = $crudService;
        $this->modelManager = $modelManager;
    }

    /**
     * @param string $currentVersion
     */
    public function update($currentVersion)
    {
        $this->applyMigrations($currentVersion);
        $attributes = new Attributes($this->crudService, $this->modelManager);
        $attributes->createAttributes();
        $customFacetHelper = new CustomFacet($this->connection);
        $customFacetHelper->installCustomFacet();
    }

    /**
     * @param string $currentVersion
     */
    private function applyMigrations($currentVersion)
    {
        if ($this->database->columnExist('s_articles_bundles', 'max_quantity')) {
            $sql = 'ALTER TABLE `s_articles_bundles`
                    CHANGE `max_quantity`  `max_quantity` INT( 11 ) NOT NULL';
            $this->connection->executeUpdate($sql);
        }

        if (version_compare($currentVersion, '2.0.12', '<')) {
            if (!$this->database->columnExist('s_articles_bundles_articles', 'position')) {
                $sql = 'ALTER TABLE s_articles_bundles_articles
                        ADD `position` INT NULL DEFAULT NULL';
                $this->connection->executeUpdate($sql);
            }

            if (!$this->database->columnExist('s_articles_bundles', 'display_global')) {
                $sql = 'ALTER TABLE s_articles_bundles
                        ADD `display_global` INT(11) UNSIGNED NOT NULL DEFAULT 1';
                $this->connection->executeUpdate($sql);
            }
        }

        if (version_compare($currentVersion, '2.0.17', '<')
            && !$this->database->columnExist('s_articles_bundles', 'display_delivery')
        ) {
            $sql = 'ALTER TABLE s_articles_bundles
                    ADD `display_delivery` INT(11)';
            $this->connection->executeUpdate($sql);
        }

        if (version_compare($currentVersion, '2.1.0', '<')
            && !$this->database->columnExist('s_articles_bundles', 'show_name')
        ) {
            $sql = 'ALTER TABLE `s_articles_bundles`
                    ADD `show_name` TINYINT(1) NOT NULL DEFAULT 1';
            $this->connection->executeUpdate($sql);
        }

        // Remove non existent articles from active bundles
        if (version_compare($currentVersion, '2.1.2', '<')) {
            $sql = 'DELETE s_articles_bundles_articles FROM s_articles_bundles_articles
                      LEFT JOIN s_articles_details ad ON s_articles_bundles_articles.article_detail_id = ad.id
                    WHERE ISNULL(ad.id)';
            $this->connection->executeUpdate($sql);

            if (!$this->database->columnExist('s_articles_bundles', 'bundle_position')) {
                $sql = 'ALTER TABLE s_articles_bundles
                        ADD `bundle_position` INT(11) NOT NULL DEFAULT 0';
                $this->connection->executeUpdate($sql);
            }

            if (!$this->database->columnExist('s_articles_bundles', 'description')) {
                $sql = 'ALTER TABLE `s_articles_bundles`
                        ADD `description` TEXT NULL DEFAULT NULL';
                $this->connection->executeUpdate($sql);
            }
        }

        if (version_compare($currentVersion, '2.1.3', '<')
            && !$this->database->columnExist('s_order_basket_attributes', 'bundle_article_ordernumber')
        ) {
            $sql = 'ALTER TABLE s_order_basket_attributes
                    ADD COLUMN `bundle_article_ordernumber` VARCHAR(255) DEFAULT NULL;';
            $this->connection->executeUpdate($sql);
        }

        if (version_compare($currentVersion, '3.0.3', '<')
            && !$this->database->columnExist('s_order_basket_attributes', 'bundle_package_id')) {
            $sql = 'ALTER TABLE s_order_basket_attributes
                    ADD COLUMN `bundle_package_id` INT NULL DEFAULT NULL;';
            $this->connection->executeUpdate($sql);
        }
    }
}
