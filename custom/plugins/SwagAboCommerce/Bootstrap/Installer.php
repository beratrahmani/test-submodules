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

namespace SwagAboCommerce\Bootstrap;

use Doctrine\DBAL\Connection;
use Shopware\Bundle\AttributeBundle\Service\CrudService;
use Shopware\Components\Model\ModelManager;

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

    /**
     * installs database and customFacet
     */
    public function install()
    {
        $this->getDatabase()->install();
        $this->getCustomFacet()->install();
        $this->getAttributes()->install();
    }

    /**
     * uninstalls database and customFacet
     */
    public function uninstall()
    {
        $this->getDatabase()->uninstall();
        $this->getCustomFacet()->uninstall();
        $this->getAttributes()->uninstall();
    }

    /**
     * activates the custom facet
     */
    public function activate()
    {
        $this->getCustomFacet()->activate(true);
    }

    /**
     * deactivates the custom facet
     */
    public function deactivate()
    {
        $this->getCustomFacet()->activate(false);
    }

    /**
     * @return Database
     */
    private function getDatabase()
    {
        return new Database($this->connection);
    }

    /**
     * @return CustomFacet
     */
    private function getCustomFacet()
    {
        return new CustomFacet($this->connection);
    }

    /**
     * @return Attributes
     */
    private function getAttributes()
    {
        return new Attributes(
            $this->crudService,
            $this->modelManager
        );
    }
}
