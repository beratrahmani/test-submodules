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
use Exception;
use SwagBundle\Components\BundleComponentInterface;

class Database
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
     * @throws Exception
     */
    public function updateSchema()
    {
        try {
            $this->createBackupTables();
            $this->createNewTables();
            $this->importData();
            $this->removeBackupTables();
        } catch (Exception $ex) {
            $this->rollback();

            throw $ex;
        }
    }

    /**
     * Removes all plugin tables if they exists.
     */
    public function removePluginTables()
    {
        $this->dropTable('s_articles_bundles');
        $this->dropTable('s_articles_bundles_articles');
        $this->dropTable('s_articles_bundles_prices');
        $this->dropTable('s_articles_bundles_customergroups');
        $this->dropTable('s_articles_bundles_stint');
    }

    /**
     * Internal helper function to check if a database table column exist.
     *
     * @param string $tableName
     * @param string $columnName
     *
     * @return bool
     */
    public function columnExist($tableName, $columnName)
    {
        $sql = 'SHOW COLUMNS FROM ' . $tableName . ' LIKE :columnName';

        return $this->connection->executeQuery($sql, ['columnName' => $columnName])->rowCount() > 0;
    }

    /**
     * Creates backup tables for all bundle database tables.
     */
    private function createBackupTables()
    {
        $this->createBackupTable('s_articles_bundles');
        $this->createBackupTable('s_articles_bundles_articles');
        $this->createBackupTable('s_articles_bundles_prices');
        $this->createBackupTable('s_articles_bundles_customergroups');
        $this->createBackupTable('s_articles_bundles_stint');
    }

    /**
     * Internal helper function which creates new tables for the bundle plugin.
     */
    private function createNewTables()
    {
        $this->createBundlesTable();
        $this->createBundleArticlesTable();
        $this->createBundlePricesTable();
        $this->createBundleStintTable();
        $this->createBundleCustomerGroupsTable();
    }

    /**
     * Internal helper function which imports the bundle data from the old bundle tables into the just created
     * new bundle.
     */
    private function importData()
    {
        $migrations = [];

        if ($this->tableExist('s_articles_bundles_sw_backup')) {
            $this->importBundles();
        }

        if ($this->tableExist('s_articles_bundles_articles_sw_backup')) {
            if ($this->columnExist('s_articles_bundles_articles_sw_backup', 'article_detail_id')) {
                $this->importTable('s_articles_bundles_articles', 's_articles_bundles_articles_sw_backup');
            } else {
                $migrations[] = 'articles';
                $this->migrateBundleArticles();
            }
        }

        if ($this->tableExist('s_articles_bundles_prices_sw_backup')) {
            if ($this->columnExist('s_articles_bundles_prices_sw_backup', 'customer_group_id')) {
                $this->importTable('s_articles_bundles_prices', 's_articles_bundles_prices_sw_backup');
            } else {
                $migrations[] = 'prices';
                $this->migrateBundlePrices();
            }
        }

        if ($this->tableExist('s_articles_bundles_customergroups_sw_backup')) {
            $this->importTable('s_articles_bundles_customergroups', 's_articles_bundles_customergroups_sw_backup');
        } elseif ($this->tableExist('s_articles_bundles_sw_backup')) {
            $migrations[] = 'customergroups';
            $this->migrateBundleCustomerGroups();
        }

        if ($this->tableExist('s_articles_bundles_stint_sw_backup')) {
            if ($this->columnExist('s_articles_bundles_stint_sw_backup', 'article_detail_id')) {
                $this->importTable('s_articles_bundles_stint', 's_articles_bundles_stint_sw_backup');
            } else {
                $migrations[] = 'stints';
                $this->migrateBundleStints();
            }
        }

        return $migrations;
    }

    /**
     * Internal helper function which removes the backup tables of the plugin.
     */
    private function removeBackupTables()
    {
        $this->dropTable('s_articles_bundles_sw_backup');
        $this->dropTable('s_articles_bundles_articles_sw_backup');
        $this->dropTable('s_articles_bundles_prices_sw_backup');
        $this->dropTable('s_articles_bundles_customergroups_sw_backup');
        $this->dropTable('s_articles_bundles_stint_sw_backup');
    }

    /**
     * Rolls back database changes in case of errors
     */
    private function rollback()
    {
        $this->removeNewTables();
        $this->restoreBackupTables();
    }

    /**
     * Internal helper function to create a new table for the article bundles.
     */
    private function createBundlesTable()
    {
        $sql = "
            CREATE TABLE IF NOT EXISTS `s_articles_bundles` (
                `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `articleID` int(11) unsigned NOT NULL,
                `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
                `show_name` tinyint(1) NOT NULL,
                `active` int(1) unsigned NOT NULL,
                `description` text NULL DEFAULT NULL,
                `rab_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
                `taxID` int(11) unsigned DEFAULT NULL,
                `ordernumber` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
                `max_quantity_enable` int(11) unsigned NOT NULL,
                `display_global` int(11) unsigned NOT NULL,
                `display_delivery` int(11),
                `max_quantity` int(11) NOT NULL,
                `valid_from` datetime DEFAULT NULL,
                `valid_to` datetime DEFAULT NULL,
                `datum` datetime NOT NULL,
                `sells` int(11) unsigned NOT NULL,
                `bundle_type` int(11) NOT NULL DEFAULT '0',
                `bundle_position` int(11) NOT NULL DEFAULT 0,
                PRIMARY KEY (`id`),
                KEY `articleID` (`articleID`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;
        ";
        $this->connection->executeUpdate($sql);
    }

    /**
     * Internal helper function which creates a new table for the bundle customer groups
     */
    private function createBundleCustomerGroupsTable()
    {
        $sql = '
            CREATE TABLE IF NOT EXISTS `s_articles_bundles_customergroups` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `bundle_id` int(10) unsigned DEFAULT NULL,
                `customer_group_id` int(10) unsigned DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `bundle_id` (`bundle_id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;
        ';
        $this->connection->executeUpdate($sql);
    }

    /**
     * Internal helper function which creates a new table for the bundle articles.
     */
    private function createBundleArticlesTable()
    {
        $sql = "
            CREATE TABLE IF NOT EXISTS `s_articles_bundles_articles` (
                `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `bundle_id` int(11) unsigned NOT NULL,
                `article_detail_id`  int(11) unsigned NOT NULL,
                `quantity` int(11) NOT NULL DEFAULT '1',
                `configurable` int(1) NOT NULL DEFAULT '0',
                `bundle_group_id` INT unsigned NULL DEFAULT NULL,
                `position` INT NULL DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `bundle_id` (`bundle_id`,`article_detail_id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;
        ";
        $this->connection->executeUpdate($sql);
    }

    /**
     * Internal helper function to create a new table for the bundle prices.
     */
    private function createBundlePricesTable()
    {
        $sql = '
            CREATE TABLE IF NOT EXISTS `s_articles_bundles_prices` (
                `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `bundle_id` int(11) unsigned NOT NULL,
                `customer_group_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
                `price` double NOT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `bundle_id` (`bundle_id`,`customer_group_id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;
        ';
        $this->connection->executeUpdate($sql);
    }

    /**
     * Internal helper function to create a new table for the bundle stints.
     */
    private function createBundleStintTable()
    {
        $sql = '
            CREATE TABLE IF NOT EXISTS `s_articles_bundles_stint` (
                `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `bundle_id` int(11) unsigned NOT NULL,
                `article_detail_id` int(11) unsigned NOT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `bundle_id` (`bundle_id`,`article_detail_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;
        ';
        $this->connection->executeUpdate($sql);
    }

    /**
     * Internal helper function which imports the table data from the "$from" table into the "$into" table.
     *
     * @param string $into
     * @param string $from
     * @param string $columns
     */
    private function importTable($into, $from, $columns = '*')
    {
        if ($columns !== '*') {
            $sql = 'INSERT INTO ' . $into . ' (' . $columns . ') SELECT ' . $columns . ' FROM ' . $from;
        } else {
            $sql = 'INSERT INTO ' . $into . ' SELECT ' . $columns . ' FROM ' . $from;
        }

        $this->connection->executeUpdate($sql);
    }

    /**
     * Converts the old customer groups definition from the s_articles_bundles table into the new
     * s_articles_bundles_customergroups table.
     */
    private function migrateBundleCustomerGroups()
    {
        $sql = "SELECT id, customergroups FROM s_articles_bundles_sw_backup WHERE customergroups != ''";
        $bundles = $this->connection->executeQuery($sql)->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($bundles as $bundle) {
            if (empty($bundle['customergroups'])) {
                continue;
            }

            $customerGroups = [$bundle['customergroups']];
            if (strpos($bundle['customergroups'], ',')) {
                $customerGroups = explode(',', $bundle['customergroups']);
            }

            foreach ($customerGroups as $group) {
                $sql = 'SELECT id FROM s_core_customergroups WHERE groupkey = ?';
                $customerGroupId = $this->connection->executeQuery($sql, [trim($group)])->fetchColumn();

                if (!empty($customerGroupId)) {
                    $sql = 'INSERT INTO s_articles_bundles_customergroups (bundle_id, customer_group_id) VALUES (?, ?)';
                    $this->connection->executeUpdate($sql, [$bundle['id'], $customerGroupId]);
                }
            }
        }
    }

    /**
     * Converts the old bundle article data from shopware 3 to shopware 4.
     */
    private function migrateBundleArticles()
    {
        $sql = 'SELECT bundle.bundleID as bundleId, detail.id as articleDetailId
                FROM s_articles_bundles_articles_sw_backup bundle
                INNER JOIN s_articles_details detail
                  ON bundle.ordernumber = detail.ordernumber';
        $articles = $this->connection->executeQuery($sql)->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($articles as $article) {
            if (empty($article) || empty($article['articleDetailId'])) {
                continue;
            }

            $sql = 'INSERT INTO s_articles_bundles_articles (bundle_id, article_detail_id) VALUES (?, ?)';
            $this->connection->executeUpdate($sql, [$article['bundleId'], $article['articleDetailId']]);
        }
    }

    /**
     * Converts the old bundle stint data from shopware 3 to shopware 4.
     */
    private function migrateBundleStints()
    {
        $sql = 'SELECT bundle.bundleID as bundleId, detail.id as articleDetailId
                FROM s_articles_bundles_stint_sw_backup bundle
                INNER JOIN s_articles_details detail
                  ON bundle.ordernumber = detail.ordernumber';
        $articles = $this->connection->executeQuery($sql)->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($articles as $article) {
            if (empty($article) || empty($article['articleDetailId'])) {
                continue;
            }

            $this->connection->executeUpdate(
                'INSERT INTO s_articles_bundles_stint (bundle_id, article_detail_id) VALUES (:bundleId, :articleDetailId)',
                ['bundleId' => $article['bundleId'], 'articleDetailId' => $article['articleDetailId']]
            );
        }
    }

    /**
     * Converts the old bundle price data from shopware 3 to shopware 4.
     */
    private function migrateBundlePrices()
    {
        $sql = 'SELECT bundle.bundleID as bundleId, customerGroups.id as customerGroupId, bundle.price, customerGroups.taxinput, tax.tax, main.rab_type
                FROM s_articles_bundles_prices_sw_backup bundle
                INNER JOIN s_core_customergroups customerGroups
                  ON bundle.customergroup = customerGroups.groupkey
                INNER JOIN s_articles_bundles_sw_backup main
                  ON main.id = bundle.bundleID
                LEFT JOIN s_core_tax tax
                  ON customerGroups.tax = tax.id';

        $prices = $this->connection->executeQuery($sql)->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($prices as $price) {
            if (empty($price) || empty($price['customerGroupId'])) {
                continue;
            }

            if ((int) $price['taxinput'] === 1 && $price['rab_type'] === BundleComponentInterface::ABSOLUTE_DISCOUNT && !empty($price['tax'])) {
                $price['price'] = ($price['price'] / (100 + $price['tax'])) * 100;
            }

            $this->connection->executeUpdate(
                'INSERT INTO s_articles_bundles_prices (bundle_id, customer_group_id, price) VALUES (:bundleId, :customerGroupId, :price)',
                [
                    'bundleId' => $price['bundleId'],
                    'customerGroupId' => $price['customerGroupId'],
                    'price' => $price['price'],
                ]
            );
        }
    }

    /**
     * Restores the created backup tables.
     * Called if an exception occurred while updating the database in the install function.
     */
    private function restoreBackupTables()
    {
        $this->renameTable('s_articles_bundles_sw_backup', 's_articles_bundles');
        $this->renameTable('s_articles_bundles_articles_sw_backup', 's_articles_bundles_articles');
        $this->renameTable('s_articles_bundles_prices_sw_backup', 's_articles_bundles_prices');
        $this->renameTable('s_articles_bundles_customergroups_sw_backup', 's_articles_bundles_customergroups');
        $this->renameTable('s_articles_bundles_stint_sw_backup', 's_articles_bundles_stint');
    }

    /**
     * Internal helper function to remove the new tables.
     * Called if an exception occurred while updating the database in the install function.
     */
    private function removeNewTables()
    {
        if ($this->tableExist('s_articles_bundles_sw_backup')) {
            $this->dropTable('s_articles_bundles');
        }

        if ($this->tableExist('s_articles_bundles_articles_sw_backup')) {
            $this->dropTable('s_articles_bundles_articles');
        }

        if ($this->tableExist('s_articles_bundles_prices_sw_backup')) {
            $this->dropTable('s_articles_bundles_prices');
        }

        if ($this->tableExist('s_articles_bundles_customergroups_sw_backup')) {
            $this->dropTable('s_articles_bundles_customergroups');
        }

        if ($this->tableExist('s_articles_bundles_stint_sw_backup')) {
            $this->dropTable('s_articles_bundles_stint');
        }
    }

    /**
     * Internal helper function which starts an custom import of the bundle data.
     */
    private function importBundles()
    {
        $columns = ['id, articleID, name, active, rab_type, taxID, ordernumber, display_global, max_quantity_enable, max_quantity, valid_from, valid_to, datum, sells, bundle_position'];
        if ($this->columnExist('s_articles_bundles_sw_backup', 'bundle_type')) {
            $columns[] = 'bundle_type';
        } else {
            $columns[] = "'1' as bundle_type";
        }
        $this->importTable('s_articles_bundles', 's_articles_bundles_sw_backup', implode(',', $columns));

        $this->connection->executeUpdate("UPDATE s_articles_bundles SET valid_from = NULL WHERE valid_from = '0000-00-00 00:00:00'");
        $this->connection->executeUpdate("UPDATE s_articles_bundles SET valid_to = NULL WHERE valid_to = '0000-00-00 00:00:00'");
    }

    /**
     * Helper function to create a new backup for the passed table
     *
     * @param string $name
     */
    private function createBackupTable($name)
    {
        if ($this->tableExist($name)) {
            $this->dropTable($name . '_sw_backup');
            $this->renameTable($name, $name . '_sw_backup');
        }
    }

    /**
     * Internal helper function to rename
     *
     * @param string $from
     * @param string $to
     */
    private function renameTable($from, $to)
    {
        if ($this->tableExist($from)) {
            $sql = 'RENAME TABLE ' . $from . ' TO ' . $to;
            $this->connection->executeUpdate($sql);
        }
    }

    /**
     * Internal helper function to remove a table safety.
     *
     * @param string $name
     */
    private function dropTable($name)
    {
        $sql = 'DROP TABLE IF EXISTS ' . $name;
        $this->connection->executeUpdate($sql);
    }

    /**
     * Internal helper function to check if a database table exists.
     *
     * @param string $tableName
     *
     * @return bool
     */
    private function tableExist($tableName)
    {
        $sql = 'SHOW TABLES LIKE :tableName';

        return $this->connection->executeQuery($sql, ['tableName' => $tableName])->rowCount() > 0;
    }
}
