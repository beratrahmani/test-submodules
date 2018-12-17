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

namespace SwagPromotion\Bootstrap;

use Doctrine\DBAL\Connection;

class DatabaseHandler
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
     * Install all promotion tables
     */
    public function installTables()
    {
        $this->createPromotionTable();
        $this->createCustomerTable();
        $this->createCustomerGroupTable();
        $this->createDoNotAllowTable();
        $this->createDoNotRunAfterTable();
        $this->createFreeGoodsTable();
        $this->createShopTable();
    }

    /**
     * Drops all Promotion tables
     */
    public function uninstallTables()
    {
        $sql = 'SET foreign_key_checks = 0;
            DROP TABLE IF EXISTS s_plugin_promotion;
            DROP TABLE IF EXISTS s_plugin_promotion_customer_count;
            DROP TABLE IF EXISTS s_plugin_promotion_customer_group;
            DROP TABLE IF EXISTS s_plugin_promotion_do_not_allow_later;
            DROP TABLE IF EXISTS s_plugin_promotion_do_not_run_after;
            DROP TABLE IF EXISTS s_plugin_promotion_free_goods;
            DROP TABLE IF EXISTS s_plugin_promotion_shop;
            SET foreign_key_checks = 1;';

        $this->connection->exec($sql);
    }

    /**
     * creates 's_plugin_promotion'
     */
    private function createPromotionTable()
    {
        $sql = 'CREATE TABLE IF NOT EXISTS `s_plugin_promotion` (
                  `id` INT(11) NOT NULL AUTO_INCREMENT,
                  `name` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL,
                  `rules` LONGTEXT COLLATE utf8_unicode_ci NOT NULL,
                  `apply_rules` LONGTEXT COLLATE utf8_unicode_ci,
                  `type` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL,
                  `number` VARCHAR(255) COLLATE utf8_unicode_ci DEFAULT NULL,
                  `description` LONGTEXT COLLATE utf8_unicode_ci,
                  `detail_description` LONGTEXT COLLATE utf8_unicode_ci,
                  `max_usage` INT(11) DEFAULT NULL,
                  `voucher_id` INT(11) DEFAULT NULL,
                  `no_vouchers` TINYINT(1) NOT NULL,
                  `valid_from` DATETIME DEFAULT NULL,
                  `valid_to` DATETIME DEFAULT NULL,
                  `stack_mode` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL,
                  `amount` DOUBLE DEFAULT NULL,
                  `step` INT(11) DEFAULT NULL,
                  `max_quantity` INT(11) DEFAULT NULL,
                  `active` TINYINT(1) NOT NULL,
                  `exclusive` TINYINT(1) NOT NULL,
                  `shipping_free` TINYINT(1) DEFAULT NULL,
                  `priority` INT(11) DEFAULT NULL,
                  `stop_processing` TINYINT(1) NOT NULL,
                  `show_badge` TINYINT(1) DEFAULT NULL,
                  `badge_text` VARCHAR(255) COLLATE utf8_unicode_ci DEFAULT NULL,
                  `apply_rules_first` TINYINT(1) NOT NULL,
                  `show_hint_in_basket` TINYINT(1) NOT NULL,
                  PRIMARY KEY (`id`),
                  KEY `IDX_promotion_voucher_id` (`voucher_id`),
                  KEY `promotion_repository` (`active`,`valid_from`,`valid_to`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;';

        $this->connection->exec($sql);
    }

    /**
     * creates 's_plugin_promotion_customer_count'
     */
    private function createCustomerTable()
    {
        $sql = 'CREATE TABLE IF NOT EXISTS  `s_plugin_promotion_customer_count` (
                  `id` INT(11) NOT NULL AUTO_INCREMENT,
                  `promotion_id` INT(11) DEFAULT NULL,
                  `customer_id` INT(11) DEFAULT NULL,
                  `order_id` INT(11) DEFAULT NULL,
                  PRIMARY KEY (`id`),
                  KEY `IDX_pcc_customer_id` (`customer_id`),
                  KEY `IDX_pcc_promotion_id` (`promotion_id`),
                  KEY `IDX_pcc_order_id` (`order_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;';

        $this->connection->exec($sql);
    }

    /**
     * creates 's_plugin_promotion_customer_group'
     */
    private function createCustomerGroupTable()
    {
        $sql = 'CREATE TABLE IF NOT EXISTS  `s_plugin_promotion_customer_group` (
                  `promotionID` INT(11) NOT NULL,
                  `groupID` INT(11) NOT NULL,
                  PRIMARY KEY (`promotionID`,`groupID`),
                  KEY `IDX_pcg_promotion_id` (`promotionID`),
                  KEY `IDX_pcg_group_id` (`groupID`),
                  CONSTRAINT `FK_spp_to_sppcg_promotion_id` FOREIGN KEY (`promotionID`) REFERENCES `s_plugin_promotion` (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;';

        $this->connection->exec($sql);
    }

    /**
     * creates 's_plugin_promotion_do_not_allow_later'
     */
    private function createDoNotAllowTable()
    {
        $sql = 'CREATE TABLE IF NOT EXISTS  `s_plugin_promotion_do_not_allow_later` (
                  `promotionID` INT(11) NOT NULL,
                  `doNotAllowLaterID` INT(11) NOT NULL,
                  PRIMARY KEY (`promotionID`,`doNotAllowLaterID`),
                  KEY `IDX_pdnal_promotion_id` (`promotionID`),
                  KEY `IDX_pdnal_do_not_allow_later_id` (`doNotAllowLaterID`),
                  CONSTRAINT `FK_spp_to_sppdnal_do_not_allow_later_id` FOREIGN KEY (`doNotAllowLaterID`) REFERENCES `s_plugin_promotion` (`id`),
                  CONSTRAINT `FK_spp_to_sppdnal_promotion_id` FOREIGN KEY (`promotionID`) REFERENCES `s_plugin_promotion` (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;';

        $this->connection->exec($sql);
    }

    /**
     * creates 's_plugin_promotion_do_not_run_after'
     */
    private function createDoNotRunAfterTable()
    {
        $sql = 'CREATE TABLE IF NOT EXISTS  `s_plugin_promotion_do_not_run_after` (
                  `promotionID` INT(11) NOT NULL,
                  `doNotRunAfterID` INT(11) NOT NULL,
                  PRIMARY KEY (`promotionID`,`doNotRunAfterID`),
                  KEY `IDX_sppdnra_promotion_id` (`promotionID`),
                  KEY `IDX_sppdnra_do_not_run_after_id` (`doNotRunAfterID`),
                  CONSTRAINT `FK_spp_to_sppdnra_do_not_run_after_id` FOREIGN KEY (`doNotRunAfterID`) REFERENCES `s_plugin_promotion` (`id`),
                  CONSTRAINT `FK_spp_to_sppdnra_promotion_id` FOREIGN KEY (`promotionID`) REFERENCES `s_plugin_promotion` (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;';

        $this->connection->exec($sql);
    }

    /**
     * creates 's_plugin_promotion_free_goods'
     */
    private function createFreeGoodsTable()
    {
        $sql = 'CREATE TABLE IF NOT EXISTS  `s_plugin_promotion_free_goods` (
                  `promotionID` INT(11) NOT NULL,
                  `articleID` INT(11) NOT NULL,
                  PRIMARY KEY (`promotionID`,`articleID`),
                  KEY `IDX_sppfg_promotion_id` (`promotionID`),
                  KEY `IDX_sppfg_article_id` (`articleID`),
                  CONSTRAINT `FK_spp_to_sppfg_promotion_id` FOREIGN KEY (`promotionID`) REFERENCES `s_plugin_promotion` (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;';

        $this->connection->exec($sql);
    }

    /**
     * creates 's_plugin_promotion_shop'
     */
    private function createShopTable()
    {
        $sql = 'CREATE TABLE IF NOT EXISTS  `s_plugin_promotion_shop` (
                  `promotionID` INT(11) NOT NULL,
                  `shopID` INT(11) NOT NULL,
                  PRIMARY KEY (`promotionID`,`shopID`),
                  KEY `IDX_spps_promotion_id` (`promotionID`),
                  KEY `IDX_spps_shop_id` (`shopID`),
                  CONSTRAINT `FK_spp_to_spps_promotion_id` FOREIGN KEY (`promotionID`) REFERENCES `s_plugin_promotion` (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;';

        $this->connection->exec($sql);
    }
}
