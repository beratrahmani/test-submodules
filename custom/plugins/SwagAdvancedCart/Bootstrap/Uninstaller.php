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

use Doctrine\Common\Cache\Cache;
use Doctrine\DBAL\Connection;
use Shopware\Bundle\AttributeBundle\Service\CrudService;
use Shopware\Components\Model\ModelManager;

class Uninstaller
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var CrudService
     */
    private $attributeCrudService;

    /**
     * @var ModelManager
     */
    private $modelManager;

    /**
     * Uninstaller constructor.
     *
     * @param Connection   $connection
     * @param CrudService  $attributeCrudService
     * @param ModelManager $modelManager
     */
    public function __construct(Connection $connection, CrudService $attributeCrudService, ModelManager $modelManager)
    {
        $this->connection = $connection;
        $this->attributeCrudService = $attributeCrudService;
        $this->modelManager = $modelManager;
    }

    /**
     * @return bool
     */
    public function uninstall()
    {
        $this->removeAttributeColumns();
        $this->dropTables();
    }

    private function removeAttributeColumns()
    {
        $this->attributeCrudService->delete(
            's_user_attributes',
            'swag_advanced_cart_cookie_name_hash'
        );

        /** @var Cache $metaDataCache */
        $metaDataCache = $this->modelManager->getConfiguration()->getMetadataCacheImpl();
        if ($metaDataCache) {
            $metaDataCache->deleteAll();
        }

        $this->modelManager->generateAttributeModels(['s_user_attributes']);
    }

    private function dropTables()
    {
        $this->connection->executeQuery('DROP TABLE IF EXISTS s_order_basket_saved_items;');
        $this->connection->executeQuery('DROP TABLE IF EXISTS s_order_basket_saved;');
    }
}
