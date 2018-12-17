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

use Enlight_Components_Db_Adapter_Pdo_Mysql as DatabaseConnection;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\Model\ModelRepository;
use Shopware\Models\Media\Album;
use Shopware\Models\Media\Settings;
use Shopware\Models\Plugin\Plugin;
use Shopware\Models\Widget\Widget;
use Shopware_Components_Translation as Translator;

class Installer
{
    /**
     * @var DatabaseConnection
     */
    private $databaseConnection;

    /**
     * @var ModelManager
     */
    private $entityManager;

    /**
     * @var Plugin
     */
    private $pluginModel;

    /**
     * @var AclProvider
     */
    private $aclProvider;

    /**
     * @param DatabaseConnection $databaseConnection
     * @param ModelManager       $modelManager
     * @param Plugin             $pluginModel
     */
    public function __construct(DatabaseConnection $databaseConnection, ModelManager $modelManager, Plugin $pluginModel)
    {
        $this->databaseConnection = $databaseConnection;
        $this->entityManager = $modelManager;
        $this->pluginModel = $pluginModel;

        $this->aclProvider = new AclProvider(
            $this->databaseConnection,
            $this->pluginModel->getId()
        );
    }

    public function install()
    {
        $this->installWidget();
        $this->createDataBase();
        $this->importSnippets();
        $this->createAttachmentAlbum();
        $this->createStatusTranslations();

        $this->aclProvider->addACLResource();

        $this->importDump('s_ticket_support_mails', 'submission.sql');
        $this->importDump('s_ticket_support_status', 'status.sql');
        $this->importDump('s_ticket_support_types', 'type.sql');
    }

    public function createDataBase()
    {
        $this->createSTicketSupport();
        $this->createSTicketSupportHistory();
        $this->createSTicketSupportMails();
        $this->createSTicketSupportStatus();
        $this->createSTicketSupportTypes();
        $this->createSTicketSupportFiles();
    }

    /**
     * This method register the Widget in the Database
     */
    public function installWidget()
    {
        $name = 'swag-ticket-system';
        if (!$this->isWidgetInstalled($name)) {
            $this->createWidget($name);
        }
    }

    /**
     * Create media manager album for ticket answer
     */
    public function createAttachmentAlbum()
    {
        /** @var ModelRepository $albumRepository */
        $albumRepository = $this->entityManager->getRepository(Album::class);

        /** @var Album $album */
        $album = $albumRepository->findOneBy(['name' => 'TicketAttachment']);
        if (!$album) {
            $album = new Album();
            $album->setName('TicketAttachment');
            $album->setPosition(0);

            $this->entityManager->persist($album);
            $this->entityManager->flush($album);
        }

        $settings = $album->getSettings();

        if (!$settings) {
            $settings = new Settings();
            $settings->setAlbum($album);
            $settings->setIcon('sprite-blue-folder');
            $settings->setCreateThumbnails(0);
            $settings->setThumbnailSize('');

            $this->entityManager->persist($settings);
            $this->entityManager->flush($settings);
        }
    }

    /**
     * Creates the status-translations
     */
    public function createStatusTranslations()
    {
        $translationCmp = new Translator();
        $translationArray = [
            [
                'id' => 1,
                'description' => 'Open',
            ],
            [
                'id' => 2,
                'description' => 'In process',
            ],
            [
                'id' => 3,
                'description' => 'Processed',
            ],
            [
                'id' => 4,
                'description' => 'Done',
            ],
        ];

        foreach ($translationArray as $translation) {
            $translationCmp->write(
                2,
                'ticketStatus',
                $translation['id'],
                ['description' => $translation['description']]
            );
        }
    }

    /**
     * imports the standard data if not already available
     *
     * @param string $dataBaseTable
     * @param string $dumpFileName
     */
    private function importDump($dataBaseTable, $dumpFileName)
    {
        $dataBaseTable = $this->databaseConnection->quoteTableAs($dataBaseTable);

        $sql = 'SELECT id FROM ' . $dataBaseTable;
        $foundId = $this->databaseConnection->fetchOne($sql, []);

        if (!$foundId) {
            $sql = file_get_contents(realpath(dirname(__DIR__)) . '/Setup/dumps/' . $dumpFileName);
            $this->databaseConnection->query($sql, [$dataBaseTable]);
        }
    }

    /**
     * Imports the standard data if not already available
     */
    private function importSnippets()
    {
        // Add missing Snippet
        $sql = "INSERT IGNORE INTO `s_core_snippets` (
            `id` ,
            `namespace` ,
            `shopID` ,
            `localeID` ,
            `name` ,
            `value` ,
            `created` ,
            `updated`
        ) VALUES (
            NULL ,
            'frontend/ticket/detail',
            '1',
            '1', 'TicketDetailInfoAnswerSubject',
            'Antwort',
            '2010-01-01 00:00:00',
            '2010-09-28 11:54:19'
        ), (
            NULL ,
            'frontend/ticket/detail',
            '1',
            '2', 'TicketDetailInfoAnswerSubject',
            'Answer',
            '2010-01-01 00:00:00',
            '2010-09-28 11:54:19'
        )";

        $this->databaseConnection->exec($sql);
    }

    /**
     * Check for previous installations
     *
     * @param string $name
     *
     * @return bool
     */
    private function isWidgetInstalled($name)
    {
        $sql = 'SELECT name FROM s_core_widgets WHERE name = :widgetName';
        $result = $this->databaseConnection->fetchOne($sql, ['widgetName' => $name]);

        if ($result) {
            return true;
        }

        return false;
    }

    /**
     * Creates a new widget
     *
     * @param string $name
     */
    private function createWidget($name)
    {
        $widget = new Widget();
        $widget->setName($name);
        $widget->setPlugin($this->pluginModel);

        $this->pluginModel->getWidgets()->add($widget);
    }

    /**
     * create the "s_ticket_support" table
     */
    private function createSTicketSupport()
    {
        //create table s_ticket_support
        $sql = 'CREATE TABLE IF NOT EXISTS `s_ticket_support` (
              `id` INT(10) NOT NULL AUTO_INCREMENT,
              `uniqueID` VARCHAR(32) NOT NULL,
              `userID` INT(10) NOT NULL,
              `employeeID` INT(5) NOT NULL,
              `ticket_typeID` INT(10) NOT NULL,
              `statusID` INT(5) NOT NULL DEFAULT \'1\',
              `formId` INT(5) NOT NULL,
              `email` VARCHAR(255) NOT NULL,
              `subject` VARCHAR(255) NULL,
              `message` TEXT NOT NULL,
              `receipt` DATETIME NOT NULL,
              `last_contact` DATETIME NOT NULL,
              `additional` TEXT NOT NULL,
              `isocode` VARCHAR(3) NOT NULL DEFAULT \'de\',
              `shop_id` INT(11) UNSIGNED NOT NULL,
              `isRead` INT(1),
              PRIMARY KEY (`id`),
              KEY `shop_id` (`shop_id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci';

        $this->databaseConnection->exec($sql);
    }

    /**
     * create the "s_ticket_support_history" table
     */
    private function createSTicketSupportHistory()
    {
        //create table s_ticket_support_history
        $sql = 'CREATE TABLE IF NOT EXISTS `s_ticket_support_history` (
              `id` INT(10) NOT NULL AUTO_INCREMENT,
              `ticketID` INT(10) NOT NULL,
              `swUser` VARCHAR(100) NOT NULL,
              `subject` VARCHAR(255) NOT NULL,
              `message` TEXT NOT NULL,
              `receipt` DATETIME NOT NULL,
              `support_type` ENUM(\'manage\',\'direct\') NOT NULL,
              `receiver` VARCHAR(200) NOT NULL,
              `direction` VARCHAR(3) NOT NULL,
              `attachment` VARCHAR(255) NOT NULL,
              `statusId` INT(1) NOT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci';

        $this->databaseConnection->exec($sql);
    }

    /**
     * create the "s_ticket_support_mails" table
     */
    private function createSTicketSupportMails()
    {
        //create table s_ticket_support_mails
        $sql = 'CREATE TABLE IF NOT EXISTS `s_ticket_support_mails` (
              `id` INT(11) NOT NULL AUTO_INCREMENT,
              `name` VARCHAR(255) NOT NULL,
              `description` VARCHAR(255) NOT NULL,
              `frommail` VARCHAR(255) NOT NULL,
              `fromname` VARCHAR(255) NOT NULL,
              `subject` VARCHAR(255) NOT NULL,
              `content` TEXT NOT NULL,
              `contentHTML` TEXT NOT NULL,
              `ishtml` INT(11) NOT NULL,
              `attachment` VARCHAR(255) NOT NULL,
              `sys_dependent` TINYINT(1) NOT NULL DEFAULT \'0\',
              `isocode` VARCHAR(3) NOT NULL,
              `shop_id` INT(11) UNSIGNED NOT NULL,
              PRIMARY KEY (`id`),
              KEY `shop_id` (`shop_id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci';

        $this->databaseConnection->exec($sql);
    }

    /**
     * create the "s_ticket_support_status" table
     */
    private function createSTicketSupportStatus()
    {
        //create table s_ticket_support_status
        $sql = 'CREATE TABLE IF NOT EXISTS `s_ticket_support_status` (
              `id` INT(11) NOT NULL AUTO_INCREMENT,
              `description` VARCHAR(50) NOT NULL,
              `responsible` TINYINT(4) NOT NULL,
              `closed` TINYINT(4) NOT NULL,
              `color` VARCHAR(7) NOT NULL DEFAULT \'0\',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci';

        $this->databaseConnection->exec($sql);
    }

    /**
     * create the "s_ticket_support_types" table
     */
    private function createSTicketSupportTypes()
    {
        //create table s_ticket_support_types
        $sql = 'CREATE TABLE IF NOT EXISTS `s_ticket_support_types` (
              `id` INT(10) NOT NULL AUTO_INCREMENT,
              `name` VARCHAR(255) NOT NULL,
              `gridcolor` VARCHAR(7) NOT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci';

        $this->databaseConnection->exec($sql);
    }

    /**
     * create the "s_ticket_support_files" table
     */
    private function createSTicketSupportFiles()
    {
        //create table s_ticket_support_files
        $sql = 'CREATE TABLE IF NOT EXISTS `s_ticket_support_files` (
              `id` INT(10) NOT NULL AUTO_INCREMENT,
              `answer_id` INT(11) NOT NULL,
              `ticket_id` INT(11) NOT NULL,
              `name` VARCHAR(255) NOT NULL,
              `hash` VARCHAR(255) NOT NULL,
              `location` VARCHAR(255) NOT NULL,
              `uploadDate` DATETIME NOT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci';

        $this->databaseConnection->exec($sql);
    }
}
