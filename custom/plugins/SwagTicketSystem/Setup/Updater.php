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

namespace SwagTicketSystem\Setup;

use Doctrine\DBAL\Connection;
use Enlight_Components_Db_Adapter_Pdo_Mysql as DatabaseConnection;
use Shopware\Bundle\MediaBundle\MediaServiceInterface;
use Shopware\Components\Model\Configuration;

class Updater
{
    /**
     * @var DatabaseConnection
     */
    private $databaseConnection;

    /**
     * @var Installer
     */
    private $installer;

    /**
     * @var Configuration
     */
    private $modelConfig;

    /**
     * @var Connection
     */
    private $dbalConnection;

    /**
     * @var MediaServiceInterface
     */
    private $mediaService;

    /**
     * Updater constructor.
     *
     * @param DatabaseConnection    $databaseConnection
     * @param Connection            $dbalConnection
     * @param Configuration         $modelConfig
     * @param Installer             $installer
     * @param MediaServiceInterface $mediaService
     */
    public function __construct(
        DatabaseConnection $databaseConnection,
        Connection $dbalConnection,
        Configuration $modelConfig,
        MediaServiceInterface $mediaService,
        Installer $installer
    ) {
        $this->databaseConnection = $databaseConnection;
        $this->dbalConnection = $dbalConnection;
        $this->modelConfig = $modelConfig;
        $this->mediaService = $mediaService;
        $this->installer = $installer;
    }

    /**
     * standard update method just to do not execute the install method
     *
     * @param string $version
     *
     * @return bool
     */
    public function update($version)
    {
        $this->installer->createDataBase();

        if (version_compare($version, '1.0.7', '<')) {
            $this->updateToVersion107();
        }

        if (version_compare($version, '1.0.8', '<')) {
            $this->updateToVersion108();
        }

        if (version_compare($version, '1.1.0', '<')) {
            $this->updateToVersion110();
        }

        if (version_compare($version, '1.5.3', '<')) {
            $this->updateToVersion153();
        }

        if (version_compare($version, '1.6.2', '<')) {
            $this->updateToVersion162();
        }

        if (version_compare($version, '2.1.2', '<')) {
            $this->updateToVersion212();
        }

        $metaDataCache = $this->modelConfig->getMetadataCacheImpl();
        if (method_exists($metaDataCache, 'deleteAll')) {
            $metaDataCache->deleteAll();
        }

        $this->installer->installWidget();
        $this->installer->createAttachmentAlbum();

        return true;
    }

    /**
     * update table "s_ticket_support"
     * and update table "s_ticket_support_history"
     */
    private function updateToVersion107()
    {
        $sql = 'ALTER TABLE `s_ticket_support`
                CHANGE `subject` `subject` VARCHAR(255) NULL;

                ALTER TABLE s_ticket_support_history
                ADD `attachment` VARCHAR(255) NOT NULL;';

        try {
            $this->databaseConnection->query($sql);
        } catch (\Exception $e) {
            // Do nothing
        }
    }

    /**
     * update table "s_ticket_support"
     * and update table "s_ticket_support_files"
     */
    private function updateToVersion108()
    {
        $sql = 'ALTER TABLE `s_ticket_support`
                ADD `formId` INT(5) NOT NULL AFTER `statusID`;

                ALTER TABLE `s_ticket_support_files`
                ADD `ticket_id` INT(11) NOT NULL AFTER `answer_id`';

        try {
            $this->databaseConnection->query($sql);
        } catch (\Exception $e) {
            // Do nothing
        }
    }

    /**
     * update table "s_ticket_support"
     */
    private function updateToVersion110()
    {
        $sql = 'ALTER TABLE `s_ticket_support`
                ADD `isRead` INT(1) ';
        try {
            $this->databaseConnection->query($sql);
        } catch (\Exception $ex) {
            // Do nothing
        }
    }

    /**
     * update table "s_ticket_support_history"
     */
    private function updateToVersion153()
    {
        $sql = 'ALTER TABLE `s_ticket_support_history`
                    ADD `statusId` INT(1) ';
        try {
            $this->databaseConnection->query($sql);
        } catch (\Exception $ex) {
            // Do nothing
        }
    }

    /**
     * Update menu item and add new status-translations
     */
    private function updateToVersion162()
    {
        $sql = "UPDATE s_core_menu
                SET class='sprite--ticket-system'
                WHERE name='Ticket-System';";

        $this->dbalConnection->executeQuery($sql)->execute();

        $this->installer->createStatusTranslations();
    }

    /**
     * Migrate the old path of media files
     */
    private function updateToVersion212()
    {
        $mediaPathMigration = new MediaPathMigration(
            $this->dbalConnection,
            $this->mediaService
        );

        $mediaPathMigration->migrate();
    }
}
