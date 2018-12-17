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

namespace SwagDigitalPublishing\Bootstrap\Components;

use Doctrine\DBAL\Connection;

class Database
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
     * Install all tables
     */
    public function install()
    {
        $this->createBannerTable();
        $this->createLayerTable();
        $this->createElementTable();
    }

    /**
     * removes all tables
     */
    public function uninstall()
    {
        $this->dropTables();
    }

    private function dropTables()
    {
        $sql = '
            DROP TABLE s_digital_publishing_elements;
            DROP TABLE s_digital_publishing_layers;
            DROP TABLE s_digital_publishing_content_banner;
        ';

        $this->dbalConnection->exec($sql);
    }

    /**
     * creates s_digital_publishing_content_banner table
     */
    private function createBannerTable()
    {
        $sql = "CREATE TABLE IF NOT EXISTS `s_digital_publishing_content_banner` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `name` varchar(255) NOT NULL,
              `bgType` varchar(255) NOT NULL DEFAULT 'color',
              `bgOrientation` varchar(255) DEFAULT NULL,
              `bgMode` varchar(255) DEFAULT NULL,
              `bgColor` varchar(255) DEFAULT NULL,
              `mediaId` int(11) DEFAULT NULL,
              PRIMARY KEY (`id`),
              INDEX `IDX_SDP_cb_index_1` (`mediaId`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

        $this->dbalConnection->exec($sql);
    }

    /**
     * creates s_digital_publishing_layers table
     */
    private function createLayerTable()
    {
        $sql = 'CREATE TABLE IF NOT EXISTS `s_digital_publishing_layers` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `label` varchar(255) DEFAULT NULL,
              `position` int(11) DEFAULT NULL,
              `orientation` varchar(255) DEFAULT NULL,
              `width` varchar(255) DEFAULT NULL,
              `height` varchar(255) DEFAULT NULL,
              `marginTop` int(11) DEFAULT NULL,
              `marginRight` int(11) DEFAULT NULL,
              `marginBottom` int(11) DEFAULT NULL,
              `marginLeft` int(11) DEFAULT NULL,
              `borderRadius` int(11) DEFAULT NULL,
              `bgColor` varchar(255) DEFAULT NULL,
              `link` varchar(255) DEFAULT NULL,
              `contentBannerID` int(11) NOT NULL,
              PRIMARY KEY (`id`),
              INDEX `IDX_SDP_cl_index_1` (`contentBannerID`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;';

        $this->dbalConnection->exec($sql);
    }

    /**
     * creates s_digital_publishing_elements table
     */
    private function createElementTable()
    {
        $sql = 'CREATE TABLE IF NOT EXISTS `s_digital_publishing_elements` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `name` varchar(255) NOT NULL,
          `label` varchar(255) DEFAULT NULL,
          `position` int(11) DEFAULT NULL,
          `payload` longtext,
          `layerID` int(11) NOT NULL,
          PRIMARY KEY (`id`),
          INDEX `IDX_SDP_e_index_1` (`layerID`),
          FOREIGN KEY (`layerID`)
            REFERENCES `s_digital_publishing_layers` (`id`)
            ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;';

        $this->dbalConnection->exec($sql);
    }
}
