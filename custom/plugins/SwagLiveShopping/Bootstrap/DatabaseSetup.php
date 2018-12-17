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

namespace SwagLiveShopping\Bootstrap;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Shopware\Bundle\AttributeBundle\Service\CrudService;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Article\Article;
use Shopware\Models\Article\Detail;
use Shopware\Models\Customer\Group;

class DatabaseSetup
{
    /**
     * @var AbstractSchemaManager
     */
    private $schemaManager;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var ModelManager
     */
    private $entityManager;

    /**
     * @var CrudService
     */
    private $crudService;

    /**
     * @param ModelManager $entityManager
     * @param CrudService  $crudService
     */
    public function __construct(
        ModelManager $entityManager,
        CrudService $crudService
    ) {
        $this->entityManager = $entityManager;
        $this->schemaManager = $entityManager->getConnection()->getSchemaManager();
        $this->connection = $entityManager->getConnection();
        $this->crudService = $crudService;
    }

    public function removeAttributes()
    {
        $this->crudService->delete('s_order_basket_attributes', 'swag_live_shopping_timestamp');
        $this->crudService->delete('s_order_basket_attributes', 'swag_live_shopping_id');

        $this->entityManager->generateAttributeModels([
            's_order_basket_attributes',
        ]);
    }

    public function createAttributes()
    {
        $this->crudService->update('s_order_basket_attributes', 'swag_live_shopping_timestamp', 'datetime');
        $this->crudService->update('s_order_basket_attributes', 'swag_live_shopping_id', 'integer');

        $this->entityManager->generateAttributeModels([
            's_order_basket_attributes',
        ]);
    }

    public function dropTables()
    {
        $sql = 'DROP TABLE s_articles_live_customer_groups;
                DROP TABLE s_articles_live_prices;
                DROP TABLE s_articles_live_shoprelations;
                DROP TABLE s_articles_live_stint;
                DROP TABLE s_articles_lives;
        ';

        $this->connection->executeUpdate($sql);
    }

    public function installDatabase()
    {
        $oldStructure = $this->tableExist('s_articles_live');

        $this->createBackupTables($oldStructure);

        try {
            $this->createNewTables();

            if ($oldStructure) {
                $this->migrateData();
            } else {
                $this->importData();
            }

            $this->removeBackup();
        } catch (\Exception $e) {
            $this->restoreBackup();
            throw $e;
        }
    }

    /**
     * @param bool $active
     */
    public function setCustomFacetActiveFlag($active)
    {
        $this->connection->createQueryBuilder()
            ->update('s_search_custom_facet')
            ->set('active', ':active')
            ->where('unique_key LIKE "LiveShoppingFacet"')
            ->setParameter('active', $active)
            ->execute();
    }

    public function installCustomFacet()
    {
        $sql = <<<SQL
INSERT INTO `s_search_custom_facet` (`unique_key`, `active`, `display_in_categories`, `deletable`, `position`, `name`, `facet`) VALUES
('LiveShoppingFacet', 0, 1, 0, 100, 'Liveshopping Filter', '{"SwagLiveShopping\\\\\\\Bundle\\\\\\\SearchBundle\\\\\\\Facet\\\\\\\LiveShoppingFacet":{"label":"Liveshopping Aktionen"}}')
ON DUPLICATE KEY UPDATE `facet` = '{"SwagLiveShopping\\\\\\\Bundle\\\\\\\SearchBundle\\\\\\\Facet\\\\\\\LiveShoppingFacet":{"label":"Liveshopping Aktionen"}}'
SQL;

        $this->connection->executeUpdate($sql);
    }

    public function uninstallCustomFacet()
    {
        $this->connection->executeUpdate("DELETE FROM `s_search_custom_facet` WHERE unique_key = 'LiveShoppingFacet'");
    }

    /**
     * @param string $version
     */
    public function updateDatabase($version)
    {
        if (version_compare($version, '2.1.2', '<=')) {
            $this->connection->executeUpdate(
                'ALTER TABLE s_articles_lives ADD max_purchase INT(11) UNSIGNED NOT NULL AFTER max_quantity'
            );
        }

        if (version_compare($version, '2.6.0', '<')) {
            $this->connection->executeUpdate(
                "UPDATE `s_core_menu` SET `class` = 'sprite-liveshopping'
                WHERE `name` = 'Liveshopping'
                AND `class` = 'sprite-alarm-clock';"
            );
        }

        if (version_compare($version, '2.7.4', '<')) {
            $this->connection->executeUpdate(
                "UPDATE `s_core_menu` SET `controller` = 'SwagLiveShopping'
                WHERE `name` = 'Liveshopping'
                AND `controller` = 'LiveShopping';"
            );
        }
    }

    /**
     * @param bool $oldStructure
     */
    private function createBackupTables($oldStructure)
    {
        if ($oldStructure) {
            $this->createBackupTable('s_articles_live');
            $this->createBackupTable('s_articles_live_prices');
            $this->createBackupTable('s_articles_live_shoprelations');
            $this->createBackupTable('s_articles_live_stint');
        } else {
            $this->createBackupTable('s_articles_lives');
            $this->createBackupTable('s_articles_live_customer_groups');
            $this->createBackupTable('s_articles_live_prices');
            $this->createBackupTable('s_articles_live_shoprelations');
            $this->createBackupTable('s_articles_live_stint');
        }
    }

    /**
     * @param string $tableName
     *
     * @return bool
     */
    private function tableExist($tableName)
    {
        return $this->schemaManager->tablesExist([$tableName]);
    }

    /**
     * @param string $name
     */
    private function createBackupTable($name)
    {
        if (!$this->tableExist($name)) {
            return;
        }
        if ($this->tableExist($name . '_sw_backup')) {
            $this->dropTable($name . '_sw_backup');
        }
        $this->renameTable($name, $name . '_sw_backup');
    }

    /**
     * @param string $name
     */
    private function dropTable($name)
    {
        if (!$this->tableExist($name)) {
            return;
        }
        $this->schemaManager->dropTable($name);
    }

    /**
     * @param string $from
     * @param string $to
     */
    private function renameTable($from, $to)
    {
        if ($this->tableExist($from)) {
            $this->schemaManager->renameTable($from, $to);
        }
    }

    private function createNewTables()
    {
        $this->createLiveShoppingTable();
        $this->createCustomerGroupTable();
        $this->createLimitedVariantTable();
        $this->createPriceTable();
        $this->createSubShopTable();
    }

    /**
     * Helper function to create the s_articles_lives table.
     *
     * The s_articles_lives table contains all defined live shopping articles.
     */
    private function createLiveShoppingTable()
    {
        $sql = '
            CREATE TABLE IF NOT EXISTS `s_articles_lives` (
              `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
              `article_id` INT(11) UNSIGNED DEFAULT NULL,
              `type` INT(1) DEFAULT NULL,
              `name` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL,
              `active` INT(1) UNSIGNED NOT NULL,
              `order_number` VARCHAR(255) COLLATE utf8_unicode_ci DEFAULT NULL,
              `max_quantity_enable` INT(1) UNSIGNED NOT NULL,
              `max_quantity` INT(11) UNSIGNED NOT NULL,
              `max_purchase` INT(11) UNSIGNED NOT NULL,
              `valid_from` DATETIME NULL DEFAULT NULL,
              `valid_to` DATETIME NULL DEFAULT NULL,
              `datum` DATETIME NULL DEFAULT NULL,
              `sells` INT(11) UNSIGNED NOT NULL,
              PRIMARY KEY (`id`),
              KEY `article_id` (`article_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;
        ';
        $this->connection->executeUpdate($sql);
    }

    /**
     * Helper function to create the s_articles_live_customer_groups table.
     *
     * The s_articles_live_customer_groups table contains the definition which customer groups
     * can buy/see the defined live shopping article.
     */
    private function createCustomerGroupTable()
    {
        $sql = '
            CREATE TABLE IF NOT EXISTS `s_articles_live_customer_groups` (
              `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
              `live_shopping_id` INT(11) UNSIGNED DEFAULT NULL,
              `customer_group_id` INT(11) DEFAULT NULL,
              PRIMARY KEY (`id`),
              KEY `live_shopping_id` (`live_shopping_id`,`customer_group_id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;
        ';
        $this->connection->executeUpdate($sql);
    }

    /**
     * Helper function to create the s_articles_live_stint table.
     *
     * The s_articles_live_stint table contains the definition of a limited variant definition for
     * each live shopping article. The stint table allows the user to define an offset of article variants
     * on which the live shopping article will be displayed.
     */
    private function createLimitedVariantTable()
    {
        $sql = '
            CREATE TABLE IF NOT EXISTS `s_articles_live_stint` (
              `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
              `live_shopping_id` INT(11) UNSIGNED DEFAULT NULL,
              `article_detail_id` INT(11) DEFAULT NULL,
              PRIMARY KEY (`id`),
              KEY `live_shopping_id` (`live_shopping_id`,`article_detail_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;
        ';
        $this->connection->executeUpdate($sql);
    }

    private function createPriceTable()
    {
        $sql = '
            CREATE TABLE IF NOT EXISTS `s_articles_live_prices` (
              `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
              `live_shopping_id` INT(11) UNSIGNED DEFAULT NULL,
              `customer_group_id` INT(11) DEFAULT NULL,
              `price` DOUBLE NOT NULL,
              `endprice` DOUBLE NOT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;
        ';
        $this->connection->executeUpdate($sql);
    }

    private function createSubShopTable()
    {
        $sql = '
            CREATE TABLE IF NOT EXISTS `s_articles_live_shoprelations` (
              `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
              `live_shopping_id` INT(11) UNSIGNED DEFAULT NULL,
              `shop_id` INT(11) UNSIGNED DEFAULT NULL,
              PRIMARY KEY (`id`),
              KEY `live_shopping_id` (`live_shopping_id`,`shop_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;
        ';
        $this->connection->executeUpdate($sql);
    }

    private function migrateData()
    {
        $this->migrateLiveShoppings();
        $this->migratePrices();
        $this->migrateLimitedShops();
        $this->migrateLimitedVariants();
    }

    private function migrateLiveShoppings()
    {
        $sql = 'SELECT * FROM s_articles_live_sw_backup';
        $result = $this->connection->fetchAll($sql);

        foreach ($result as $liveShoppingData) {
            if (!empty($liveShoppingData['customergroups'])) {
                $customerGroups = explode(',', $liveShoppingData['customergroups']);
                foreach ($customerGroups as $key) {
                    $customerGroup = $this->entityManager->getRepository(Group::class)->findOneBy(['key' => $key]);

                    if (!($customerGroup instanceof Group)) {
                        continue;
                    }
                    $sql = 'INSERT INTO s_articles_live_customer_groups (live_shopping_id, customer_group_id) VALUES (?, ?)';
                    $this->connection->executeUpdate($sql, [$liveShoppingData['id'], $customerGroup->getId()]);
                }
            }

            $sql = 'INSERT INTO s_articles_lives (id, article_id, type, name, active, order_number, max_quantity_enable, max_quantity, valid_from, valid_to, datum, sells)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';

            $this->connection->executeUpdate(
                $sql,
                [
                    $liveShoppingData['id'],
                    $liveShoppingData['articleID'],
                    $liveShoppingData['typeID'],
                    $liveShoppingData['name'],
                    $liveShoppingData['active'],
                    $liveShoppingData['ordernumber'],
                    $liveShoppingData['max_quantity_enable'],
                    $liveShoppingData['max_quantity'],
                    $liveShoppingData['valid_from'],
                    $liveShoppingData['valid_to'],
                    $liveShoppingData['datum'],
                    $liveShoppingData['sells'],
                ]
            );
        }
    }

    private function migratePrices()
    {
        $sql = 'SELECT s_articles_live_prices_sw_backup.*, s_articles_live_sw_backup.articleID
                FROM s_articles_live_prices_sw_backup
                INNER JOIN s_articles_live_sw_backup
                    ON s_articles_live_prices_sw_backup.liveshoppingID = s_articles_live_sw_backup.id';

        $result = $this->connection->fetchAll($sql);

        foreach ($result as $priceData) {
            if (empty($priceData) || empty($priceData['customergroup'])) {
                continue;
            }

            $customerGroup = $this->entityManager->getRepository(Group::class)->findOneBy(
                ['key' => $priceData['customergroup']]
            );
            if (!($customerGroup instanceof Group)) {
                continue;
            }

            if ($customerGroup->getTaxInput() && !empty($priceData['articleID'])) {
                $product = $this->entityManager->find(Article::class, $priceData['articleID']);
                if ($product instanceof Article) {
                    if (!empty($priceData['price'])) {
                        $priceData['price'] = $priceData['price'] / (100 + $product->getTax()->getTax()) * 100;
                    }
                    if (!empty($priceData['endprice'])) {
                        $priceData['endprice'] = $priceData['endprice'] / (100 + $product->getTax()->getTax()) * 100;
                    }
                }
            }

            $sql = 'INSERT INTO s_articles_live_prices (id, live_shopping_id, customer_group_id, price, endprice)
                    VALUES (?, ?, ?, ?, ?)';

            $this->connection->executeUpdate($sql, [
                $priceData['id'],
                $priceData['liveshoppingID'],
                $customerGroup->getId(),
                $priceData['price'],
                $priceData['endprice'],
            ]);
        }
    }

    private function migrateLimitedShops()
    {
        $this->importTable(
            's_articles_live_shoprelations',
            's_articles_live_shoprelations_sw_backup',
            'id, liveshoppingID as live_shopping_id, subshopID as shop_id'
        );
    }

    private function migrateLimitedVariants()
    {
        $sql = 'SELECT * FROM s_articles_live_stint_sw_backup';
        $result = $this->connection->fetchAll($sql);

        foreach ($result as $variantData) {
            if (empty($variantData) || empty($variantData['ordernumber'])) {
                continue;
            }
            $variant = $this->entityManager->getRepository(Detail::class)->findOneBy(
                ['number' => $variantData['ordernumber']]
            );
            if (!($variant instanceof Detail)) {
                continue;
            }

            $sql = 'INSERT INTO s_articles_live_stint (live_shopping_id, article_detail_id) VALUES (?,?)';
            $this->connection->executeUpdate($sql, [$variantData['liveshoppingID'], $variant->getId()]);
        }
    }

    /**
     * @param string $into
     * @param string $from
     * @param string $columns
     */
    private function importTable($into, $from, $columns = '*')
    {
        $sql = 'INSERT INTO ' . $into . ' (SELECT ' . $columns . ' FROM ' . $from . ')';
        $this->connection->executeUpdate($sql);
    }

    private function importData()
    {
        if ($this->tableExist('s_articles_lives_sw_backup')) {
            $this->importTable('s_articles_lives', 's_articles_lives_sw_backup');
        }
        if ($this->tableExist('s_articles_live_customer_groups_sw_backup')) {
            $this->importTable('s_articles_live_customer_groups', 's_articles_live_customer_groups_sw_backup');
        }
        if ($this->tableExist('s_articles_live_prices_sw_backup')) {
            $this->importTable('s_articles_live_prices', 's_articles_live_prices_sw_backup');
        }
        if ($this->tableExist('s_articles_live_shoprelations_sw_backup')) {
            $this->importTable('s_articles_live_shoprelations', 's_articles_live_shoprelations_sw_backup');
        }
        if ($this->tableExist('s_articles_live_stint_sw_backup')) {
            $this->importTable('s_articles_live_stint', 's_articles_live_stint_sw_backup');
        }
    }

    private function removeBackup()
    {
        $this->dropTable('s_articles_live_sw_backup');
        $this->dropTable('s_articles_lives_sw_backup');
        $this->dropTable('s_articles_live_prices_sw_backup');
        $this->dropTable('s_articles_live_shoprelations_sw_backup');
        $this->dropTable('s_articles_live_stint_sw_backup');
        $this->dropTable('s_articles_live_customer_groups_sw_backup');
    }

    private function restoreBackup()
    {
        $this->dropTable('s_articles_live');
        $this->dropTable('s_articles_lives');
        $this->dropTable('s_articles_live_prices');
        $this->dropTable('s_articles_live_shoprelations');
        $this->dropTable('s_articles_live_stint');
        $this->dropTable('s_articles_live_customer_groups');

        $this->renameTable('s_articles_live_sw_backup', 's_articles_live');
        $this->renameTable('s_articles_lives_sw_backup', 's_articles_live');
        $this->renameTable('s_articles_live_prices_sw_backup', 's_articles_live');
        $this->renameTable('s_articles_live_shoprelations_sw_backup', 's_articles_live');
        $this->renameTable('s_articles_live_stint_sw_backup', 's_articles_live');
        $this->renameTable('s_articles_live_customer_groups_sw_backup', 's_articles_live');
    }
}
