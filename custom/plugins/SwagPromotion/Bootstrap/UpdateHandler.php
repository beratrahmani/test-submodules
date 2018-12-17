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
use Shopware\Bundle\AttributeBundle\Service\CrudService;
use Shopware\Bundle\AttributeBundle\Service\TypeMapping;
use Shopware\Components\Model\ModelManager;

class UpdateHandler
{
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
     * @param Connection   $connection
     * @param ModelManager $entityManager
     * @param CrudService  $crudService
     */
    public function __construct(Connection $connection, ModelManager $entityManager, CrudService $crudService)
    {
        $this->connection = $connection;
        $this->entityManager = $entityManager;
        $this->crudService = $crudService;
    }

    /**
     * @param string $oldVersion
     *
     * @return bool
     */
    public function update($oldVersion)
    {
        $success = [];

        if (version_compare($oldVersion, '1.1.1', '<')) {
            $success[] = $this->updateVersion111();
        }

        if (version_compare($oldVersion, '1.2.3', '<')) {
            $success[] = $this->updateVersion123();
        }

        if (version_compare($oldVersion, '1.3.1', '<')) {
            $success[] = $this->updateVersion131();
        }

        if (version_compare($oldVersion, '1.4.0', '<')) {
            $success[] = $this->updateVersion140();
        }

        if (version_compare($oldVersion, '2.0.0', '<')) {
            $success[] = $this->updateVersion200();
        }

        if (version_compare($oldVersion, '2.3.0', '<')) {
            $success[] = $this->updateVersion230();
        }

        foreach ($success as $value) {
            if (!$value) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return bool
     */
    private function updateVersion111()
    {
        try {
            $sql = 'CREATE INDEX promotion_repository ON s_plugin_promotion (active, valid_from, valid_to);';
            $this->connection->executeQuery($sql);
        } catch (\Exception $e) {
            // index already created
        }

        $columnCheck = $this->columnExists('s_order_basket_attributes', 'swag_promotion_is_free_good');
        if ($columnCheck) {
            $this->connection->update(
                's_order_basket_attributes',
                ['swag_promotion_is_free_good' => null],
                ['swag_promotion_is_free_good IS NOT NULL']
            );

            $this->crudService->update(
                AttributesHandler::BASKET_ATTRIBUTE_TABLE,
                'swag_promotion_is_free_good',
                TypeMapping::TYPE_STRING,
                [],
                'swag_is_free_good_by_promotion_id'
            );
        }

        // regenerate attribute model proxy class
        $this->entityManager->generateAttributeModels(['s_order_basket_attributes']);

        return true;
    }

    /**
     * @return bool
     */
    private function updateVersion123()
    {
        $columnCheck = $this->columnExists('s_plugin_promotion', 'show_badge');
        if (!$columnCheck) {
            $sql = 'ALTER TABLE `s_plugin_promotion` 
                    ADD COLUMN `show_badge` TINYINT(1) 
                    DEFAULT 1;';

            try {
                $this->connection->executeQuery($sql);
            } catch (\Exception $ex) {
                return false;
            }
        }

        $columnCheck = $this->columnExists('s_plugin_promotion', 'badge_text');
        if (!$columnCheck) {
            $sql = 'ALTER TABLE `s_plugin_promotion` 
                    ADD COLUMN `badge_text` VARCHAR(255);';

            try {
                $this->connection->executeQuery($sql);
            } catch (\Exception $ex) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return bool
     */
    private function updateVersion131()
    {
        try {
            $columnCheck = $this->columnExists('s_order_basket_attributes', 'swag_promotion_id');
            if (!$columnCheck) {
                $sql = 'ALTER TABLE s_order_basket_attributes CHANGE COLUMN `swag_promotionId` `swag_promotion_id` INT(11);';
                $this->connection->executeQuery($sql);
            }

            $attributeHandler = new AttributesHandler($this->crudService, $this->entityManager);
            $attributeHandler->installAttributes();
        } catch (\Exception $ex) {
            return false;
        }

        return true;
    }

    /**
     * @return bool
     */
    private function updateVersion140()
    {
        $columnCheck = $this->columnExists('s_plugin_promotion', 'apply_rules_first');
        if (!$columnCheck) {
            $sql = 'ALTER TABLE `s_plugin_promotion` 
                    ADD COLUMN `apply_rules_first` TINYINT(1) NOT NULL;';

            try {
                $this->connection->executeQuery($sql);
            } catch (\Exception $ex) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return bool
     */
    private function updateVersion200()
    {
        $columnCheck = $this->columnExists('s_plugin_promotion', 'show_hint_in_basket');

        if (!$columnCheck) {
            $sql = 'ALTER TABLE `s_plugin_promotion` 
                    ADD COLUMN `show_hint_in_basket` TINYINT(1) NOT NULL;';

            try {
                $this->connection->executeQuery($sql);
            } catch (\Exception $ex) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return bool
     */
    private function updateVersion230()
    {
        try {
            $attributeHandler = new AttributesHandler($this->crudService, $this->entityManager);
            $attributeHandler->installAttributes();
        } catch (\Exception $ex) {
            return false;
        }

        return true;
    }

    /**
     * @param string $tableName
     * @param string $columnName
     *
     * @return bool|string
     */
    private function columnExists($tableName, $columnName)
    {
        $sql = 'SHOW COLUMNS FROM `' . $tableName . '` LIKE "' . $columnName . '";';

        return $this->connection->executeQuery($sql)->fetchColumn();
    }
}
