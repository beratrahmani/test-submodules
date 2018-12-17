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

class Installer
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
     * @param Connection   $connection
     * @param CrudService  $crudService
     * @param ModelManager $modelManager
     */
    public function __construct(Connection $connection, CrudService $crudService, ModelManager $modelManager)
    {
        $this->connection = $connection;
        $this->crudService = $crudService;
        $this->modelManager = $modelManager;
    }

    public function install()
    {
        $database = new Database($this->connection);
        $database->updateSchema();

        $attributes = new Attributes($this->crudService, $this->modelManager);
        $attributes->createAttributes();

        $customFacetHelper = new CustomFacet($this->connection);
        $customFacetHelper->installCustomFacet();
    }
}
