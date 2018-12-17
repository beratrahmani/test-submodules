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

namespace SwagLiveShopping\Bootstrap;

use Shopware\Bundle\AttributeBundle\Service\CrudService;
use Shopware\Components\Emotion\ComponentInstaller;
use Shopware\Components\Model\ModelManager;

abstract class AbstractSetupService
{
    /**
     * @var DatabaseSetup
     */
    protected $databaseSetup;

    /**
     * @var EmotionElementSetup
     */
    protected $pluginElementSetup;

    /**
     * @param ModelManager       $entityManager
     * @param CrudService        $crudService
     * @param ComponentInstaller $componentInstaller
     * @param string             $shopwareVersion
     */
    public function __construct(
        ModelManager $entityManager,
        CrudService $crudService,
        ComponentInstaller $componentInstaller,
        $shopwareVersion
    ) {
        $this->databaseSetup = new DatabaseSetup(
            $entityManager,
            $crudService
        );

        $this->pluginElementSetup = new EmotionElementSetup(
            $componentInstaller
        );
    }
}
