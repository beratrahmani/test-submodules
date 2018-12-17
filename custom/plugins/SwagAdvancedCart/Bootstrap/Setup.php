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

namespace SwagAdvancedCart\Bootstrap;

use Doctrine\DBAL\Connection;
use Exception;
use Shopware\Bundle\AttributeBundle\Service\CrudService;
use Shopware\Bundle\AttributeBundle\Service\TypeMapping;
use Shopware\Components\Model\Configuration;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Mail\Mail;
use Shopware_Components_Translation;

/**
 * Class Setup
 *
 * provides the install und update methods for setting up AdvancedCart
 */
class Setup
{
    /**
     * @var string
     */
    private $pluginPath;

    /**
     * @var CrudService
     */
    private $crudService;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var ModelManager
     */
    private $modelManager;

    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @param string        $pluginPath
     * @param CrudService   $crudService
     * @param Connection    $connection
     * @param ModelManager  $modelManager
     * @param Configuration $configuration
     */
    public function __construct(
        $pluginPath,
        CrudService $crudService,
        Connection $connection,
        ModelManager $modelManager,
        Configuration $configuration
    ) {
        $this->pluginPath = $pluginPath;
        $this->crudService = $crudService;
        $this->connection = $connection;
        $this->modelManager = $modelManager;
        $this->configuration = $configuration;
    }

    /**
     * Plugin install method
     */
    public function install()
    {
        $sqlIndexer = new SqlIndex($this->connection);

        $this->createDatabaseTables();
        $this->checkForWrongColumnNameBasketId();
        $this->createUserAttribute();
        $sqlIndexer->createIndexes110();

        $this->createMailTemplate();
    }

    /**
     * Plugin update method
     *
     * @param string $version
     */
    public function update($version)
    {
        $sqlIndexer = new SqlIndex($this->connection);

        if (version_compare($version, '1.0.2', '<=')) {
            $sqlIndexer->createIndexes102();

            $sql = 'ALTER TABLE `s_order_basket_saved_items` CHANGE `basket_id` `basket_id` INT( 11 ) UNSIGNED NOT NULL;';
            $this->connection->exec($sql);
        }

        if (version_compare($version, '1.1.0', '>=')) {
            $sqlIndexer->createIndexes110();
        }

        $this->createMailTemplate();

        if (!$this->checkIfColumnExist('s_user_attributes', 'swag_advanced_cart_cookie_name_hash')) {
            $sql = 'ALTER TABLE s_user_attributes
                    ADD `swag_advanced_cart_cookie_name_hash` VARCHAR(255)';
            $this->connection->exec($sql);
        }

        $this->checkForWrongColumnNameBasketId();

        if (version_compare($version, '1.1.6', '<=')) {
            $sql = "UPDATE s_core_menu SET class = 'sprite-advanced-cart' WHERE controller = 'SwagAdvancedCart';";

            $this->connection->exec($sql);
        }

        if (version_compare($version, '1.2.3', '<')) {
            $this->updateDatabaseTableCollation();
        }
    }

    /**
     * @param $tableName
     * @param $columnName
     *
     * @throws Exception
     *
     * @return bool
     */
    private function checkIfColumnExist($tableName, $columnName)
    {
        try {
            $sql = 'SHOW COLUMNS FROM ' . $tableName;
            $columns = $this->connection->fetchAll($sql);
            foreach ($columns as $column) {
                if ($column['Field'] == $columnName) {
                    return true;
                }
            }
        } catch (Exception $ex) {
            return false;
        }

        return false;
    }

    /**
     * Updates the Database Collation
     */
    private function updateDatabaseTableCollation()
    {
        $queries = [];
        $queries[] = 'ALTER TABLE s_order_basket_saved MODIFY 
                  cookie_value VARCHAR(255) 
                  CHARACTER SET utf8 
                  COLLATE utf8_unicode_ci;';

        $queries[] = 'ALTER TABLE s_order_basket_saved MODIFY 
                  `name` VARCHAR(255) 
                  CHARACTER SET utf8 
                  COLLATE utf8_unicode_ci;';

        $queries[] = 'ALTER TABLE s_order_basket_saved_items MODIFY 
                  article_ordernumber VARCHAR(255) 
                  CHARACTER SET utf8 
                  COLLATE utf8_unicode_ci;';

        $this->connection->exec(implode(' ', $queries));
    }

    /**
     * Plugin createMailTemplate method
     * Creates the Mail Template for sharing carts
     */
    private function createMailTemplate()
    {
        $translationComponent = new Shopware_Components_Translation();

        /** @var Mail $mailModel */
        $mailModel = $this->modelManager->getRepository(Mail::class)
            ->findOneBy(['name' => 'sSHARECART']);
        if (!$mailModel) {
            $mailModel = new Mail();
            $mailModel->setSubject('Geteilter Warenkorb');
            $mailModel->setName('sSHARECART');
            $mailModel->setMailtype($mailModel::MAILTYPE_SYSTEM);
            $mailModel->setContent(file_get_contents($this->pluginPath . '/Resources/mail_templates/plain_de.tpl'));
            $mailModel->setContentHtml(file_get_contents($this->pluginPath . '/Resources/mail_templates/html_de.tpl'));
            $mailModel->setIsHtml(true);
            $mailModel->setFromMail('{config name=mail}');
            $mailModel->setFromName('{config name=shopName}');

            $this->modelManager->persist($mailModel);
            $this->modelManager->flush();
        } elseif ($translationComponent->read(2, 'config_mails', $mailModel->getId())) {
            return;
        }

        $translationComponent->write(
            2,
            'config_mails',
            $mailModel->getId(),
            [
                'subject' => 'Shared cart',
                'content' => file_get_contents($this->pluginPath . '/Resources/mail_templates/plain_en.tpl'),
                'contentHtml' => file_get_contents($this->pluginPath . '/Resources/mail_templates/html_en.tpl'),
            ]
        );
    }

    /**
     * Creates database tables
     */
    private function createDatabaseTables()
    {
        $this->connection->exec(
            'CREATE TABLE IF NOT EXISTS `s_order_basket_saved` (
              `id` INT(11) NOT NULL AUTO_INCREMENT,
              `cookie_value` VARCHAR(255) NOT NULL,
              `user_id` INT(11),
              `shop_id` INT(11),
              `expire` DATE NOT NULL,
              `modified` DATETIME NOT NULL,
              `name` VARCHAR(255),
              `published` INT(1),
              PRIMARY KEY (`id`))
              ENGINE=InnoDB DEFAULT CHARSET=utf8 DEFAULT COLLATE utf8_unicode_ci;'
        );

        $this->connection->exec(
            'CREATE TABLE IF NOT EXISTS `s_order_basket_saved_items` (
              `id` INT(11) NOT NULL AUTO_INCREMENT,
              `basket_id` INT(11) UNSIGNED NOT NULL,
              `article_ordernumber` VARCHAR(255),
              `quantity` INT(3),
              PRIMARY KEY (`id`), INDEX `basket_id` (`basket_id`))
              ENGINE=InnoDB DEFAULT CHARSET=utf8 DEFAULT COLLATE utf8_unicode_ci;'
        );
    }

    /**
     * Create a attribute to save the cookieName
     */
    private function createUserAttribute()
    {
        $this->crudService->update(
            's_user_attributes',
            'swag_advanced_cart_cookie_name_hash',
            TypeMapping::TYPE_STRING
        );

        $this->modelManager->generateAttributeModels(['s_user_attributes']);
    }

    /**
     * Checks if the old column name `basket_id` still exists and changes it, if necessary
     */
    private function checkForWrongColumnNameBasketId()
    {
        if ($this->checkIfColumnExist('s_order_basket_saved', 'basket_id')) {
            $sql = 'ALTER TABLE `s_order_basket_saved` CHANGE `basket_id` `cookie_value` VARCHAR(50) NOT NULL;';
            $this->connection->exec($sql);

            $metaDataCache = $this->configuration->getMetadataCacheImpl();

            if (method_exists($metaDataCache, 'deleteAll')) {
                $metaDataCache->deleteAll();
            }
        }
    }
}
