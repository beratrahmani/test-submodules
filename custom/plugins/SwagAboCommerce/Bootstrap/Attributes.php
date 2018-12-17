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

use Shopware\Bundle\AttributeBundle\Service\CrudService;
use Shopware\Bundle\AttributeBundle\Service\TypeMapping;
use Shopware\Components\Model\ModelManager;

class Attributes
{
    /**
     * @var CrudService
     */
    private $crudService;

    /**
     * @var ModelManager
     */
    private $modelManager;

    /**
     * @param CrudService  $crudService
     * @param ModelManager $modelManager
     */
    public function __construct(CrudService $crudService, ModelManager $modelManager)
    {
        $this->crudService = $crudService;
        $this->modelManager = $modelManager;
    }

    /**
     * creates all required attributes
     */
    public function install()
    {
        $this->crudService->update(
            's_order_attributes',
            'swag_abo_commerce_id',
            TypeMapping::TYPE_INTEGER
        );

        $this->crudService->update(
            's_order_basket_attributes',
            'swag_abo_commerce_id',
            TypeMapping::TYPE_INTEGER
        );

        $this->crudService->update(
            's_order_basket_attributes',
            'swag_abo_commerce_delivery_interval',
            TypeMapping::TYPE_INTEGER
        );

        $this->crudService->update(
            's_order_basket_attributes',
            'swag_abo_commerce_duration',
            TypeMapping::TYPE_INTEGER
        );

        $this->modelManager->generateAttributeModels(['s_order_attributes', 's_order_basket_attributes']);
    }

    /**
     * deletes all required attributes
     */
    public function uninstall()
    {
        $this->crudService->delete('s_order_attributes', 'swag_abo_commerce_id');
        $this->crudService->delete('s_order_basket_attributes', 'swag_abo_commerce_id');
        $this->crudService->delete('s_order_basket_attributes', 'swag_abo_commerce_delivery_interval');
        $this->crudService->delete('s_order_basket_attributes', 'swag_abo_commerce_duration');

        $this->modelManager->generateAttributeModels(['s_order_attributes', 's_order_basket_attributes']);
    }
}
